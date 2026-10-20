@extends('layouts.app', ['title' => $meeting->title . ' - MeetHub'])

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="meetingShowApp()">
    <!-- Breadcrumb & Header Summary -->
    <div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-1.5 text-xs text-teal-700 hover:text-teal-900 font-bold mb-3 transition">
            <span>&larr;</span>
            <span>Kembali ke Beranda</span>
        </a>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3 mb-1.5">
                    <span class="px-3 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $meeting->status === 'ongoing' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : ($meeting->status === 'scheduled' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700') }}">
                        {{ $meeting->status === 'ongoing' ? '● Berlangsung' : ($meeting->status === 'scheduled' ? 'Terjadwal' : 'Selesai') }}
                    </span>
                    <span class="text-xs text-slate-500 font-mono bg-slate-100 px-2.5 py-0.5 rounded-md font-semibold">Kode: {{ $meeting->room_code }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $meeting->title }}</h1>
            </div>

            <!-- Join & Manage Action Buttons -->
            <div class="flex items-center space-x-2">
                @if ($meeting->status !== 'ended' && $meeting->status !== 'cancelled')
                <a href="{{ route('meetings.room', $meeting->room_code) }}"
                    class="py-2.5 px-5 bg-gradient-to-r from-teal-700 to-emerald-700 hover:from-teal-800 hover:to-emerald-800 text-white font-bold rounded-2xl text-xs shadow-md shadow-teal-900/20 transition flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span>Masuk Ruangan</span>
                </a>
                @endif

                <a href="{{ route('meetings.report', $meeting->room_code) }}" target="_blank"
                    class="py-2.5 px-3.5 bg-white border border-slate-200/90 text-slate-700 hover:bg-slate-50 font-bold rounded-2xl text-xs shadow-xs transition flex items-center space-x-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>Laporan PDF</span>
                </a>

                @can('update', $meeting)
                    @if ($meeting->status === 'scheduled')
                    <a href="{{ route('meetings.edit', $meeting->room_code) }}"
                        class="py-2.5 px-3.5 bg-white border border-slate-200/90 text-slate-700 hover:bg-slate-50 font-bold rounded-2xl text-xs shadow-xs transition">
                        Edit
                    </a>
                    @endif

                    @if ($meeting->status === 'ongoing')
                    <form action="{{ route('meetings.end', $meeting->room_code) }}" method="POST" onsubmit="return confirm('Akhiri rapat untuk semua peserta?')">
                        @csrf
                        <button type="submit" class="py-2.5 px-3.5 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold rounded-2xl text-xs border border-rose-200/80 transition">
                            Akhiri
                        </button>
                    </form>
                    @endif

                    @if ($meeting->status === 'scheduled')
                    <form action="{{ route('meetings.destroy', $meeting->room_code) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan rapat ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="py-2.5 px-3.5 bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold rounded-2xl text-xs border border-rose-200/80 transition">
                            Batalkan
                        </button>
                    </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <!-- Quick Share & Invitation Dock -->
    <div class="rounded-3xl bg-gradient-to-r from-teal-50/90 via-emerald-50/50 to-teal-50/60 border border-teal-100/90 p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shadow-xs">
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-teal-950 mb-1 flex items-center space-x-1.5">
                <span>🔗</span>
                <span>Tautan Undangan Rapat</span>
            </h4>
            <p class="text-xs text-teal-800 font-mono select-all font-semibold">{{ $meeting->join_url }}</p>
        </div>
        <div class="flex items-center space-x-2">
            <button type="button" @click="navigator.clipboard.writeText('{{ $meeting->join_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                class="px-3.5 py-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-xl transition flex items-center space-x-1.5 shadow-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                <span x-text="copied ? 'Tersalin!' : 'Salin Tautan'">Salin Tautan</span>
            </button>
            <button type="button" @click="copyWhatsApp()"
                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition flex items-center space-x-1.5 shadow-xs">
                <span>💬</span>
                <span x-text="copiedWa ? 'Tersalin!' : 'Format WA'">Format WA</span>
            </button>
            <button type="button" @click="showQr = true"
                class="px-3 py-2 bg-white border border-teal-200 text-teal-800 hover:bg-teal-50 text-xs font-bold rounded-xl transition flex items-center space-x-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span>QR Code</span>
            </button>
            <a href="{{ route('meetings.lobby', $meeting->room_code) }}" target="_blank"
                class="px-3 py-2 bg-white border border-teal-200 text-teal-800 hover:bg-teal-50 text-xs font-bold rounded-xl transition">
                Buka Lobi
            </a>
        </div>
    </div>

    <!-- QR Code Modal Dialog -->
    <div x-show="showQr" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="showQr = false" class="bg-white rounded-3xl p-6 max-w-xs w-full text-center shadow-2xl">
            <h3 class="font-extrabold text-slate-900 text-base mb-1">Scan untuk Gabung</h3>
            <p class="text-xs text-slate-500 mb-4">Arahkan kamera smartphone ke QR Code:</p>
            <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 flex items-center justify-center mx-auto mb-4 shadow-inner">
                {!! $qrSvg !!}
            </div>
            <p class="font-mono text-xs text-teal-800 font-bold mb-4">{{ $meeting->room_code }}</p>
            <button @click="showQr = false" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                Tutup
            </button>
        </div>
    </div>

    <!-- Segmented Navigation Tabs -->
    <div class="bg-slate-100/90 p-1 rounded-2xl flex space-x-1 text-xs font-bold border border-slate-200/60">
        <button @click="activeTab = 'info'"
            :class="activeTab === 'info' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
            class="flex-1 py-2 rounded-xl transition text-center">
            Informasi Rapat
        </button>
        <button @click="activeTab = 'attendance'"
            :class="activeTab === 'attendance' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
            class="flex-1 py-2 rounded-xl transition text-center">
            Presensi ({{ $meeting->attendanceSummaries->count() }})
        </button>
        <button @click="activeTab = 'notes'"
            :class="activeTab === 'notes' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
            class="flex-1 py-2 rounded-xl transition text-center">
            Notulensi & Tugas ({{ $meeting->actionItems->count() }})
        </button>
        <button @click="activeTab = 'files'"
            :class="activeTab === 'files' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'"
            class="flex-1 py-2 rounded-xl transition text-center">
            Materi ({{ $meeting->files->count() }})
        </button>
    </div>

    <!-- Tab 1: Info -->
    <div x-show="activeTab === 'info'" class="space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-slate-400 mb-4">Detail & Pengaturan Rapat</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-slate-500 font-medium">Penyelenggara (Host)</dt>
                    <dd class="mt-1 font-bold text-slate-900 text-sm flex items-center space-x-2">
                        <span class="w-6 h-6 rounded-full bg-teal-700 text-white font-bold text-[10px] flex items-center justify-center">
                            {{ strtoupper(substr($meeting->host->name, 0, 1)) }}
                        </span>
                        <span>{{ $meeting->host->name }}</span>
                    </dd>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-slate-500 font-medium">Jadwal Mulai</dt>
                    <dd class="mt-1 font-bold text-slate-900 text-sm font-mono">
                        {{ $meeting->scheduled_at ? $meeting->scheduled_at->translatedFormat('l, d F Y - H:i') . ' WIB' : 'Langsung Sekarang' }}
                    </dd>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-slate-500 font-medium">Perkiraan Durasi</dt>
                    <dd class="mt-1 font-bold text-slate-900 text-sm">{{ $meeting->duration_minutes }} Menit</dd>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-slate-500 font-medium">Keamanan Sandi</dt>
                    <dd class="mt-1 font-bold text-slate-900 text-sm">
                        {{ $meeting->hasPassword() ? 'Dilindungi Kata Sandi' : 'Terbuka (Tanpa Sandi)' }}
                    </dd>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-slate-500 font-medium">Akses Tamu</dt>
                    <dd class="mt-1 font-bold text-slate-900 text-sm">
                        {{ $meeting->allow_guests ? 'Diizinkan (Tanpa Login)' : 'Hanya User Terdaftar' }}
                    </dd>
                </div>
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <dt class="text-slate-500 font-medium">Ruang Tunggu (Lobby)</dt>
                    <dd class="mt-1 font-bold text-slate-900 text-sm">
                        {{ $meeting->lobby_enabled ? 'Aktif (Perlu Approval)' : 'Non-aktif (Langsung Masuk)' }}
                    </dd>
                </div>
            </dl>

            @if ($meeting->description)
            <div class="mt-6 border-t border-slate-100 pt-5">
                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Agenda & Deskripsi</h4>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-slate-700 text-xs leading-relaxed whitespace-pre-line">
                    {{ $meeting->description }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Tab 2: Attendance -->
    <div x-show="activeTab === 'attendance'" class="space-y-4">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 tracking-tight">Catatan Kehadiran Otomatis</h3>
                    <p class="text-xs text-slate-500">Terekam realtime dari aktivitas peserta di ruang video conference.</p>
                </div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('meetings.attendance.export', $meeting->room_code) }}"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span>Unduh CSV (Excel)</span>
                    </a>
                </div>
            </div>

            @if ($meeting->attendanceSummaries->isEmpty())
            <div class="py-16 text-center text-slate-400 text-xs">
                Belum ada peserta yang bergabung ke rapat ini.
            </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                            <th class="py-3 px-6">Peserta</th>
                            <th class="py-3 px-6">Waktu Pertama Bergabung</th>
                            <th class="py-3 px-6">Total Durasi</th>
                            <th class="py-3 px-6">Status Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($meeting->attendanceSummaries as $att)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-6 font-bold text-slate-900">
                                {{ $att->user ? $att->user->name : ($att->guest_identifier . ' (Tamu)') }}
                            </td>
                            <td class="py-3.5 px-6 text-slate-600 font-mono">
                                {{ $att->first_join_at ? $att->first_join_at->format('H:i:s') . ' WIB' : '-' }}
                            </td>
                            <td class="py-3.5 px-6 font-mono text-slate-700">
                                <span class="bg-slate-100 px-2 py-0.5 rounded-md font-semibold">{{ $att->formatted_duration }}</span>
                            </td>
                            <td class="py-3.5 px-6">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $att->status === 'present' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $att->status === 'present' ? 'Hadir Tepat' : 'Terlambat' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <!-- Tab 3: Notes & Action Items -->
    <div x-show="activeTab === 'notes'" class="space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-900 mb-2">Notulensi Rapat</h3>
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-slate-700 text-xs leading-relaxed min-h-[120px] whitespace-pre-wrap font-sans">
                {{ $meeting->note && $meeting->note->content ? $meeting->note->content : 'Belum ada catatan notulensi untuk rapat ini.' }}
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm">
            <h3 class="text-sm font-extrabold text-slate-900 mb-3">Tindak Lanjut & Action Items</h3>
            @if ($meeting->actionItems->isEmpty())
                <p class="text-xs text-slate-400 italic">Belum ada tugas atau tindak lanjut.</p>
            @else
                <div class="space-y-2">
                    @foreach ($meeting->actionItems as $item)
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                        <div class="flex items-center space-x-2.5">
                            <span class="w-2.5 h-2.5 rounded-full {{ $item->is_done ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            <span class="{{ $item->is_done ? 'line-through text-slate-400' : 'font-bold text-slate-800' }}">{{ $item->title }}</span>
                        </div>
                        <div class="text-slate-500 text-[11px] font-medium">
                            {{ $item->assignee ? 'PIC: ' . $item->assignee->name : '' }}
                            {{ $item->due_date ? ' | Tenggat: ' . $item->due_date->format('d/m/Y') : '' }}
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Tab 4: Files -->
    <div x-show="activeTab === 'files'" class="space-y-4">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-7">
            <h3 class="text-sm font-extrabold text-slate-900 mb-4">Berkas Materi Rapat</h3>
            @if ($meeting->files->isEmpty())
                <p class="text-xs text-slate-400 italic">Belum ada materi atau berkas diunggah.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach ($meeting->files as $f)
                    <div class="p-3.5 bg-slate-50 border border-slate-200/80 rounded-2xl flex items-center justify-between hover:bg-slate-100/50 transition">
                        <div class="overflow-hidden mr-2">
                            <span class="font-bold text-slate-900 text-xs block truncate">{{ $f->original_name }}</span>
                            <span class="text-[10px] text-slate-500 font-mono">{{ $f->formatted_size }} &bull; Oleh {{ $f->uploader ? $f->uploader->name : 'Tamu' }}</span>
                        </div>
                        <a href="{{ route('files.download', $f->id) }}" class="px-3.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-xs font-bold transition flex-shrink-0 shadow-xs">
                            Unduh
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function meetingShowApp() {
    return {
        activeTab: 'info',
        copied: false,
        copiedWa: false,
        showQr: false,
        copyWhatsApp() {
            const topic = @json($meeting->title);
            const time = @json($meeting->scheduled_start_at ? $meeting->scheduled_start_at->translatedFormat('l, d F Y - H:i') . ' WIB' : 'Langsung Sekarang');
            const roomCode = @json($meeting->room_code);
            const passcode = @json($meeting->passcode ?? '');
            const joinUrl = @json($meeting->join_url);

            let msg = `*UNDANGAN RAPAT ONLINE - MEETHUB*\n\n` +
                      `*Topik:* ${topic}\n` +
                      `*Waktu:* ${time}\n` +
                      `*Kode Ruangan:* ${roomCode}\n`;

            if (passcode) {
                msg += `*Passcode:* ${passcode}\n`;
            }

            msg += `\n*Gabung melalui tautan:* \n${joinUrl}\n\n` +
                   `_Sampai jumpa di ruang rapat!_`;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(msg).then(() => {
                    this.copiedWa = true;
                    setTimeout(() => this.copiedWa = false, 2500);
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = msg;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                this.copiedWa = true;
                setTimeout(() => this.copiedWa = false, 2500);
            }
        }
    };
}
</script>
@endpush
