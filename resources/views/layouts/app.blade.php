<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50/70">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'MeetHub') }} - Platform Rapat Online</title>

    <!-- Google Fonts Preconnect & Inter/Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Tailwind CSS & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen flex flex-col text-slate-800 antialiased font-sans selection:bg-teal-600 selection:text-white bg-ambient-pattern">
    <!-- Navbar -->
    <header class="bg-white/85 backdrop-blur-md border-b border-slate-200/70 sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Brand Logo -->
                <div class="flex items-center space-x-8">
                    <a href="{{ route('dashboard') }}" class="group flex items-center space-x-2.5 focus:outline-none">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-teal-700 via-teal-600 to-emerald-500 flex items-center justify-center text-white shadow-md shadow-teal-700/20 group-hover:scale-105 transition-transform duration-200">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="flex items-baseline">
                            <span class="text-lg font-extrabold tracking-tight text-slate-900">Meet</span>
                            <span class="text-lg font-extrabold tracking-tight bg-gradient-to-r from-teal-600 to-emerald-600 bg-clip-text text-transparent">Hub</span>
                            <span class="ml-1 px-1.5 py-0.2 rounded-md bg-teal-50 text-[10px] font-bold text-teal-800 uppercase tracking-wider border border-teal-200/60">PRO</span>
                        </div>
                    </a>

                    <!-- Navigation Links (Desktop) -->
                    @auth
                    <nav class="hidden md:flex items-center space-x-1 text-xs font-semibold">
                        <a href="{{ route('dashboard') }}"
                            class="px-3.5 py-2 rounded-xl transition {{ request()->routeIs('dashboard') ? 'bg-teal-50/80 text-teal-800 font-bold border border-teal-100/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/60' }}">
                            Beranda
                        </a>
                        <a href="{{ route('meetings.create') }}"
                            class="px-3.5 py-2 rounded-xl transition {{ request()->routeIs('meetings.create') ? 'bg-teal-50/80 text-teal-800 font-bold border border-teal-100/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/60' }}">
                            Jadwalkan Rapat
                        </a>
                        @if (Auth::user()->isAdmin())
                        <a href="{{ route('admin.users') }}"
                            class="px-3.5 py-2 rounded-xl transition {{ request()->is('admin*') ? 'bg-teal-50/80 text-teal-800 font-bold border border-teal-100/60' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/60' }}">
                            Admin Panel
                        </a>
                        @endif
                    </nav>
                    @endauth
                </div>

                <!-- User Controls -->
                <div class="flex items-center space-x-3">
                    @auth
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2.5 p-1.5 rounded-xl hover:bg-slate-100/80 transition focus:outline-none">
                            <span class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-700 to-emerald-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </span>
                            <div class="hidden sm:block text-left">
                                <span class="block text-xs font-bold text-slate-800 leading-tight">{{ Auth::user()->name }}</span>
                                <span class="text-[10px] text-slate-500 uppercase tracking-wider font-semibold">{{ ucfirst(Auth::user()->role) }}</span>
                            </div>
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100 py-2 z-50 text-xs">
                            <div class="px-4 py-2 border-b border-slate-100">
                                <span class="font-bold text-slate-900 block truncate">{{ Auth::user()->name }}</span>
                                <span class="text-slate-500 font-mono text-[11px] block truncate">{{ Auth::user()->email }}</span>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('profile') }}" class="flex items-center space-x-2 px-4 py-2 text-slate-700 hover:bg-slate-50 transition font-medium">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span>Profil & Keamanan</span>
                                </a>
                                @if (Auth::user()->isAdmin())
                                <a href="{{ route('admin.users') }}" class="flex items-center space-x-2 px-4 py-2 text-slate-700 hover:bg-slate-50 transition font-medium">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>Admin Panel</span>
                                </a>
                                @endif
                            </div>
                            <div class="border-t border-slate-100 pt-1">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center space-x-2 text-left px-4 py-2 text-rose-600 hover:bg-rose-50/80 transition font-medium">
                                        <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                        <span>Keluar Akun</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @else
                    <a href="{{ route('login') }}" class="text-xs font-bold text-slate-700 hover:text-teal-600 px-3.5 py-2 transition">Masuk</a>
                    <a href="{{ route('register') }}" class="text-xs font-bold bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-xl shadow-md shadow-teal-600/20 transition">Daftar</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Flash Messages (Floating Alert Toast) -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        @if (session('success'))
        <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-900 flex items-center justify-between text-xs font-semibold shadow-sm backdrop-blur-sm">
            <div class="flex items-center space-x-2.5">
                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 text-xs font-bold">✓</span>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-700 hover:text-emerald-900 text-sm font-bold">&times;</button>
        </div>
        @endif

        @if (session('error'))
        <div x-data="{ show: true }" x-show="show" class="mb-4 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-900 flex items-center justify-between text-xs font-semibold shadow-sm backdrop-blur-sm">
            <div class="flex items-center space-x-2.5">
                <span class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center flex-shrink-0 text-xs font-bold">!</span>
                <span>{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-rose-700 hover:text-rose-900 text-sm font-bold">&times;</button>
        </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white/60 border-t border-slate-200/70 py-6 mt-12 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <span class="font-extrabold text-slate-800 tracking-tight">Meet<span class="text-teal-600">Hub</span></span>
                <span>&bull;</span>
                <span>Platform Rapat Online Aman Berbasis WebRTC Jitsi</span>
            </div>
            <div class="text-slate-400 text-[11px]">
                &copy; {{ date('Y') }} MeetHub Enterprise. Seluruh hak cipta dilindungi.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
