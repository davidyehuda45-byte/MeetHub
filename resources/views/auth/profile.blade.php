@extends('layouts.app', ['title' => 'Profil Saya - MeetHub'])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('dashboard') }}" class="inline-flex items-center space-x-1.5 text-xs text-teal-700 hover:text-teal-900 font-bold mb-3 transition">
            <span>&larr;</span>
            <span>Kembali ke Beranda</span>
        </a>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pengaturan Profil</h1>
        <p class="text-slate-500 text-xs sm:text-sm mt-1">Kelola identitas akun, zona waktu, dan kata sandi keamanan Anda.</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8">
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Avatar Preview & Role -->
            <div class="flex items-center space-x-4 pb-6 border-b border-slate-100">
                <div class="w-16 h-16 rounded-3xl bg-gradient-to-tr from-teal-700 to-emerald-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg shadow-teal-900/30">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">{{ $user->name }}</h3>
                    <p class="text-slate-500 text-xs font-mono">{{ $user->email }}</p>
                    <span class="inline-block mt-1.5 px-3 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $user->role === 'admin' ? 'bg-amber-100 text-amber-800' : ($user->role === 'host' ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-700') }}">
                        Peran: {{ ucfirst($user->role) }}
                    </span>
                </div>
            </div>

            <!-- Name -->
            <div>
                <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap</label>
                <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                    class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                @error('name') <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
            </div>

            <!-- Email (Readonly) -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                <input type="email" disabled value="{{ $user->email }}"
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs text-slate-500 font-mono cursor-not-allowed">
            </div>

            <!-- Timezone -->
            <div>
                <label for="timezone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Zona Waktu</label>
                <select id="timezone" name="timezone" class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600 bg-white">
                    <option value="Asia/Jakarta" {{ $user->timezone === 'Asia/Jakarta' ? 'selected' : '' }}>WIB - Asia/Jakarta (GMT+7)</option>
                    <option value="Asia/Makassar" {{ $user->timezone === 'Asia/Makassar' ? 'selected' : '' }}>WITA - Asia/Makassar (GMT+8)</option>
                    <option value="Asia/Jayapura" {{ $user->timezone === 'Asia/Jayapura' ? 'selected' : '' }}>WIT - Asia/Jayapura (GMT+9)</option>
                </select>
            </div>

            <!-- Change Password Section -->
            <div class="border-t border-slate-100 pt-6 space-y-4">
                <div>
                    <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Ubah Kata Sandi</h4>
                    <p class="text-slate-400 text-[11px] mt-0.5">Biarkan kosong jika tidak ingin mengganti kata sandi.</p>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi Baru</label>
                    <input type="password" id="password" name="password" placeholder="Minimal 8 karakter"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                    @error('password') <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi baru"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>
            </div>

            <!-- Submit -->
            <div class="border-t border-slate-100 pt-6 flex justify-end">
                <button type="submit" class="py-2.5 px-6 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-2xl text-xs shadow-md shadow-teal-900/20 transition">
                    Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
