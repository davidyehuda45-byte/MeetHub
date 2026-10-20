@extends('layouts.app', ['title' => 'Lupa Kata Sandi - MeetHub'])

@section('content')
<div class="min-h-[70vh] flex flex-col justify-center items-center py-6 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-700 to-emerald-600 flex items-center justify-center text-white mx-auto mb-4 shadow-lg shadow-teal-900/30">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        </div>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
            Pemulihan Kata Sandi
        </h2>
        <p class="mt-1.5 text-xs sm:text-sm text-slate-500">
            Masukkan email terdaftar untuk menerima tautan reset kata sandi.
        </p>
    </div>

    <div class="mt-7 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-200/50 rounded-3xl border border-slate-200/80 sm:px-8">
            <form class="space-y-4" action="{{ route('password.email') }}" method="POST">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Alamat Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                        placeholder="nama@email.com"
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600">
                    @error('email')
                        <p class="mt-1 text-[11px] text-rose-600 font-semibold">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3 px-4 rounded-2xl shadow-md shadow-teal-900/20 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 transition">
                        Kirim Tautan Pemulihan
                    </button>
                </div>

                <div class="text-center pt-3">
                    <a href="{{ route('login') }}" class="text-xs font-bold text-teal-700 hover:underline">
                        &larr; Kembali ke halaman masuk
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
