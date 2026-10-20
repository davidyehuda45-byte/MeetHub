<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MeetingAttendanceController extends Controller
{
    /**
     * Export attendance data as CSV (Excel compatible with UTF-8 BOM).
     */
    public function exportCsv(string $code): StreamedResponse
    {
        $meeting = Meeting::with(['attendanceSummaries.user', 'host'])
            ->where('room_code', $code)
            ->firstOrFail();

        Gate::authorize('exportAttendance', $meeting);

        $filename = "Presensi_MeetHub_{$meeting->room_code}_" . now()->format('Ymd_His') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($meeting) {
            $handle = fopen('php://output', 'w');

            // Add UTF-8 BOM for Microsoft Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Meeting Information Header
            fputcsv($handle, ['LAPORAN PRESENSI RAPAT MEETHUB']);
            fputcsv($handle, ['Judul Rapat', $meeting->title]);
            fputcsv($handle, ['Kode Ruang', $meeting->room_code]);
            fputcsv($handle, ['Host', $meeting->host->name]);
            fputcsv($handle, ['Waktu Mulai', $meeting->scheduled_at->format('Y-m-d H:i')]);
            fputcsv($handle, ['Status Rapat', ucfirst($meeting->status)]);
            fputcsv($handle, []); // blank line

            // Column Headers
            fputcsv($handle, [
                'No',
                'Nama Peserta',
                'Email',
                'Tipe Peserta',
                'Waktu Bergabung Pertama',
                'Total Detik',
                'Durasi Hadir (MM:SS)',
                'Status Kehadiran',
            ]);

            $no = 1;
            foreach ($meeting->attendanceSummaries as $summary) {
                $name = $summary->user ? $summary->user->name : ($summary->guest_identifier ?? 'Tamu');
                $email = $summary->user ? $summary->user->email : '-';
                $type = $summary->user ? 'User Terdaftar' : 'Tamu';
                $firstJoin = $summary->first_join_at ? $summary->first_join_at->format('Y-m-d H:i:s') : '-';
                $durationFormatted = $summary->formatted_duration;
                $statusText = match ($summary->status) {
                    'present' => 'Hadir',
                    'late' => 'Terlambat',
                    default => 'Absen',
                };

                fputcsv($handle, [
                    $no++,
                    $name,
                    $email,
                    $type,
                    $firstJoin,
                    $summary->total_seconds,
                    $durationFormatted,
                    $statusText,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Printable / PDF-ready meeting summary report.
     */
    public function summaryReport(string $code): View
    {
        $meeting = Meeting::with([
            'host',
            'attendanceSummaries.user',
            'attendanceSessions.user',
            'note',
            'actionItems.assignee',
            'files.uploader',
        ])->where('room_code', $code)->firstOrFail();

        Gate::authorize('view', $meeting);

        return view('meetings.report', compact('meeting'));
    }

    /**
     * Download formal Attendance PDF report.
     */
    public function downloadPdf(string $code): Response
    {
        $meeting = Meeting::with([
            'host',
            'attendanceSummaries.user',
            'attendanceSessions.user',
            'note',
            'actionItems.assignee',
            'files.uploader',
        ])->where('room_code', $code)->firstOrFail();

        Gate::authorize('exportAttendance', $meeting);

        $pdf = Pdf::loadView('pdf.attendance_report', compact('meeting'))
            ->setPaper('a4', 'portrait')
            ->setOption([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $filename = "Laporan_Presensi_MeetHub_{$meeting->room_code}_" . now()->format('Ymd_His') . ".pdf";

        return $pdf->download($filename);
    }
}

