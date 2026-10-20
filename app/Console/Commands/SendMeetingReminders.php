<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\Notification;
use Illuminate\Console\Command;

class SendMeetingReminders extends Command
{
    protected $signature = 'meethub:send-reminders';
    protected $description = 'Kirim pengingat untuk rapat yang akan dimulai dalam 15 menit ke depan';

    public function handle(): int
    {
        $start = now();
        $end = now()->addMinutes(15);

        $meetings = Meeting::with(['host', 'invitations.user'])
            ->where('status', 'scheduled')
            ->whereBetween('scheduled_at', [$start, $end])
            ->get();

        $count = 0;

        foreach ($meetings as $meeting) {
            // Notify host
            Notification::create([
                'user_id' => $meeting->host_id,
                'type' => 'meeting_reminder',
                'data' => [
                    'meeting_id' => $meeting->id,
                    'title' => $meeting->title,
                    'room_code' => $meeting->room_code,
                    'scheduled_at' => $meeting->scheduled_at->toIso8601String(),
                    'message' => "Rapat '{$meeting->title}' akan dimulai dalam beberapa menit.",
                ],
            ]);

            // Notify invited users
            foreach ($meeting->invitations as $invitation) {
                if ($invitation->user_id) {
                    Notification::create([
                        'user_id' => $invitation->user_id,
                        'type' => 'meeting_reminder',
                        'data' => [
                            'meeting_id' => $meeting->id,
                            'title' => $meeting->title,
                            'room_code' => $meeting->room_code,
                            'scheduled_at' => $meeting->scheduled_at->toIso8601String(),
                            'message' => "Anda memiliki undangan rapat '{$meeting->title}' yang segera dimulai.",
                        ],
                    ]);
                }
            }

            AuditLog::log('meeting_reminder_sent', Meeting::class, $meeting->id);
            $count++;
        }

        $this->info("Pengingat berhasil dikirim untuk {$count} rapat.");

        return Command::SUCCESS;
    }
}

