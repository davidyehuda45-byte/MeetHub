<?php

namespace App\Mail;

use App\Models\Meeting;
use App\Models\MeetingInvitation;
use App\Services\CalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MeetingInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Meeting $meeting;
    public MeetingInvitation $invitation;
    public string $inviterName;
    public string $icsContent;
    public string $googleCalendarUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        Meeting $meeting,
        MeetingInvitation $invitation,
        string $inviterName = 'Penyelenggara',
        ?string $icsContent = null
    ) {
        $this->meeting = $meeting;
        $this->invitation = $invitation;
        $this->inviterName = $inviterName;

        $calendarService = app(CalendarService::class);
        $this->icsContent = $icsContent ?? $calendarService->generateIcs($meeting);
        $this->googleCalendarUrl = $calendarService->generateGoogleCalendarUrl($meeting);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Undangan Rapat: ' . $this->meeting->title . ' - MeetHub',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.meeting_invitation',
            with: [
                'meeting' => $this->meeting,
                'invitation' => $this->invitation,
                'inviterName' => $this->inviterName,
                'googleCalendarUrl' => $this->googleCalendarUrl,
                'joinUrl' => route('meetings.lobby', $this->meeting->room_code),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(
                fn () => $this->icsContent,
                'meethub-' . $this->meeting->room_code . '.ics'
            )->withMime('text/calendar; charset=utf-8; method=REQUEST'),
        ];
    }
}

