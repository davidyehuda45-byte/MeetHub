@extends('layouts.app', ['title' => 'Monitoring Rapat - Admin MeetHub'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Monitoring Semua Rapat</h1>
            <p class="text-slate-500 text-xs mt-1">Pantau seluruh rapat aktif dan terjadwal di seluruh platform MeetHub.</p>
        </div>
    </div>

    <!-- Navigation Pills for Admin -->
    <div class="bg-slate-100/90 p-1 rounded-2xl inline-flex space-x-1 text-xs font-bold border border-slate-200/60">
        <a href="{{ route('admin.users') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Pengguna</a>
        <a href="{{ route('admin.meetings') }}" class="px-4 py-2 rounded-xl bg-white text-slate-900 shadow-xs">Semua Rapat</a>
        <a href="{{ route('admin.audit-logs') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Audit Log</a>
        <a href="{{ route('admin.settings') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Pengaturan</a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Rapat</span>
            <span class="text-2xl font-extrabold text-slate-900 block mt-1">{{ $stats['total'] }}</span>
        </div>
        <div class="bg-emerald-50/70 rounded-3xl p-5 border border-emerald-200/80 shadow-sm">
            <span class="text-xs text-emerald-700 font-bold uppercase tracking-wider flex items-center space-x-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Sedang Berlangsung</span>
            </span>
            <span class="text-2xl font-extrabold text-emerald-800 block mt-1">{{ $stats['ongoing'] }}</span>
        </div>
        <div class="bg-teal-50/70 rounded-3xl p-5 border border-teal-200/80 shadow-sm">
            <span class="text-xs text-teal-800 font-bold uppercase tracking-wider">Terjadwal</span>
            <span class="text-2xl font-extrabold text-teal-950 block mt-1">{{ $stats['scheduled'] }}</span>
        </div>
        <div class="bg-slate-50/80 rounded-3xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs text-slate-500 font-bold uppercase tracking-wider">Selesai</span>
            <span class="text-2xl font-extrabold text-slate-700 block mt-1">{{ $stats['ended'] }}</span>
        </div>
    </div>

    <!-- Meetings Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/70 text-slate-500 font-bold uppercase tracking-wider text-[11px] border-b border-slate-100">
                        <th class="py-3.5 px-6">Judul Rapat</th>
                        <th class="py-3.5 px-6">Host</th>
                        <th class="py-3.5 px-6">Kode Ruang</th>
                        <th class="py-3.5 px-6">Waktu Mulai</th>
                        <th class="py-3.5 px-6">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($meetings as $m)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6 font-bold text-slate-900 text-sm">
                            {{ $m->title }}
                        </td>
                        <td class="py-4 px-6 text-slate-700 font-medium">
                            {{ $m->host->name }}
                        </td>
                        <td class="py-4 px-6 font-mono text-xs text-teal-700 font-semibold">
                            {{ $m->room_code }}
                        </td>
                        <td class="py-4 px-6 text-slate-500 whitespace-nowrap font-mono text-[11px]">
                            {{ $m->scheduled_at->format('d M Y, H:i') }}
                        </td>
                        <td class="py-4 px-6">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $m->status === 'ongoing' ? 'bg-emerald-100 text-emerald-800' : ($m->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ ucfirst($m->status) }}
                            </span>
                        </td>
                        <td class="py-4 px-6 text-right space-x-1.5 whitespace-nowrap">
                            <a href="{{ route('meetings.show', $m->room_code) }}"
                                class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $meetings->links() }}
        </div>
    </div>
</div>
@endsection
