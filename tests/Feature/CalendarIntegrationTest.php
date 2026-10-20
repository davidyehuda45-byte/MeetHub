<?php

namespace Tests\Feature;

use App\Models\Meeting;
use App\Models\User;
use App\Services\CalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_download_meeting_ics_file(): void
    {
        $host = User::factory()->create([
            'role' => 'host',
        ]);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Rapat Strategi Kuartal MeetHub',
            'description' => 'Membahas roadmap dan target produk.',
            'room_code' => 'mh-cal-1234',
            'jitsi_room_name' => 'meethub_mhcal1234',
            'scheduled_at' => now()->addDay()->setHour(14)->setMinute(0)->setSecond(0),
            'duration_minutes' => 90,
            'status' => 'scheduled',
            'allow_guests' => true,
        ]);

        $response = $this->get("/meetings/{$meeting->room_code}/calendar.ics");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="meethub-mh-cal-1234.ics"');

        $content = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $content);
        $this->assertStringContainsString('VERSION:2.0', $content);
        $this->assertStringContainsString('BEGIN:VEVENT', $content);
        $this->assertStringContainsString('SUMMARY:Rapat Strategi Kuartal MeetHub', $content);
        $this->assertStringContainsString('UID:meethub-' . $meeting->id . '-mh-cal-1234@', $content);
        $this->assertStringContainsString('TRIGGER:-PT15M', $content);
        $this->assertStringContainsString('STATUS:CONFIRMED', $content);
        $this->assertStringContainsString('END:VEVENT', $content);
        $this->assertStringContainsString('END:VCALENDAR', $content);
    }

    public function test_google_calendar_url_generator(): void
    {
        $host = User::factory()->create();

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => 'Diskusi Google Meet vs MeetHub',
            'description' => 'Komparasi performa dan latensi.',
            'room_code' => 'mh-goog-5678',
            'jitsi_room_name' => 'meethub_mhgoog5678',
            'scheduled_at' => now()->addDays(2),
            'duration_minutes' => 45,
            'status' => 'scheduled',
        ]);

        $service = new CalendarService();
        $url = $service->generateGoogleCalendarUrl($meeting);

        $this->assertStringStartsWith('https://calendar.google.com/calendar/render?', $url);
        $this->assertStringContainsString('action=TEMPLATE', $url);
        $this->assertStringContainsString('Diskusi+Google+Meet+vs+MeetHub', $url);
        $this->assertStringContainsString('mh-goog-5678', $url);
    }

    public function test_calendar_ics_returns_404_for_nonexistent_meeting(): void
    {
        $response = $this->get('/meetings/non-existent-code/calendar.ics');
        $response->assertStatus(404);
    }
}

