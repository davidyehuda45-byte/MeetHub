<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Meeting;
use App\Models\MeetingNote;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MeetingService
{
    /**
     * Generate a unique, readable room code (e.g. mh-k3p8-x9v2).
     */
    public function generateRoomCode(): string
    {
        do {
            $part1 = strtolower(Str::random(4));
            $part2 = strtolower(Str::random(4));
            $code = "mh-{$part1}-{$part2}";
        } while (Meeting::where('room_code', $code)->exists());

        return $code;
    }

    /**
     * Generate a safe Jitsi room name.
     */
    public function generateJitsiRoomName(string $roomCode): string
    {
        $sanitized = preg_replace('/[^a-zA-Z0-9]/', '', $roomCode);
        return "meethub_{$sanitized}_" . Str::lower(Str::random(6));
    }

    /**
     * Create an instant meeting ("Mulai Sekarang").
     */
    public function createInstantMeeting(User $host, array $attributes = []): Meeting
    {
        $roomCode = $this->generateRoomCode();
        $jitsiRoom = $this->generateJitsiRoomName($roomCode);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => $attributes['title'] ?? ('Rapat Cepat ' . $host->name),
            'description' => $attributes['description'] ?? 'Rapat instan langsung dimulai',
            'room_code' => $roomCode,
            'jitsi_room_name' => $jitsiRoom,
            'password_hash' => !empty($attributes['password']) ? Hash::make($attributes['password']) : null,
            'scheduled_at' => now(),
            'duration_minutes' => $attributes['duration_minutes'] ?? 60,
            'status' => 'ongoing',
            'lobby_enabled' => (bool) ($attributes['lobby_enabled'] ?? false),
            'allow_guests' => (bool) ($attributes['allow_guests'] ?? true),
            'start_muted' => (bool) ($attributes['start_muted'] ?? false),
            'started_at' => now(),
        ]);

        // Initialize empty notes for this meeting
        MeetingNote::create([
            'meeting_id' => $meeting->id,
            'content' => '',
            'updated_by' => $host->id,
            'version' => 1,
        ]);

        AuditLog::log('meeting_created_instant', Meeting::class, $meeting->id, [
            'room_code' => $roomCode,
            'title' => $meeting->title,
        ], $host->id);

        return $meeting;
    }

    /**
     * Create a scheduled meeting.
     */
    public function createScheduledMeeting(User $host, array $data): Meeting
    {
        $roomCode = $this->generateRoomCode();
        $jitsiRoom = $this->generateJitsiRoomName($roomCode);

        $meeting = Meeting::create([
            'host_id' => $host->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'room_code' => $roomCode,
            'jitsi_room_name' => $jitsiRoom,
            'password_hash' => !empty($data['password']) ? Hash::make($data['password']) : null,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => (int) ($data['duration_minutes'] ?? 60),
            'status' => 'scheduled',
            'lobby_enabled' => (bool) ($data['lobby_enabled'] ?? false),
            'allow_guests' => (bool) ($data['allow_guests'] ?? true),
            'start_muted' => (bool) ($data['start_muted'] ?? false),
        ]);

        MeetingNote::create([
            'meeting_id' => $meeting->id,
            'content' => '',
            'updated_by' => $host->id,
            'version' => 1,
        ]);

        AuditLog::log('meeting_created_scheduled', Meeting::class, $meeting->id, [
            'room_code' => $roomCode,
            'title' => $meeting->title,
            'scheduled_at' => $meeting->scheduled_at->toIso8601String(),
        ], $host->id);

        return $meeting;
    }

    /**
     * Start a scheduled meeting.
     */
    public function startMeeting(Meeting $meeting, User $user): Meeting
    {
        if ($meeting->status === 'scheduled') {
            $meeting->update([
                'status' => 'ongoing',
                'started_at' => now(),
            ]);

            AuditLog::log('meeting_started', Meeting::class, $meeting->id, [], $user->id);
        }

        return $meeting;
    }

    /**
     * End an ongoing meeting.
     */
    public function endMeeting(Meeting $meeting, User $user): Meeting
    {
        $meeting->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        AuditLog::log('meeting_ended', Meeting::class, $meeting->id, [], $user->id);

        return $meeting;
    }

    /**
     * Cancel a meeting.
     */
    public function cancelMeeting(Meeting $meeting, User $user): Meeting
    {
        $meeting->update([
            'status' => 'cancelled',
        ]);

        AuditLog::log('meeting_cancelled', Meeting::class, $meeting->id, [], $user->id);

        return $meeting;
    }
}

