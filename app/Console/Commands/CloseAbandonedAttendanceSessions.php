<?php

namespace App\Console\Commands;

use App\Models\AttendanceSession;
use App\Services\AttendanceService;
use Illuminate\Console\Command;

class CloseAbandonedAttendanceSessions extends Command
{
    protected $signature = 'meethub:close-abandoned-sessions';
    protected $description = 'Tutup otomatis sesi presensi yang terputus tanpa event leave (heartbeat timeout > 2 menit)';

    public function handle(AttendanceService $attendanceService): int
    {
        $timeoutThreshold = now()->subMinutes(2);

        $abandonedSessions = AttendanceSession::whereNull('left_at')
            ->where('last_heartbeat_at', '<', $timeoutThreshold)
            ->get();

        $closed = 0;

        foreach ($abandonedSessions as $session) {
            $session->update([
                'left_at' => $session->last_heartbeat_at ?? $session->joined_at,
            ]);

            $attendanceService->updateSummaryAfterEvent($session);
            $closed++;
        }

        $this->info("Berhasil menutup {$closed} sesi presensi yang ditinggalkan.");

        return Command::SUCCESS;
    }
}

