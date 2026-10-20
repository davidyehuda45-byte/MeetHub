<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran - {{ $meeting->title }}</title>
    <style>
        @page {
            margin: 28px 32px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.45;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f766e;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand-title {
            font-size: 20px;
            font-weight: bold;
            color: #0f766e;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .brand-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-title {
            text-align: right;
            vertical-align: top;
        }
        .doc-badge {
            background-color: #f0fdfa;
            color: #0f766e;
            border: 1px solid #ccfbf1;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
            display: inline-block;
        }
        .print-date {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 4px;
        }
        .meta-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 16px;
        }
        .meta-table {
            width: 100%;
        }
        .meta-label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            width: 18%;
        }
        .meta-value {
            font-size: 11px;
            font-weight: bold;
            color: #0f172a;
            width: 32%;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #0f766e;
            border-left: 3px solid #0f766e;
            padding-left: 8px;
            margin-top: 18px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-align: left;
            padding: 6px 8px;
            font-size: 9px;
            text-transform: uppercase;
            border: 1px solid #cbd5e1;
            font-weight: bold;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .status-present {
            color: #059669;
            font-weight: bold;
        }
        .status-late {
            color: #d97706;
            font-weight: bold;
        }
        .status-absent {
            color: #dc2626;
            font-weight: bold;
        }
        .notes-box {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 10px;
            font-size: 10px;
            line-height: 1.5;
            color: #334155;
            white-space: pre-wrap;
            margin-bottom: 16px;
        }
        .stats-grid {
            width: 100%;
            margin-bottom: 16px;
        }
        .stat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            padding: 8px;
            text-align: center;
        }
        .stat-val {
            font-size: 14px;
            font-weight: bold;
            color: #0f766e;
        }
        .stat-lbl {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .sign-col {
            width: 45%;
            text-align: center;
        }
        .sign-line {
            border-bottom: 1px solid #0f172a;
            width: 180px;
            margin: 45px auto 4px auto;
        }
        .footer-note {
            margin-top: 20px;
            font-size: 8px;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    <!-- Header Letterhead -->
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-title">MeetHub</div>
                <div class="brand-sub">Platform Konferensi Video & Presensi Digital Terverifikasi</div>
            </td>
            <td class="doc-title">
                <span class="doc-badge">Dokumen Resmi Presensi</span>
                <div class="print-date">Dicetak: {{ now()->translatedFormat('d F Y, H:i:s') }} WIB</div>
            </td>
        </tr>
    </table>

    <!-- Meeting Metadata Box -->
    <div class="meta-box">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Nama Rapat:</td>
                <td class="meta-value">{{ $meeting->title }}</td>
                <td class="meta-label">Kode Rapat:</td>
                <td class="meta-value" style="font-family: monospace;">{{ $meeting->room_code }}</td>
            </tr>
            <tr>
                <td class="meta-label">Penyelenggara:</td>
                <td class="meta-value">{{ $meeting->host->name }} ({{ $meeting->host->email }})</td>
                <td class="meta-label">Waktu Mulai:</td>
                <td class="meta-value">{{ $meeting->scheduled_at ? $meeting->scheduled_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}</td>
            </tr>
            <tr>
                <td class="meta-label">Durasi Terjadwal:</td>
                <td class="meta-value">{{ $meeting->duration_minutes }} Menit</td>
                <td class="meta-label">Status Sesi:</td>
                <td class="meta-value" style="text-transform: uppercase;">{{ $meeting->status }}</td>
            </tr>
        </table>
    </div>

    <!-- Attendance Summary Statistics -->
    @php
        $totalPresent = $meeting->attendanceSummaries->whereIn('status', ['present', 'late'])->count();
        $totalMinutes = round($meeting->attendanceSummaries->sum('total_seconds') / 60);
        $avgDuration = $totalPresent > 0 ? round($totalMinutes / $totalPresent) : 0;
    @endphp
    <table class="stats-grid">
        <tr>
            <td style="width: 32%; padding-right: 4px;">
                <div class="stat-card">
                    <div class="stat-val">{{ $totalPresent }} Peserta</div>
                    <div class="stat-lbl">Total Hadir</div>
                </div>
            </td>
            <td style="width: 36%; padding: 0 4px;">
                <div class="stat-card">
                    <div class="stat-val">{{ $totalMinutes }} Menit</div>
                    <div class="stat-lbl">Akumulasi Waktu Kehadiran</div>
                </div>
            </td>
            <td style="width: 32%; padding-left: 4px;">
                <div class="stat-card">
                    <div class="stat-val">{{ $avgDuration }} Menit / Orang</div>
                    <div class="stat-lbl">Rata-rata Waktu Partisipasi</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Attendance Table -->
    <div class="section-title">Daftar Kehadiran Peserta</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">No</th>
                <th>Nama Peserta</th>
                <th>Email / Identitas</th>
                <th style="width: 80px;">Tipe</th>
                <th style="width: 100px;">Waktu Pertama Masuk</th>
                <th style="width: 70px; text-align: center;">Durasi</th>
                <th style="width: 65px; text-align: center;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($meeting->attendanceSummaries as $index => $summary)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td><strong>{{ $summary->user ? $summary->user->name : ($summary->guest_identifier ?? 'Tamu') }}</strong></td>
                    <td style="font-size: 9px; color: #475569;">{{ $summary->user ? $summary->user->email : 'Akses Tamu (Guest)' }}</td>
                    <td style="font-size: 9px;">{{ $summary->user ? 'User Terdaftar' : 'Tamu Luar' }}</td>
                    <td style="font-size: 9px;">{{ $summary->first_join_at ? $summary->first_join_at->format('d/m/Y H:i') : '-' }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $summary->formatted_duration }}</td>
                    <td style="text-align: center;">
                        @if ($summary->status === 'present')
                            <span class="status-present">Hadir</span>
                        @elseif ($summary->status === 'late')
                            <span class="status-late">Terlambat</span>
                        @else
                            <span class="status-absent">Absen</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #94a3b8; padding: 12px;">Belum ada data presensi yang tercatat untuk rapat ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Meeting Notes Section -->
    @if ($meeting->note && trim($meeting->note->content))
        <div class="section-title">Ringkasan Notulensi Rapat</div>
        <div class="notes-box">{{ $meeting->note->content }}</div>
    @endif

    <!-- Action Items Checklist Section -->
    @if ($meeting->actionItems->count() > 0)
        <div class="section-title">Daftar Tindak Lanjut (Action Items)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th>Tugas / Tindak Lanjut</th>
                    <th style="width: 120px;">Penanggung Jawab</th>
                    <th style="width: 80px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($meeting->actionItems as $idx => $task)
                    <tr>
                        <td style="text-align: center;">{{ $idx + 1 }}</td>
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->assignee ? $task->assignee->name : '-' }}</td>
                        <td style="text-align: center; font-weight: bold; color: {{ $task->is_done ? '#059669' : '#d97706' }};">
                            {{ $task->is_done ? 'SELESAI' : 'PENDING' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- Signature Block -->
    <table class="signature-table">
        <tr>
            <td class="sign-col">
                <div>Mengetahui,</div>
                <div style="font-weight: bold; margin-top: 2px;">Administrator MeetHub</div>
                <div class="sign-line"></div>
                <div style="font-size: 9px; color: #64748b;">Sistem Terverifikasi Digital</div>
            </td>
            <td style="width: 10%;"></td>
            <td class="sign-col">
                <div>Penyelenggara Rapat (Host),</div>
                <div style="font-weight: bold; margin-top: 2px;">{{ $meeting->host->name }}</div>
                <div class="sign-line"></div>
                <div style="font-size: 9px; color: #64748b;">NIP / ID: {{ $meeting->host->id }}</div>
            </td>
        </tr>
    </table>

    <!-- Footer Note -->
    <div class="footer-note">
        Dokumen ini dibuat otomatis oleh MeetHub Server (ID Rapat: {{ $meeting->id }} &bull; Hash: {{ substr(md5($meeting->room_code . $meeting->created_at), 0, 12) }}).
    </div>
</body>
</html>

