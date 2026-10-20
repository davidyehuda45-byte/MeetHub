<?php

namespace Tests\Feature;

use App\Mail\MeetingInvitationMail;
use App\Models\Meeting;
use App\Models\MeetingInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MeetingInvitationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_invitation_dispatches_queued_email_with_ics_attachment(): void
    {
        Mail::fake();

        $host = User::factory()->create([
            'name' => 'Budi Host',
            'role' => 'host',
        ]);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Penyelarasan Tim Backend',
            'description' => 'Sinkronisasi arsitektur microservices dan webhook.',
            'room_code' => 'mh-mail-1234',
            'jitsi_room_name' => 'meethub_mhmail1234',
            'scheduled_at' => now()->addDay()->setHour(10)->setMinute(0),
            'duration_minutes' => 60,
            'status' => 'scheduled',
        ]);

        $response = $this->actingAs($host)->postJson("/meetings/{$meeting->room_code}/invitations", [
            'email' => 'peserta@perusahaan.com',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        Mail::assertQueued(MeetingInvitationMail::class, function (MeetingInvitationMail $mail) use ($meeting) {
            $hasRecipient = $mail->hasTo('peserta@perusahaan.com');
            $hasCorrectSubject = str_contains($mail->envelope()->subject, 'Rapat Penyelarasan Tim Backend');
            $attachments = $mail->attachments();
            $hasIcs = count($attachments) > 0;

            return $hasRecipient && $hasCorrectSubject && $hasIcs;
        });
    }

    public function test_meeting_invitation_mailable_content_rendering(): void
    {
        $host = User::factory()->create(['name' => 'Dewi Santoso']);
        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Sinkronisasi Sprint',
            'description' => 'Review backlog sprint 4.',
            'room_code' => 'mh-mail-test',
            'jitsi_room_name' => 'meethub_mhmailtest',
            'scheduled_at' => now()->addHours(3),
            'duration_minutes' => 45,
            'status' => 'scheduled',
        ]);

        $invitation = MeetingInvitation::create([
            'meeting_id' => $meeting->id,
            'email' => 'rekan@example.com',
            'token' => 'randomtoken123',
            'status' => 'pending',
        ]);

        $mailable = new MeetingInvitationMail($meeting, $invitation, 'Dewi Santoso');

        $mailable->assertSeeInHtml('Rapat Sinkronisasi Sprint');
        $mailable->assertSeeInHtml('Dewi Santoso');
        $mailable->assertSeeInHtml('mh-mail-test');
        $mailable->assertSeeInHtml('Gabung ke Ruang Rapat');
        $mailable->assertSeeInHtml('Tambahkan ke Google Calendar');
    }
}

