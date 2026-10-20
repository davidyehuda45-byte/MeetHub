<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\MeetingRecording;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MeetingRecordingTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_and_participants_can_list_recordings(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Desain Arsitektur',
            'room_code' => 'mh-rec-1234',
            'jitsi_room_name' => 'meethub_mhrec1234',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);

        MeetingRecording::create([
            'meeting_id' => $meeting->id,
            'title' => 'Sesi 1 Pembahasan Arsitektur',
            'file_name' => 'rekaman_sesi1.mp4',
            'storage_disk' => 'local',
            'file_path' => 'recordings/mh-rec-1234/rekaman_sesi1.mp4',
            'file_size' => 15728640, // ~15MB
            'duration_seconds' => 3600, // 1 jam
            'mime_type' => 'video/mp4',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($host)->getJson("/meetings/{$meeting->room_code}/recordings");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'recordings' => [
                '*' => [
                    'id',
                    'title',
                    'file_name',
                    'file_size',
                    'duration',
                    'status',
                    'download_url',
                    'stream_url',
                ],
            ],
        ]);
        $response->assertJsonPath('recordings.0.duration', '01:00:00');
    }

    public function test_user_can_stream_and_download_recording_file(): void
    {
        Storage::fake('local');

        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Demo WebRTC',
            'room_code' => 'mh-rec-stream',
            'jitsi_room_name' => 'meethub_mhrecstream',
            'scheduled_at' => now(),
            'duration_minutes' => 30,
            'status' => 'completed',
        ]);

        $fakeVideoPath = 'recordings/test_video.mp4';
        Storage::disk('local')->put($fakeVideoPath, 'fake-mp4-video-content-stream-bytes');

        $recording = MeetingRecording::create([
            'meeting_id' => $meeting->id,
            'title' => 'Rekaman Demo',
            'file_name' => 'test_video.mp4',
            'storage_disk' => 'local',
            'file_path' => $fakeVideoPath,
            'file_size' => 35,
            'duration_seconds' => 120,
            'mime_type' => 'video/mp4',
            'status' => 'ready',
        ]);

        // 1. Download
        $downloadRes = $this->actingAs($host)->get("/recordings/{$recording->id}/download");
        $downloadRes->assertStatus(200);
        $this->assertStringContainsString('attachment; filename=test_video.mp4', $downloadRes->headers->get('Content-Disposition'));

        // 2. Stream
        $streamRes = $this->actingAs($host)->get("/recordings/{$recording->id}/stream");
        $streamRes->assertStatus(200);
        $streamRes->assertHeader('Content-Type', 'video/mp4');
        $streamRes->assertHeader('Accept-Ranges', 'bytes');
    }

    public function test_unauthorized_user_cannot_access_recording(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $stranger = User::factory()->create(['role' => 'user']);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Tertutup Tim Inti',
            'room_code' => 'mh-rec-locked',
            'jitsi_room_name' => 'meethub_mhreclocked',
            'scheduled_at' => now(),
            'duration_minutes' => 45,
            'status' => 'completed',
            'allow_guests' => false,
        ]);

        $recording = MeetingRecording::create([
            'meeting_id' => $meeting->id,
            'file_name' => 'secret_rec.mp4',
            'storage_disk' => 'local',
            'file_path' => 'recordings/secret_rec.mp4',
            'file_size' => 1000,
            'duration_seconds' => 60,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($stranger)->getJson("/meetings/{$meeting->room_code}/recordings");
        $response->assertStatus(403);

        $downloadRes = $this->actingAs($stranger)->get("/recordings/{$recording->id}/download");
        $downloadRes->assertStatus(403);
    }

    public function test_host_can_delete_recording(): void
    {
        Storage::fake('local');

        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Hapus Rekaman',
            'room_code' => 'mh-rec-del',
            'jitsi_room_name' => 'meethub_mhrecdel',
            'scheduled_at' => now(),
            'duration_minutes' => 15,
            'status' => 'completed',
        ]);

        $path = 'recordings/to_delete.mp4';
        Storage::disk('local')->put($path, 'sample-content');

        $recording = MeetingRecording::create([
            'meeting_id' => $meeting->id,
            'file_name' => 'to_delete.mp4',
            'storage_disk' => 'local',
            'file_path' => $path,
            'file_size' => 14,
            'duration_seconds' => 10,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($host)->deleteJson("/recordings/{$recording->id}");

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('meeting_recordings', ['id' => $recording->id]);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_webhook_creates_recording_record_on_recording_ready_event(): void
    {
        $secret = config('jitsi.webhook_secret');
        $host = User::factory()->create(['role' => 'host']);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Perekaman Otomatis Jibri',
            'room_code' => 'mh-jibri-rec',
            'jitsi_room_name' => 'meethub_mhjibrirec',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'ongoing',
        ]);

        $response = $this->withHeader('X-Jitsi-Token', $secret)
            ->postJson('/api/v1/webhooks/jitsi', [
                'event' => 'recording_ready',
                'room_name' => $meeting->jitsi_room_name,
                'recording' => [
                    'title' => 'Rekaman Final Jibri',
                    'file_name' => 'jibri_meeting_final.mp4',
                    'file_path' => 'recordings/jibri_meeting_final.mp4',
                    'file_size' => 52428800,
                    'duration' => 3540,
                    'storage_disk' => 'local',
                    'mime_type' => 'video/mp4',
                ],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('meeting_recordings', [
            'meeting_id' => $meeting->id,
            'file_name' => 'jibri_meeting_final.mp4',
            'duration_seconds' => 3540,
            'status' => 'ready',
        ]);
    }
}

