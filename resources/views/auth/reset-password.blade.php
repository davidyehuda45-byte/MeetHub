@extends('layouts.app', ['title' => 'Buat Kata Sandi Baru - MeetHub'])

@section('content')
<div class="min-h-[60vh] flex flex-col justify-center items-center py-6 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="text-center text-3xl font-extrabold text-slate-900 tracking-tight">
            Atur Kata Sandi Baru
        </h2>
        <p class="mt-2 text-center text-sm text-slate-600">
            Masukkan kata sandi baru untuk akun Anda.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-xl shadow-slate-100 rounded-2xl border border-slate-200 sm:px-10">
            <form class="space-y-4" action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">Alamat Email</label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" required value="{{ old('email', $email) }}"
                            class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-xl shadow-sm text-sm">
                    </div>
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700">Kata Sandi Baru</label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required placeholder="Minimal 8 karakter"
                            class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-xl shadow-sm text-sm">
                    </div>
                    @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi Baru</label>
                    <div class="mt-1">
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="appearance-none block w-full px-3 py-2 border border-slate-300 rounded-xl shadow-sm text-sm">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-md shadow-teal-900/20 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-600 transition">
                        Simpan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

