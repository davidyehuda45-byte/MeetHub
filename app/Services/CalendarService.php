<?php

namespace App\Services;

use App\Models\Meeting;
use Carbon\Carbon;

class CalendarService
{
    /**
     * Generate an RFC 5545 compliant iCalendar string (.ics) for a meeting.
     */
    public function generateIcs(Meeting $meeting): string
    {
        $appUrl = config('app.url', 'http://localhost');
        $meetingUrl = route('meetings.lobby', $meeting->room_code);
        $domain = parse_url($appUrl, PHP_URL_HOST) ?: 'meethub.local';

        $startTime = $meeting->scheduled_at ? Carbon::parse($meeting->scheduled_at) : Carbon::now();
        $endTime = (clone $startTime)->addMinutes($meeting->duration_minutes ?: 60);

        $dtStamp = Carbon::now('UTC')->format('Ymd\THis\Z');
        $dtStart = (clone $startTime)->setTimezone('UTC')->format('Ymd\THis\Z');
        $dtEnd = (clone $endTime)->setTimezone('UTC')->format('Ymd\THis\Z');

        $uid = sprintf('meethub-%d-%s@%s', $meeting->id, $meeting->room_code, $domain);
        $summary = $this->escapeIcsText($meeting->title);
        
        $descriptionText = sprintf(
            "%s\n\nBergabung ke ruang rapat MeetHub:\n%s\nKode Rapat: %s",
            $meeting->description ?: 'Rapat Online MeetHub',
            $meetingUrl,
            $meeting->room_code
        );
        $description = $this->escapeIcsText($descriptionText);

        $icsLines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//MeetHub//Video Conference//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' . $uid,
            'DTSTAMP:' . $dtStamp,
            'DTSTART:' . $dtStart,
            'DTEND:' . $dtEnd,
            'SUMMARY:' . $summary,
            'DESCRIPTION:' . $description,
            'URL;VALUE=URI:' . $meetingUrl,
            'LOCATION:' . $this->escapeIcsText($meetingUrl),
            'STATUS:CONFIRMED',
            'BEGIN:VALARM',
            'TRIGGER:-PT15M',
            'ACTION:DISPLAY',
            'DESCRIPTION:Pengingat Rapat MeetHub: ' . $summary,
            'END:VALARM',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $icsLines) . "\r\n";
    }

    /**
     * Generate a direct Google Calendar template web link for a meeting.
     */
    public function generateGoogleCalendarUrl(Meeting $meeting): string
    {
        $meetingUrl = route('meetings.lobby', $meeting->room_code);
        $startTime = $meeting->scheduled_at ? Carbon::parse($meeting->scheduled_at) : Carbon::now();
        $endTime = (clone $startTime)->addMinutes($meeting->duration_minutes ?: 60);

        $dates = (clone $startTime)->setTimezone('UTC')->format('Ymd\THis\Z') . '/' . (clone $endTime)->setTimezone('UTC')->format('Ymd\THis\Z');

        $details = sprintf(
            "%s\n\nTautan Rapat MeetHub: %s\nKode: %s",
            $meeting->description ?: 'Rapat Online MeetHub',
            $meetingUrl,
            $meeting->room_code
        );

        $params = http_build_query([
            'action' => 'TEMPLATE',
            'text' => $meeting->title,
            'dates' => $dates,
            'details' => $details,
            'location' => $meetingUrl,
        ]);

        return 'https://calendar.google.com/calendar/render?' . $params;
    }

    /**
     * Escape special characters according to RFC 5545 section 3.3.11.
     */
    protected function escapeIcsText(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(';', '\;', $text);
        $text = str_replace(',', '\,', $text);
        $text = str_replace(["\r\n", "\n", "\r"], "\\n", $text);

        return $text;
    }
}

