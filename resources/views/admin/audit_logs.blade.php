@extends('layouts.app', ['title' => 'Audit Log - Admin MeetHub'])

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Audit Log Sistem</h1>
        <p class="text-slate-500 text-xs mt-1">Catatan jejak aktivitas login, modifikasi rapat, berkas materi, dan pengaturan platform.</p>
    </div>

    <!-- Navigation Pills for Admin -->
    <div class="bg-slate-100/90 p-1 rounded-2xl inline-flex space-x-1 text-xs font-bold border border-slate-200/60">
        <a href="{{ route('admin.users') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Pengguna</a>
        <a href="{{ route('admin.meetings') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Semua Rapat</a>
        <a href="{{ route('admin.audit-logs') }}" class="px-4 py-2 rounded-xl bg-white text-slate-900 shadow-xs">Audit Log</a>
        <a href="{{ route('admin.settings') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Pengaturan</a>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/70 text-slate-500 font-bold uppercase tracking-wider text-[11px] border-b border-slate-100">
                        <th class="py-3.5 px-6">Waktu Kejadian</th>
                        <th class="py-3.5 px-6">Aksi (Action)</th>
                        <th class="py-3.5 px-6">Pengguna</th>
                        <th class="py-3.5 px-6">IP Address</th>
                        <th class="py-3.5 px-6">Detail Metadata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono text-[11px]">
                    @foreach ($logs as $log)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-6 text-slate-400 whitespace-nowrap">
                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="py-3.5 px-6 font-bold text-teal-800 font-mono">
                            {{ $log->action }}
                        </td>
                        <td class="py-3.5 px-6 text-slate-800 font-sans font-semibold">
                            {{ $log->user ? $log->user->name : 'Sistem / Tamu' }}
                        </td>
                        <td class="py-3.5 px-6 text-slate-500">
                            {{ $log->ip ?? '-' }}
                        </td>
                        <td class="py-3.5 px-6 text-slate-500 font-mono text-[10px] max-w-xs truncate">
                            {{ $log->metadata ? json_encode($log->metadata) : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
