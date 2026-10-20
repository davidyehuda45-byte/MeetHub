@extends('layouts.app', ['title' => 'Daftar - MeetHub'])

@section('content')
<div class="min-h-[75vh] flex flex-col justify-center items-center py-6 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-700 to-emerald-600 flex items-center justify-center text-white mx-auto mb-4 shadow-lg shadow-teal-900/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Buat Akun MeetHub Baru
        </h2>
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500">
            Mulai selenggarakan pertemuan video berstandar enterprise.
        </p>
    </div>

    <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-200/80 sm:px-8">
            <form class="space-y-4" action="{{ route('register') }}" method="POST">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Lengkap</label>
                    <input id="name" name="name" type="text" required value="{{ old('name') }}" placeholder="Contoh: Budi Pratama"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                    @error('name')
                        <p class="mt-1 text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                    <input id="email" name="email" type="email" required value="{{ old('email') }}" placeholder="budi@kantor.com"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                    @error('email')
                        <p class="mt-1 text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Role Selection -->
                <div>
                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tipe Akun</label>
                    <select id="role" name="role" class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600 bg-white">
                        <option value="host" {{ old('role') === 'host' ? 'selected' : '' }}>Host (Penyelenggara / Bisa Buat Rapat)</option>
                        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Peserta (Mengikuti Rapat)</option>
                    </select>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Kata Sandi (Min. 8 Karakter)</label>
                    <input id="password" name="password" type="password" required placeholder="••••••••"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                    @error('password')
                        <p class="mt-1 text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Ulangi Kata Sandi</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required placeholder="••••••••"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-2xl shadow-md shadow-teal-900/20 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 transition">
                        Daftar Akun Sekarang
                    </button>
                </div>
            </form>

            <!-- Login Link -->
            <div class="mt-6 pt-5 border-t border-slate-100 text-center text-xs text-slate-500">
                Sudah memiliki akun? <a href="{{ route('login') }}" class="font-bold text-teal-700 hover:underline">Masuk di sini</a>
            </div>
        </div>
    </div>
</div>
@endsection
