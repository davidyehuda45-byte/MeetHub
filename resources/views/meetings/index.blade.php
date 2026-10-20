@extends('layouts.app', ['title' => 'Dashboard - MeetHub'])

@section('content')
<div class="space-y-8">
    <!-- Top Hero Command Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- 1. Instant Meeting Card -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-800 via-teal-900 to-slate-950 p-6 text-white shadow-xl shadow-teal-900/15 flex flex-col justify-between group">
            <div class="absolute -right-6 -top-6 w-32 h-32 bg-teal-400/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-500"></div>
            <div>
                <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center mb-4 border border-white/15 shadow-inner">
                    <svg class="w-6 h-6 text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-teal-500/20 text-teal-200 text-[10px] font-bold uppercase tracking-wider mb-2 border border-teal-400/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>1-Klik Tanpa Setup</span>
                </div>
                <h3 class="text-xl font-extrabold tracking-tight">Rapat Instan</h3>
                <p class="text-teal-100/80 text-xs mt-1 leading-relaxed">Mulai video conference langsung dalam hitungan detik. Tanpa batas durasi.</p>
            </div>
            <form action="{{ route('meetings.instant') }}" method="POST" class="mt-6">
                @csrf
                <button type="submit" class="w-full py-3 px-4 bg-white hover:bg-teal-50 text-teal-900 font-bold rounded-2xl shadow-md shadow-black/10 transition-all duration-150 text-xs flex items-center justify-center space-x-2 group-hover:shadow-lg">
                    <svg class="w-4 h-4 fill-current text-teal-700" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path></svg>
                    <span>Mulai Sekarang</span>
                </button>
            </form>
        </div>

        <!-- 2. Schedule Meeting Card -->
        <div class="rounded-3xl bg-white p-6 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-teal-400/50 transition-all flex flex-col justify-between">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 flex items-center justify-center mb-4 border border-teal-100/60">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold uppercase tracking-wider mb-2">
                    <span>Terencana</span>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Jadwalkan Rapat</h3>
                <p class="text-slate-500 text-xs mt-1 leading-relaxed">Atur waktu, durasi, kata sandi, ruang tunggu pra-rapat, dan kelola undangan peserta.</p>
            </div>
            <div class="mt-6">
                <a href="{{ route('meetings.create') }}" class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-2xl shadow-sm transition text-xs flex items-center justify-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Atur Jadwal Baru</span>
                </a>
            </div>
        </div>

        <!-- 3. Join with Code Card -->
        <div class="rounded-3xl bg-white p-6 border border-slate-200/80 shadow-sm hover:shadow-md hover:border-emerald-400/50 transition-all flex flex-col justify-between" x-data="{ code: '' }">
            <div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4 border border-emerald-100/60">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </div>
                <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold uppercase tracking-wider mb-2">
                    <span>Masuk Cepat</span>
                </div>
                <h3 class="text-xl font-extrabold text-slate-900 tracking-tight">Gabung Ruangan</h3>
                <p class="text-slate-500 text-xs mt-1 leading-relaxed">Punya kode ruangan atau link undangan? Masukkan kodenya di bawah ini.</p>
            </div>
            <div class="mt-6">
                <div class="flex items-center space-x-2">
                    <input type="text" x-model="code" placeholder="mh-xxxx-xxxx"
                        @keydown.enter="if(code.trim()) { window.location.href = '/m/' + code.trim().replace(/^.*\/m\//, '') }"
                        class="flex-1 px-3.5 py-2.5 border border-slate-300 rounded-2xl text-xs font-mono uppercase focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-transparent placeholder:normal-case">
                    <button type="button" @click="if(code.trim()) { window.location.href = '/m/' + code.trim().replace(/^.*\/m\//, '') }"
                        class="px-4 py-2.5 bg-teal-700 hover:bg-teal-800 text-white text-xs font-bold rounded-2xl transition shadow-sm">
                        Masuk
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Active / Ongoing Meetings (Live Radar Banner) -->
    @if ($ongoingMeetings->isNotEmpty())
    <div>
        <div class="flex items-center space-x-2 mb-3">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-teal-600"></span>
            </span>
            <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Rapat Sedang Berlangsung</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($ongoingMeetings as $meeting)
            <div class="rounded-3xl bg-gradient-to-br from-teal-50/70 to-emerald-100/40 border border-teal-300/70 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex justify-between items-start mb-3">
                    <span class="px-2.5 py-1 bg-teal-700 text-white text-[10px] font-bold tracking-wide rounded-full shadow-xs">LIVE SEKARANG</span>
                    <span class="text-xs font-mono font-semibold text-teal-900 bg-teal-100/80 px-2 py-0.5 rounded-md border border-teal-200/60">{{ $meeting->room_code }}</span>
                </div>
                <h4 class="font-extrabold text-slate-900 text-base mb-1 truncate">{{ $meeting->title }}</h4>
                <p class="text-xs text-slate-600 mb-4 flex items-center space-x-1.5">
                    <span class="w-5 h-5 rounded-full bg-teal-200 text-teal-800 font-bold text-[10px] flex items-center justify-center">
                        {{ strtoupper(substr($meeting->host->name, 0, 1)) }}
                    </span>
                    <span>Host: <strong class="text-slate-800">{{ $meeting->host->name }}</strong></span>
                </p>
                <div class="flex space-x-2">
                    <a href="{{ route('meetings.room', $meeting->room_code) }}"
                        class="flex-1 py-2.5 px-3 bg-teal-700 hover:bg-teal-800 text-white font-bold text-xs rounded-xl text-center shadow-sm transition">
                        Masuk Ruangan
                    </a>
                    <a href="{{ route('meetings.show', $meeting->room_code) }}"
                        class="py-2.5 px-3 bg-white border border-teal-200 text-teal-800 hover:bg-teal-50 font-semibold text-xs rounded-xl text-center transition">
                        Detail
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Upcoming Meetings List -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 tracking-tight">Jadwal Rapat Mendatang</h2>
                <p class="text-slate-500 text-xs mt-0.5">Daftar agenda pertemuan virtual yang telah dijadwalkan.</p>
            </div>
            <div class="flex items-center space-x-2">
                <form method="GET" action="{{ route('dashboard') }}" class="flex items-center">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari judul / kode..."
                        class="px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-600">
                    <button type="submit" class="ml-1.5 px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl">
                        Cari
                    </button>
                    @if (!empty($search))
                    <a href="{{ route('dashboard') }}" class="ml-2 text-xs text-rose-600 font-semibold hover:underline">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        @if ($upcomingMeetings->isEmpty())
        <div class="py-16 text-center">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-slate-50 border border-slate-100 flex items-center justify-center text-slate-300 mb-4 shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <p class="text-slate-800 text-sm font-bold">Belum Ada Rapat Mendatang</p>
            <p class="text-slate-400 text-xs mt-1 max-w-sm mx-auto">Mulai dengan membuat jadwal rapat baru atau gunakan fitur rapat instan untuk berdiskusi sekarang.</p>
            <div class="mt-5">
                <a href="{{ route('meetings.create') }}" class="inline-flex items-center space-x-2 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-xs font-bold shadow-md shadow-teal-700/20 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>Jadwalkan Rapat Sekarang</span>
                </a>
            </div>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Agenda Rapat</th>
                        <th class="py-3.5 px-6">Waktu Mulai</th>
                        <th class="py-3.5 px-6">Durasi</th>
                        <th class="py-3.5 px-6">Host / Pembuat</th>
                        <th class="py-3.5 px-6">Kode Ruangan</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach ($upcomingMeetings as $meeting)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-4 px-6">
                            <a href="{{ route('meetings.show', $meeting->room_code) }}" class="font-bold text-slate-900 hover:text-teal-700 transition block text-sm">
                                {{ $meeting->title }}
                            </a>
                            @if ($meeting->description)
                            <span class="text-slate-400 text-[11px] line-clamp-1 mt-0.5">{{ $meeting->description }}</span>
                            @endif
                        </td>
                        <td class="py-4 px-6 text-slate-700 whitespace-nowrap">
                            <div class="font-semibold">{{ $meeting->scheduled_at->translatedFormat('d M Y') }}</div>
                            <div class="text-[11px] text-slate-400 font-mono">{{ $meeting->scheduled_at->translatedFormat('H:i') }} WIB</div>
                        </td>
                        <td class="py-4 px-6 text-slate-600">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 font-medium">{{ $meeting->duration_minutes }} Menit</span>
                        </td>
                        <td class="py-4 px-6 text-slate-700">
                            <div class="flex items-center space-x-2">
                                <span class="w-6 h-6 rounded-full bg-teal-100 text-teal-800 font-bold text-[10px] flex items-center justify-center">
                                    {{ strtoupper(substr($meeting->host->name, 0, 1)) }}
                                </span>
                                <span class="font-medium truncate max-w-[120px]">{{ $meeting->host->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-6 font-mono text-xs font-semibold text-teal-700">
                            <span class="bg-teal-50 px-2.5 py-1 rounded-lg border border-teal-100/80">{{ $meeting->room_code }}</span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                            <a href="{{ route('meetings.lobby', $meeting->room_code) }}"
                                class="inline-flex items-center px-3.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-xl shadow-xs transition">
                                Gabung
                            </a>
                            <a href="{{ route('meetings.show', $meeting->room_code) }}"
                                class="inline-flex items-center px-3.5 py-1.5 border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold rounded-xl transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $upcomingMeetings->links() }}
        </div>
        @endif
    </div>

    <!-- Past Meetings History -->
    @if ($pastMeetings->isNotEmpty())
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Riwayat Rapat Selesai</h2>
            <span class="text-xs text-slate-400 font-mono">{{ count($pastMeetings) }} Rapat Terakhir</span>
        </div>
        <div class="divide-y divide-slate-100 text-xs">
            @foreach ($pastMeetings as $past)
            <div class="py-3.5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div>
                    <h4 class="font-bold text-slate-800 text-sm hover:text-teal-700 transition">
                        <a href="{{ route('meetings.show', $past->room_code) }}">{{ $past->title }}</a>
                    </h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">
                        {{ $past->scheduled_at->translatedFormat('d M Y, H:i') }} WIB &bull; Host: {{ $past->host->name }} &bull; Kode: {{ $past->room_code }}
                    </p>
                </div>
                <div class="flex items-center space-x-2.5">
                    <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider {{ $past->status === 'ended' ? 'bg-slate-100 text-slate-600' : 'bg-rose-50 text-rose-700' }}">
                        {{ $past->status === 'ended' ? 'Selesai' : 'Dibatalkan' }}
                    </span>
                    <a href="{{ route('meetings.show', $past->room_code) }}" class="px-3 py-1 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">
                        Lihat Rekap
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
