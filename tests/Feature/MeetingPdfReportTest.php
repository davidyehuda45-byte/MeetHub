<?php

namespace Tests\Feature;

use App\Models\ActionItem;
use App\Models\AttendanceSummary;
use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingPdfReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_host_can_download_attendance_pdf_report(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $participant = User::factory()->create(['role' => 'user']);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Evaluasi Triwulan MeetHub',
            'room_code' => 'mh-pdf-1234',
            'jitsi_room_name' => 'meethub_mhpdf1234',
            'scheduled_at' => now(),
            'duration_minutes' => 60,
            'status' => 'completed',
        ]);

        AttendanceSummary::create([
            'meeting_id' => $meeting->id,
            'user_id' => $participant->id,
            'total_seconds' => 3400,
            'status' => 'present',
            'first_join_at' => now()->subHour(),
            'last_leave_at' => now(),
        ]);

        MeetingNote::create([
            'meeting_id' => $meeting->id,
            'content' => 'Poin pembahasan: Evaluasi performa WebRTC dan server relay.',
            'last_edited_by' => $host->id,
        ]);

        ActionItem::create([
            'meeting_id' => $meeting->id,
            'title' => 'Optimasi konfigurasi Coturn TURN/STUN',
            'is_done' => true,
        ]);

        $response = $this->actingAs($host)->get("/meetings/{$meeting->room_code}/attendance/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename=Laporan_Presensi_MeetHub_mh-pdf-1234_', $response->headers->get('Content-Disposition'));
        
        // Assert standard binary PDF file signature
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_admin_can_download_attendance_pdf_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $host = User::factory()->create(['role' => 'host']);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Manajemen Sistem',
            'room_code' => 'mh-pdf-admin',
            'jitsi_room_name' => 'meethub_mhpdfadmin',
            'scheduled_at' => now(),
            'duration_minutes' => 45,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($admin)->get("/meetings/{$meeting->room_code}/attendance/pdf");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_unauthorized_user_cannot_download_pdf_report(): void
    {
        $host = User::factory()->create(['role' => 'host']);
        $otherUser = User::factory()->create(['role' => 'user']);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Rahasia Direksi',
            'room_code' => 'mh-pdf-priv',
            'jitsi_room_name' => 'meethub_mhpdfpriv',
            'scheduled_at' => now(),
            'duration_minutes' => 30,
            'status' => 'ongoing',
        ]);

        $response = $this->actingAs($otherUser)->get("/meetings/{$meeting->room_code}/attendance/pdf");

        $response->assertStatus(403);
    }
}
