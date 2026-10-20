<?php

namespace Tests\Feature;

use App\Models\AttendanceSession;
use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JitsiWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected string $secret = 'meethub_jitsi_webhook_secret_key_2026';

    public function test_webhook_rejects_unauthorized_request(): void
    {
        $response = $this->postJson('/api/v1/webhooks/jitsi', [
            'event' => 'room_created',
            'room_name' => 'meethub_testroom',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['status' => 'error']);
    }

    public function test_webhook_processes_room_created_with_bearer_token(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Proyek Baru',
            'room_code' => 'mh-hook-1234',
            'jitsi_room_name' => 'meethub_mhhook1234',
            'scheduled_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->secret)
            ->postJson('/api/v1/webhooks/jitsi', [
                'event' => 'room_created',
                'room_name' => $meeting->jitsi_room_name,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'event' => 'room_created',
        ]);

        $this->assertEquals('ongoing', $meeting->fresh()->status);
        $this->assertNotNull($meeting->fresh()->started_at);
    }

    public function test_webhook_processes_participant_events_with_hmac_signature(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $participant = User::factory()->create([
            'name' => 'Rina Peserta',
            'email' => 'rina@example.com',
        ]);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Koordinasi Mingguan',
            'room_code' => 'mh-hook-5678',
            'jitsi_room_name' => 'meethub_mhhook5678',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'ongoing',
        ]);

        // 1. Participant Joined
        $joinPayload = [
            'event' => 'participant_joined',
            'room_name' => $meeting->jitsi_room_name,
            'participant' => [
                'id' => (string) $participant->id,
                'name' => $participant->name,
                'email' => $participant->email,
            ],
        ];

        $jsonContent = json_encode($joinPayload);
        $signature = 'sha256=' . hash_hmac('sha256', $jsonContent, $this->secret);

        $responseJoin = $this->call(
            'POST',
            '/api/v1/webhooks/jitsi',
            [],
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_X_JITSI_SIGNATURE' => $signature,
            ],
            $jsonContent
        );

        $responseJoin->assertStatus(200);
        $this->assertDatabaseHas('attendance_sessions', [
            'meeting_id' => $meeting->id,
            'user_id' => $participant->id,
            'platform' => 'jitsi_sfu',
        ]);

        // 2. Participant Left
        $leavePayload = [
            'event' => 'participant_left',
            'room_name' => $meeting->jitsi_room_name,
            'participant' => [
                'id' => (string) $participant->id,
                'email' => $participant->email,
            ],
        ];

        $jsonLeave = json_encode($leavePayload);
        $leaveSignature = 'sha256=' . hash_hmac('sha256', $jsonLeave, $this->secret);

        $responseLeave = $this->call(
            'POST',
            '/api/v1/webhooks/jitsi',
            [],
            [],
            [],
            [
                'HTTP_CONTENT_TYPE' => 'application/json',
                'HTTP_X_JITSI_SIGNATURE' => $leaveSignature,
            ],
            $jsonLeave
        );

        $responseLeave->assertStatus(200);

        $session = AttendanceSession::where('meeting_id', $meeting->id)
            ->where('user_id', $participant->id)
            ->first();

        $this->assertNotNull($session->left_at);
    }

    public function test_webhook_processes_room_destroyed(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Siang',
            'room_code' => 'mh-hook-9999',
            'jitsi_room_name' => 'meethub_mhhook9999',
            'scheduled_at' => now(),
            'duration_minutes' => 30,
            'status' => 'ongoing',
        ]);

        $response = $this->withHeader('X-Jitsi-Token', $this->secret)
            ->postJson('/api/v1/webhooks/jitsi', [
                'event' => 'room_destroyed',
                'room_name' => $meeting->jitsi_room_name,
            ]);

        $response->assertStatus(200);
        $this->assertEquals('completed', $meeting->fresh()->status);
        $this->assertNotNull($meeting->fresh()->ended_at);
    }
}

