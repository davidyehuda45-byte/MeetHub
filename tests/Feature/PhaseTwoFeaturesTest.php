<?php

namespace Tests\Feature;

use App\Models\ActionItem;
use App\Models\Meeting;
use App\Models\MeetingFile;
use App\Models\MeetingNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseTwoFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_autosave_meeting_notes(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Notulensi',
            'room_code' => 'mh-note-1234',
            'jitsi_room_name' => 'meethub_mhnote1234',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'ongoing',
            'allow_guests' => true,
        ]);

        $response = $this->actingAs($host)->putJson("/meetings/{$meeting->room_code}/notes", [
            'content' => 'Poin 1: Hasil diskusi menyepakati peluncuran minggu depan.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('meeting_notes', [
            'meeting_id' => $meeting->id,
            'content' => 'Poin 1: Hasil diskusi menyepakati peluncuran minggu depan.',
        ]);
    }

    public function test_can_create_and_toggle_action_item(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Action Item',
            'room_code' => 'mh-task-1234',
            'jitsi_room_name' => 'meethub_mhtask1234',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'ongoing',
        ]);

        $response = $this->actingAs($host)->postJson("/meetings/{$meeting->room_code}/action-items", [
            'title' => 'Siapkan dokumen presentasi',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('action_items', [
            'meeting_id' => $meeting->id,
            'title' => 'Siapkan dokumen presentasi',
            'is_done' => false,
        ]);

        $item = ActionItem::where('title', 'Siapkan dokumen presentasi')->first();

        // Toggle task
        $toggleRes = $this->actingAs($host)->postJson("/action-items/{$item->id}/toggle");
        $toggleRes->assertStatus(200);
        $toggleRes->assertJson(['status' => 'success', 'is_done' => true]);

        $this->assertTrue($item->fresh()->is_done);
    }

    public function test_can_upload_and_download_meeting_file(): void
    {
        Storage::fake('local');

        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Berkas',
            'room_code' => 'mh-file-1234',
            'jitsi_room_name' => 'meethub_mhfile1234',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'ongoing',
        ]);

        $fakePdf = UploadedFile::fake()->create('materi_presentasi.pdf', 500, 'application/pdf');

        $uploadRes = $this->actingAs($host)->postJson("/meetings/{$meeting->room_code}/files", [
            'file' => $fakePdf,
        ]);

        $uploadRes->assertStatus(200);
        $uploadRes->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('meeting_files', [
            'meeting_id' => $meeting->id,
            'original_name' => 'materi_presentasi.pdf',
        ]);

        $fileRecord = MeetingFile::where('original_name', 'materi_presentasi.pdf')->first();
        Storage::disk('local')->assertExists($fileRecord->stored_path);

        $downloadRes = $this->actingAs($host)->get("/files/{$fileRecord->id}");
        $downloadRes->assertStatus(200);
    }

    public function test_host_can_export_attendance_csv(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Presensi Export',
            'room_code' => 'mh-att-1234',
            'jitsi_room_name' => 'meethub_mhatt1234',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'ended',
        ]);

        $response = $this->actingAs($host)->get("/meetings/{$meeting->room_code}/attendance/export");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $regularUser = User::factory()->create(['role' => 'user']);

        // Regular user forbidden
        $forbiddenRes = $this->actingAs($regularUser)->get('/admin/users');
        $forbiddenRes->assertStatus(403);

        // Admin allowed
        $adminRes = $this->actingAs($admin)->get('/admin/users');
        $adminRes->assertStatus(200);
        $adminRes->assertSee('Manajemen Pengguna');
    }

    public function test_can_send_meeting_invitation(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $participant = User::factory()->create(['email' => 'peserta.invite@meethub.id']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Undangan',
            'room_code' => 'mh-inv-1234',
            'jitsi_room_name' => 'meethub_mhinv1234',
            'scheduled_at' => now()->addHour(),
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($host)->postJson("/meetings/{$meeting->room_code}/invitations", [
            'email' => 'peserta.invite@meethub.id',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('meeting_invitations', [
            'meeting_id' => $meeting->id,
            'email' => 'peserta.invite@meethub.id',
            'user_id' => $participant->id,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $participant->id,
            'type' => 'meeting_invitation',
        ]);
    }

    public function test_send_meeting_reminders_command(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Segera Mulai',
            'room_code' => 'mh-rem-1234',
            'jitsi_room_name' => 'meethub_mhrem1234',
            'scheduled_at' => now()->addMinutes(5),
            'duration_minutes' => 30,
            'status' => 'scheduled',
        ]);

        $this->artisan('meethub:send-reminders')
            ->expectsOutputToContain('Pengingat berhasil dikirim')
            ->assertExitCode(0);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $host->id,
            'type' => 'meeting_reminder',
        ]);
    }

    public function test_health_check_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'database',
                'storage',
                'jitsi_domain',
                'version',
            ]);
    }

    public function test_admin_settings_management(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // View settings
        $viewRes = $this->actingAs($admin)->get('/admin/settings');
        $viewRes->assertStatus(200);
        $viewRes->assertSee('Pengaturan Sistem');

        // Update settings
        $postRes = $this->actingAs($admin)->post('/admin/settings', [
            'app_name' => 'MeetHub Enterprise',
            'jitsi_domain' => 'jitsi.mycorp.internal',
            'allow_guests_default' => '1',
            'max_file_size_mb' => 50,
        ]);

        $postRes->assertRedirect();
        $this->assertEquals('jitsi.mycorp.internal', \App\Models\Setting::get('jitsi_domain'));
        $this->assertEquals('MeetHub Enterprise', \App\Models\Setting::get('app_name'));
        $this->assertEquals('50', \App\Models\Setting::get('max_file_size_mb'));
    }
}

