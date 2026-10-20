<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_create_instant_meeting(): void
    {
        $host = User::factory()->create([
            'role' => 'host',
            'is_active' => true,
        ]);

        $response = $this->actingAs($host)->post(route('meetings.instant'));

        $this->assertDatabaseHas('meetings', [
            'host_id' => $host->id,
            'status' => 'ongoing',
        ]);

        $meeting = Meeting::where('host_id', $host->id)->first();
        $response->assertRedirect(route('meetings.room', $meeting->room_code));
    }

    public function test_host_can_schedule_meeting(): void
    {
        $host = User::factory()->create([
            'role' => 'host',
            'is_active' => true,
        ]);

        $response = $this->actingAs($host)->post(route('meetings.store'), [
            'title' => 'Rapat Proyek Baru',
            'description' => 'Membahas roadmap tim',
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'scheduled_time' => '10:00',
            'duration_minutes' => 60,
            'allow_guests' => 1,
            'lobby_enabled' => 0,
            'start_muted' => 1,
        ]);

        $this->assertDatabaseHas('meetings', [
            'title' => 'Rapat Proyek Baru',
            'status' => 'scheduled',
            'start_muted' => 1,
        ]);

        $meeting = Meeting::where('title', 'Rapat Proyek Baru')->first();
        $response->assertRedirect(route('meetings.show', $meeting->room_code));
    }

    public function test_guest_can_access_lobby(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Terbuka',
            'room_code' => 'mh-test-room',
            'jitsi_room_name' => 'meethub_mhtestroom',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'allow_guests' => true,
        ]);

        $response = $this->get(route('meetings.lobby', $meeting->room_code));
        $response->assertStatus(200);
        $response->assertSee('Rapat Terbuka');
        $response->assertSee('Ruang Tunggu Pra-Rapat');
    }

    public function test_api_join_returns_jitsi_jwt_and_config(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Mobile',
            'room_code' => 'mh-mobi-1234',
            'jitsi_room_name' => 'meethub_mhmobi1234',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'scheduled',
            'allow_guests' => true,
        ]);

        $response = $this->postJson("/api/v1/meetings/{$meeting->room_code}/join", [
            'guest_name' => 'Tamu Mobile',
            'platform' => 'android',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'jitsi_domain',
            'room_name',
            'room_code',
            'jwt',
            'role',
            'display_name',
            'session_token',
            'config',
        ]);

        $this->assertEquals('participant', $response->json('role'));
        $this->assertEquals('Tamu Mobile', $response->json('display_name'));
        $this->assertNotEmpty($response->json('jwt'));
        $this->assertNotEmpty($response->json('session_token'));
    }
}

