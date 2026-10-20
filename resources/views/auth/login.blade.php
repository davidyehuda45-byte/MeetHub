@extends('layouts.app', ['title' => 'Masuk - MeetHub'])

@section('content')
<div class="min-h-[75vh] flex flex-col justify-center items-center py-6 sm:px-6 lg:px-8" x-data="{ showPass: false }">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-700 to-emerald-600 flex items-center justify-center text-white mx-auto mb-4 shadow-lg shadow-teal-900/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Selamat Datang di MeetHub
        </h2>
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500">
            Masuk untuk mulai atau kelola ruang pertemuan virtual Anda.
        </p>
    </div>

    <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-200/80 sm:px-8">
            <form class="space-y-4" action="{{ route('login') }}" method="POST">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path></svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                            placeholder="nama@email.com"
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-transparent">
                    </div>
                    @error('email')
                        <p class="mt-1 text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700">Kata Sandi</label>
                        <a href="{{ route('password.request') }}" class="text-[11px] font-bold text-teal-700 hover:text-teal-800">
                            Lupa kata sandi?
                        </a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input id="password" name="password" :type="showPass ? 'text' : 'password'" autocomplete="current-password" required
                            placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-transparent">
                        <button type="button" @click="showPass = !showPass" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <span x-text="showPass ? '🙈' : '👁️'" class="text-xs"></span>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1 text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="flex items-center pt-1">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded-md">
                    <label for="remember" class="ml-2 block text-xs text-slate-600 font-medium cursor-pointer">Ingat saya di perangkat ini</label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-2xl shadow-md shadow-teal-900/20 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 transition">
                        Masuk ke Akun
                    </button>
                </div>
            </form>

            <!-- 1-Click Demo Logins -->
            <div class="mt-6 border-t border-slate-100 pt-5">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 text-center">Akun Demo (Isi Cepat):</span>
                <div class="grid grid-cols-3 gap-2">
                    <button type="button" onclick="fillCreds('host@meethub.id', 'password123')"
                        class="text-[11px] py-2 px-2 bg-teal-50/80 hover:bg-teal-100/80 text-teal-800 rounded-xl text-center font-bold border border-teal-100 transition shadow-2xs">
                        Host
                    </button>
                    <button type="button" onclick="fillCreds('peserta@meethub.id', 'password123')"
                        class="text-[11px] py-2 px-2 bg-slate-50 hover:bg-slate-100 text-slate-700 rounded-xl text-center font-bold border border-slate-200 transition shadow-2xs">
                        Peserta
                    </button>
                    <button type="button" onclick="fillCreds('admin@meethub.id', 'password123')"
                        class="text-[11px] py-2 px-2 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-xl text-center font-bold border border-amber-200 transition shadow-2xs">
                        Admin
                    </button>
                </div>
            </div>

            <!-- Register Link -->
            <div class="mt-5 text-center text-xs text-slate-500">
                Belum punya akun? <a href="{{ route('register') }}" class="font-bold text-teal-700 hover:underline">Daftar sekarang</a>
            </div>
        </div>
    </div>
</div>

<script>
    function fillCreds(email, password) {
        document.getElementById('email').value = email;
        document.getElementById('password').value = password;
    }
</script>
@endsection
