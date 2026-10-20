@extends('layouts.app', ['title' => 'Jadwalkan Rapat - MeetHub'])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-1.5 text-xs text-teal-700 hover:text-teal-900 font-bold mb-3 transition">
            <span>&larr;</span>
            <span>Kembali ke Beranda</span>
        </a>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Jadwalkan Rapat Baru</h1>
        <p class="text-slate-500 text-xs sm:text-sm mt-1">Konfigurasikan ruang video conference, jadwal, dan kontrol akses peserta.</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('meetings.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Judul Rapat <span class="text-rose-500">*</span>
                </label>
                <input type="text" id="title" name="title" required value="{{ old('title') }}"
                    placeholder="Contoh: Rapat Koordinasi Mingguan Tim Engineering"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-transparent">
                @error('title') <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Agenda / Deskripsi (Opsional)
                </label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Tuliskan pokok bahasan atau poin penting yang akan didiskusikan..."
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-600 leading-relaxed"></textarea>
            </div>

            <!-- Date, Time, Duration Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="scheduled_date" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Tanggal <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" id="scheduled_date" name="scheduled_date" required value="{{ old('scheduled_date', date('Y-m-d')) }}"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>

                <div>
                    <label for="scheduled_time" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Jam Mulai <span class="text-rose-500">*</span>
                    </label>
                    <input type="time" id="scheduled_time" name="scheduled_time" required value="{{ old('scheduled_time', date('H:i')) }}"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>

                <div>
                    <label for="duration_minutes" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Durasi Rapat
                    </label>
                    <select id="duration_minutes" name="duration_minutes" class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600 bg-white">
                        <option value="30" {{ old('duration_minutes') == 30 ? 'selected' : '' }}>30 Menit</option>
                        <option value="60" {{ old('duration_minutes', 60) == 60 ? 'selected' : '' }}>1 Jam (60 Menit)</option>
                        <option value="90" {{ old('duration_minutes') == 90 ? 'selected' : '' }}>1.5 Jam (90 Menit)</option>
                        <option value="120" {{ old('duration_minutes') == 120 ? 'selected' : '' }}>2 Jam (120 Menit)</option>
                        <option value="180" {{ old('duration_minutes') == 180 ? 'selected' : '' }}>3 Jam (180 Menit)</option>
                    </select>
                </div>
            </div>

            <!-- Security & Room Controls -->
            <div class="border-t border-slate-100 pt-6 space-y-5">
                <h3 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">Keamanan & Kontrol Ruangan</h3>

                <!-- Room Password -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Kata Sandi Ruangan (Opsional)
                    </label>
                    <p class="text-[11px] text-slate-400 mb-1.5">Kosongkan jika ruangan terbuka untuk siapa saja yang memiliki link.</p>
                    <input type="password" id="password" name="password" placeholder="Buat kata sandi aman (opsional)..."
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>

                <!-- Toggles (Modern Switch Style) -->
                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="pr-4">
                            <span class="block text-xs font-bold text-slate-800">Izinkan Tamu Bergabung (Guest Access)</span>
                            <span class="block text-[11px] text-slate-500">Peserta tanpa akun MeetHub dapat masuk hanya dengan mengetikkan nama tampilan.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="allow_guests" value="1" {{ old('allow_guests', '1') ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="pr-4">
                            <span class="block text-xs font-bold text-slate-800">Aktifkan Ruang Tunggu (Lobby)</span>
                            <span class="block text-[11px] text-slate-500">Peserta harus disetujui Host/Moderator sebelum masuk ke ruang utama.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="lobby_enabled" value="1" {{ old('lobby_enabled') ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="pr-4">
                            <span class="block text-xs font-bold text-slate-800">Mulai dengan Mikrofon Terbisu (Muted)</span>
                            <span class="block text-[11px] text-slate-500">Mikrofon peserta otomatis dimatikan saat pertama kali bergabung ke ruangan.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" name="start_muted" value="1" {{ old('start_muted') ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="border-t border-slate-100 pt-6 flex justify-end space-x-3">
                <a href="{{ route('dashboard') }}" class="py-2.5 px-5 border border-slate-200/90 text-slate-700 hover:bg-slate-50 font-bold rounded-2xl text-xs transition">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-6 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-2xl text-xs shadow-md shadow-teal-900/20 transition">
                    Simpan & Jadwalkan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
