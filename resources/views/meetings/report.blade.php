<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rapat - {{ $meeting->title }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 12pt; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 p-4 sm:p-8 font-sans antialiased">
    <div class="max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12">
        <!-- Top Controls (Hidden on Print) -->
        <div class="no-print flex justify-between items-center mb-8 pb-4 border-b border-slate-100">
            <a href="{{ route('meetings.show', $meeting->room_code) }}" class="text-xs font-semibold text-teal-700 hover:text-teal-900 flex items-center space-x-1">
                <span>&larr; Kembali ke Detail Rapat</span>
            </a>
            <button onclick="window.print()" class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>

        <!-- Document Header -->
        <div class="flex justify-between items-start border-b-2 border-slate-900 pb-6 mb-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-teal-700">Laporan Resmi Rapat</span>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ $meeting->title }}</h1>
                <p class="text-slate-500 text-xs mt-1">Kode Ruang: <span class="font-mono font-bold text-slate-700">{{ $meeting->room_code }}</span></p>
            </div>
            <div class="text-right">
                <div class="text-xl font-black text-teal-700">Meet<span class="text-slate-900">Hub</span></div>
                <p class="text-[10px] text-slate-400">Dicetak: {{ now()->translatedFormat('d M Y, H:i') }}</p>
            </div>
        </div>

        <!-- Meeting Metadata -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50 rounded-xl p-4 mb-8 text-xs border border-slate-100">
            <div>
                <span class="block text-slate-400 font-medium">Penyelenggara:</span>
                <span class="font-bold text-slate-800">{{ $meeting->host->name }}</span>
            </div>
            <div>
                <span class="block text-slate-400 font-medium">Waktu Rapat:</span>
                <span class="font-bold text-slate-800">{{ $meeting->scheduled_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <div>
                <span class="block text-slate-400 font-medium">Perkiraan Durasi:</span>
                <span class="font-bold text-slate-800">{{ $meeting->duration_minutes }} Menit</span>
            </div>
            <div>
                <span class="block text-slate-400 font-medium">Status:</span>
                <span class="font-bold text-slate-800 uppercase">{{ $meeting->status }}</span>
            </div>
        </div>

        <!-- Section 1: Presensi Peserta -->
        <div class="mb-8">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-2 mb-4 flex items-center justify-between">
                <span>1. Rekapitulasi Presensi Peserta</span>
                <span class="text-xs font-normal text-slate-500">Total: {{ $meeting->attendanceSummaries->count() }} Orang</span>
            </h2>

            @if ($meeting->attendanceSummaries->isEmpty())
                <p class="text-xs text-slate-500 italic">Tidak ada catatan kehadiran yang terekam.</p>
            @else
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 font-semibold uppercase">
                            <th class="py-2">No</th>
                            <th class="py-2">Nama Peserta</th>
                            <th class="py-2">Tipe</th>
                            <th class="py-2">Waktu Masuk</th>
                            <th class="py-2">Durasi Hadir</th>
                            <th class="py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($meeting->attendanceSummaries as $index => $summary)
                        <tr>
                            <td class="py-2 font-mono text-slate-400">{{ $index + 1 }}</td>
                            <td class="py-2 font-semibold text-slate-900">
                                {{ $summary->user ? $summary->user->name : ($summary->guest_identifier . ' (Tamu)') }}
                            </td>
                            <td class="py-2 text-slate-600">{{ $summary->user ? 'Terdaftar' : 'Tamu' }}</td>
                            <td class="py-2 text-slate-600">{{ $summary->first_join_at ? $summary->first_join_at->format('H:i:s') : '-' }}</td>
                            <td class="py-2 font-mono text-slate-700">{{ $summary->formatted_duration }}</td>
                            <td class="py-2">
                                <span class="font-semibold {{ $summary->status === 'present' ? 'text-emerald-700' : 'text-amber-700' }}">
                                    {{ $summary->status === 'present' ? 'Hadir' : 'Terlambat' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <!-- Section 2: Notulensi Rapat -->
        <div class="mb-8">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-2 mb-4">
                2. Notulensi & Ringkasan Pembahasan
            </h2>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 leading-relaxed whitespace-pre-wrap min-h-[80px]">
                {{ $meeting->note && $meeting->note->content ? $meeting->note->content : 'Tidak ada notulensi yang dicatat.' }}
            </div>
        </div>

        <!-- Section 3: Tindak Lanjut (Action Items) -->
        <div class="mb-8">
            <h2 class="text-base font-bold text-slate-900 border-b border-slate-200 pb-2 mb-4 flex items-center justify-between">
                <span>3. Tindak Lanjut & Action Items</span>
                <span class="text-xs font-normal text-slate-500">{{ $meeting->actionItems->count() }} Butir</span>
            </h2>

            @if ($meeting->actionItems->isEmpty())
                <p class="text-xs text-slate-500 italic">Belum ada butir tindak lanjut yang dicatat.</p>
            @else
                <div class="space-y-2 text-xs">
                    @foreach ($meeting->actionItems as $item)
                    <div class="flex items-start space-x-2 py-1.5 border-b border-slate-100">
                        <span class="font-bold {{ $item->is_done ? 'text-emerald-600' : 'text-slate-400' }}">
                            {{ $item->is_done ? '[✓ Selesai]' : '[  Belum ]' }}
                        </span>
                        <div class="flex-1">
                            <span class="font-medium text-slate-900">{{ $item->title }}</span>
                            @if ($item->assignee)
                                <span class="text-slate-500 ml-2">&bull; PIC: {{ $item->assignee->name }}</span>
                            @endif
                            @if ($item->due_date)
                                <span class="text-slate-400 ml-2">&bull; Tenggat: {{ $item->due_date->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Footer Signature -->
        <div class="pt-8 border-t border-slate-200 flex justify-between items-end text-xs text-slate-500">
            <div>
                <p>Dokumen ini dibuat otomatis oleh sistem MeetHub.</p>
                <p>Kehadiran divalidasi melalui token kriptografi Jitsi WebRTC.</p>
            </div>
            <div class="text-center">
                <p class="mb-12">Penyelenggara Rapat (Host),</p>
                <p class="font-bold text-slate-800 underline">{{ $meeting->host->name }}</p>
            </div>
        </div>
    </div>
</body>
</html>

