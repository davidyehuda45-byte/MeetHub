@extends('layouts.app', ['title' => 'Manajemen Pengguna - Admin MeetHub'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
            <p class="text-slate-500 text-xs mt-1">Kelola akun, peran wewenang (Role), dan status aktivasi pengguna.</p>
        </div>

        <form method="GET" action="{{ route('admin.users') }}" class="flex items-center space-x-2">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau email..."
                class="px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-600">
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-slate-800 transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Navigation Pills for Admin -->
    <div class="bg-slate-100/90 p-1 rounded-2xl inline-flex space-x-1 text-xs font-bold border border-slate-200/60">
        <a href="{{ route('admin.users') }}" class="px-4 py-2 rounded-xl bg-white text-slate-900 shadow-xs">Pengguna</a>
        <a href="{{ route('admin.meetings') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Semua Rapat</a>
        <a href="{{ route('admin.audit-logs') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Audit Log</a>
        <a href="{{ route('admin.settings') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Pengaturan</a>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/70 text-slate-500 font-bold uppercase tracking-wider text-[11px] border-b border-slate-100">
                        <th class="py-3.5 px-6">Nama & Email</th>
                        <th class="py-3.5 px-6">Peran (Role)</th>
                        <th class="py-3.5 px-6">Status Akun</th>
                        <th class="py-3.5 px-6">Terdaftar Pada</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($users as $u)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-4 px-6">
                            <span class="font-bold text-slate-900 block text-sm">{{ $u->name }}</span>
                            <span class="text-slate-500 font-mono text-[11px]">{{ $u->email }}</span>
                        </td>
                        <form action="{{ route('admin.users.update', $u->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <td class="py-4 px-6">
                                <select name="role" class="px-3 py-1.5 border border-slate-300 rounded-xl text-xs font-semibold bg-white focus:outline-none focus:ring-2 focus:ring-teal-600">
                                    <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="host" {{ $u->role === 'host' ? 'selected' : '' }}>Host</option>
                                    <option value="user" {{ $u->role === 'user' ? 'selected' : '' }}>Peserta</option>
                                </select>
                            </td>
                            <td class="py-4 px-6">
                                <select name="is_active" class="px-3 py-1.5 border border-slate-300 rounded-xl text-xs font-bold bg-white focus:outline-none focus:ring-2 focus:ring-teal-600 {{ $u->is_active ? 'text-emerald-700' : 'text-rose-700' }}">
                                    <option value="1" {{ $u->is_active ? 'selected' : '' }}>Aktif</option>
                                    <option value="0" {{ !$u->is_active ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </td>
                            <td class="py-4 px-6 text-slate-500 whitespace-nowrap font-mono text-[11px]">
                                {{ $u->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <button type="submit" class="px-3.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-xs font-bold shadow-xs transition">
                                    Simpan
                                </button>
                            </td>
                        </form>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
