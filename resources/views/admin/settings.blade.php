@extends('layouts.app', ['title' => 'Pengaturan Sistem - Admin MeetHub'])

@section('content')
<div class="space-y-6 max-w-4xl">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pengaturan Sistem</h1>
        <p class="text-slate-500 text-xs mt-1">Konfigurasi global platform MeetHub, integrasi Jitsi Meet, dan batas upload materi.</p>
    </div>

    <!-- Navigation Pills for Admin -->
    <div class="bg-slate-100/90 p-1 rounded-2xl inline-flex space-x-1 text-xs font-bold border border-slate-200/60">
        <a href="{{ route('admin.users') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Pengguna</a>
        <a href="{{ route('admin.meetings') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Semua Rapat</a>
        <a href="{{ route('admin.audit-logs') }}" class="px-4 py-2 rounded-xl text-slate-500 hover:text-slate-800 transition">Audit Log</a>
        <a href="{{ route('admin.settings') }}" class="px-4 py-2 rounded-xl bg-white text-slate-900 shadow-xs">Pengaturan</a>
    </div>

    <!-- Settings Form -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
            @csrf

            <!-- App Name -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Aplikasi</label>
                <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name']) }}" required
                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-300 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                <p class="text-[11px] text-slate-400 mt-1">Nama branding yang tampil di header, email, dan notifikasi.</p>
            </div>

            <!-- Jitsi Domain -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Domain Server Jitsi Meet</label>
                <input type="text" name="jitsi_domain" value="{{ old('jitsi_domain', $settings['jitsi_domain']) }}" required
                    placeholder="meet.ffmuc.net atau jitsi.domainanda.com"
                    class="w-full px-4 py-2.5 rounded-2xl border border-slate-300 text-xs font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                <p class="text-[11px] text-slate-400 mt-1">
                    Gunakan server publik bebas login (<span class="font-mono text-teal-700 font-bold">meet.ffmuc.net</span>) atau domain server Jitsi Docker self-hosted Anda.
                </p>
            </div>

            <!-- Allow Guests Default -->
            <div class="pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                    <div class="pr-4">
                        <span class="block text-xs font-bold text-slate-800">Izinkan Tamu Masuk Rapat secara Default</span>
                        <span class="text-[11px] text-slate-500">Peserta tanpa akun MeetHub diizinkan bergabung ke ruangan dengan memasukkan nama tampilan.</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input type="checkbox" name="allow_guests_default" value="1" {{ $settings['allow_guests_default'] ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                    </label>
                </div>
            </div>

            <!-- Max File Size -->
            <div class="pt-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Ukuran Maksimal Berkas Materi (MB)</label>
                <div class="flex items-center space-x-3">
                    <input type="number" name="max_file_size_mb" value="{{ old('max_file_size_mb', $settings['max_file_size_mb']) }}" min="1" max="100" required
                        class="w-32 px-4 py-2.5 rounded-2xl border border-slate-300 text-xs font-mono font-bold focus:outline-none focus:ring-2 focus:ring-teal-600">
                    <span class="text-xs text-slate-500 font-medium">Megabytes (MB) per file materi rapat</span>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-2xl text-xs shadow-md shadow-teal-900/20 transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
