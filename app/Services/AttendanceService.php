<?php

namespace App\Services;

use App\Models\AttendanceSession;
use App\Models\AttendanceSummary;
use App\Models\Meeting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AttendanceService
{
    /**
     * Record a participant joining a meeting.
     */
    public function recordJoin(
        Meeting $meeting,
        ?User $user,
        ?string $guestName = null,
        string $platform = 'web',
        ?string $deviceInfo = null
    ): AttendanceSession {
        $sessionToken = Str::uuid()->toString();

        $session = AttendanceSession::create([
            'meeting_id' => $meeting->id,
            'user_id' => $user?->id,
            'guest_name' => $user ? null : ($guestName ?: 'Tamu'),
            'session_token' => $sessionToken,
            'joined_at' => now(),
            'last_heartbeat_at' => now(),
            'platform' => $platform,
            'device_info' => $deviceInfo,
        ]);

        // If meeting is not ongoing, make it ongoing
        if ($meeting->status === 'scheduled') {
            $meeting->update([
                'status' => 'ongoing',
                'started_at' => now(),
            ]);
        }

        $this->updateSummaryAfterEvent($session);

        return $session;
    }

    /**
     * Record a heartbeat from a participant.
     */
    public function recordHeartbeat(string $sessionToken): bool
    {
        $session = AttendanceSession::where('session_token', $sessionToken)
            ->whereNull('left_at')
            ->first();

        if (!$session) {
            return false;
        }

        $session->update([
            'last_heartbeat_at' => now(),
        ]);

        $this->updateSummaryAfterEvent($session);

        return true;
    }

    /**
     * Record a participant leaving a meeting.
     */
    public function recordLeave(string $sessionToken): bool
    {
        $session = AttendanceSession::where('session_token', $sessionToken)
            ->whereNull('left_at')
            ->first();

        if (!$session) {
            return false;
        }

        $session->update([
            'left_at' => now(),
            'last_heartbeat_at' => now(),
        ]);

        $this->updateSummaryAfterEvent($session);

        return true;
    }

    /**
     * Update AttendanceSummary aggregate for this participant.
     */
    public function updateSummaryAfterEvent(AttendanceSession $session): void
    {
        $meetingId = $session->meeting_id;
        $userId = $session->user_id;
        $guestIdentifier = $userId ? null : ($session->guest_name ?: 'Tamu');

        // Calculate total seconds across all sessions for this participant
        $sessions = AttendanceSession::where('meeting_id', $meetingId)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->when(!$userId, fn($q) => $q->whereNull('user_id')->where('guest_name', $session->guest_name))
            ->get();

        $totalSeconds = 0;
        $firstJoinAt = null;

        foreach ($sessions as $s) {
            if (!$firstJoinAt || $s->joined_at->lt($firstJoinAt)) {
                $firstJoinAt = $s->joined_at;
            }

            $endTime = $s->left_at ?? $s->last_heartbeat_at ?? now();
            $duration = max(0, $s->joined_at->diffInSeconds($endTime));
            $totalSeconds += $duration;
        }

        // Determine status (terlambat if joined > 15 mins after meeting scheduled start)
        $meeting = $session->meeting;
        $status = 'present';
        if ($meeting->scheduled_at && $firstJoinAt) {
            $diffMinutes = $meeting->scheduled_at->diffInMinutes($firstJoinAt, false);
            if ($diffMinutes > 15) {
                $status = 'late';
            }
        }

        AttendanceSummary::updateOrCreate(
            [
                'meeting_id' => $meetingId,
                'user_id' => $userId,
                'guest_identifier' => $guestIdentifier,
            ],
            [
                'total_seconds' => $totalSeconds,
                'first_join_at' => $firstJoinAt,
                'status' => $status,
            ]
        );
    }
}

