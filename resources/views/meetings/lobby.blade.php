@extends('layouts.app', ['title' => 'Lobi Rapat - ' . $meeting->title])

@section('content')
<div class="max-w-4xl mx-auto py-2" x-data="prejoinLobby()">
    <!-- Header -->
    <div class="text-center mb-6">
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-teal-50 border border-teal-100/80 text-teal-800 text-xs font-bold uppercase tracking-wider mb-2">
            <span class="w-2 h-2 rounded-full bg-teal-600 animate-pulse"></span>
            <span>Ruang Tunggu Pra-Rapat</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{{ $meeting->title }}</h1>
        <p class="text-slate-500 text-xs sm:text-sm mt-1">
            Host: <strong class="text-slate-700">{{ $meeting->host->name }}</strong> &bull; Kode Ruangan: <span class="font-mono text-teal-800 font-bold bg-teal-50 px-2 py-0.5 rounded-md border border-teal-100">{{ $meeting->room_code }}</span>
        </p>
    </div>

    <!-- Offline Connectivity Banner -->
    <div x-show="isOffline" x-cloak class="mb-5 p-4 rounded-3xl bg-rose-600 text-white text-xs shadow-lg flex items-center justify-between animate-pulse">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-2.828-5.657m2.828 5.657L3 21m0-18l18 18"></path></svg>
            </div>
            <div>
                <span class="font-bold block text-sm">Koneksi Internet Terputus</span>
                <span class="text-rose-100">Perangkat Anda offline. Periksa Wi-Fi atau data seluler Anda sebelum bergabung.</span>
            </div>
        </div>
    </div>

    <!-- In-App Browser Warning (FR-JOIN-06) -->
    <div x-show="isInAppBrowser" x-cloak class="mb-5 p-4 rounded-3xl bg-amber-500/10 border border-amber-500/20 text-amber-900 text-xs shadow-sm">
        <div class="flex items-start space-x-3">
            <span class="w-8 h-8 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold flex-shrink-0 mt-0.5 text-sm">!</span>
            <div>
                <span class="font-bold block text-sm text-amber-950 mb-0.5">Buka di Browser Utama (Chrome / Safari)</span>
                <span class="text-amber-800 leading-relaxed">Anda membuka link ini dari in-app browser aplikasi chat/sosial media. Izin kamera dan mikrofon biasanya dibatasi. Ketuk ikon titik tiga (⋮) di pojok dan pilih <strong>"Buka di Browser / Open in Chrome"</strong>.</span>
            </div>
        </div>
    </div>

    <!-- Mobile App Deep Link Offer (FR-JOIN-07) -->
    <div x-show="isMobileDevice" x-cloak class="mb-5 p-4 rounded-3xl bg-teal-50/80 border border-teal-200/80 text-teal-950 text-xs shadow-sm flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-xl bg-teal-700 text-white flex items-center justify-center flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <span class="font-bold block text-teal-950">Aplikasi Mobile MeetHub Tersedia</span>
                <span class="text-teal-800">Gunakan aplikasi Android/iOS untuk pengalaman share screen lebih lancar.</span>
            </div>
        </div>
        <a href="meethub://m/{{ $meeting->room_code }}"
            class="px-3.5 py-1.5 bg-teal-700 hover:bg-teal-800 text-white rounded-xl font-bold text-xs shadow-sm transition flex-shrink-0 ml-3">
            Buka di App
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <!-- Left: Studio Camera Viewfinder -->
        <div class="lg:col-span-7 bg-slate-950 rounded-3xl p-4 shadow-2xl ring-1 ring-white/10 overflow-hidden relative">
            <div class="aspect-video w-full bg-slate-900 rounded-2xl overflow-hidden relative flex items-center justify-center border border-white/5">
                <!-- Live Tag -->
                <div class="absolute top-3 left-3 z-10 px-2.5 py-1 rounded-full bg-slate-900/80 backdrop-blur-md border border-white/10 text-[10px] font-bold text-slate-200 flex items-center space-x-1.5">
                    <span class="w-2 h-2 rounded-full" :class="videoEnabled ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500'"></span>
                    <span x-text="videoEnabled ? 'KAMERA AKTIF' : 'KAMERA NONAKTIF'"></span>
                </div>

                <!-- Video Element -->
                <video id="cameraPreview" autoplay playsinline muted
                    class="w-full h-full object-cover transform -scale-x-100"
                    :class="{ 'hidden': !videoEnabled }"></video>

                <!-- Avatar fallback when video is off -->
                <div x-show="!videoEnabled" class="flex flex-col items-center justify-center text-slate-400">
                    <div class="w-24 h-24 rounded-3xl bg-gradient-to-tr from-teal-700 to-emerald-600 flex items-center justify-center text-white font-extrabold text-3xl shadow-xl shadow-teal-900/40 mb-3 border-2 border-white/10">
                        <span x-text="getInitials()">M</span>
                    </div>
                    <span class="text-xs font-semibold text-slate-400">Kamera dimatikan</span>
                </div>

                <!-- Mic Visualizer Bar (Floating) -->
                <div class="absolute bottom-3 left-3 right-3 bg-slate-900/90 backdrop-blur-md rounded-xl p-2.5 flex items-center space-x-3 text-white text-xs border border-white/10">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center" :class="audioEnabled ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                        </svg>
                    </div>
                    <div class="flex-1 bg-slate-800 h-2 rounded-full overflow-hidden">
                        <div id="micVolumeBar" class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-75 rounded-full" style="width: 0%"></div>
                    </div>
                    <span class="text-[10px] font-mono font-semibold" :class="audioEnabled ? 'text-emerald-400' : 'text-slate-400'" x-text="audioEnabled ? 'Mic OK' : 'Muted'">Mic OK</span>
                </div>
            </div>

            <!-- In-Preview Quick Toggle Controls -->
            <div class="flex items-center justify-center space-x-3 mt-4">
                <!-- Toggle Mic -->
                <button type="button" @click="toggleAudio()"
                    :class="audioEnabled ? 'bg-slate-800 hover:bg-slate-700 text-slate-200 border-white/10' : 'bg-rose-600 hover:bg-rose-700 text-white border-transparent'"
                    class="px-4 py-2.5 rounded-2xl border shadow-sm transition flex items-center space-x-2 text-xs font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                    </svg>
                    <span x-text="audioEnabled ? 'Matikan Mic' : 'Nyalakan Mic'"></span>
                </button>

                <!-- Toggle Video -->
                <button type="button" @click="toggleVideo()"
                    :class="videoEnabled ? 'bg-slate-800 hover:bg-slate-700 text-slate-200 border-white/10' : 'bg-rose-600 hover:bg-rose-700 text-white border-transparent'"
                    class="px-4 py-2.5 rounded-2xl border shadow-sm transition flex items-center space-x-2 text-xs font-bold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    <span x-text="videoEnabled ? 'Matikan Kamera' : 'Nyalakan Kamera'"></span>
                </button>
            </div>
        </div>

        <!-- Right: Join Form Card -->
        <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-sm">
            <h3 class="text-lg font-extrabold text-slate-900 tracking-tight mb-1">Siap Bergabung?</h3>
            <p class="text-xs text-slate-500 mb-6">Pastikan nama dan perangkat audio/video Anda telah sesuai.</p>

            <form action="{{ route('meetings.room', $meeting->room_code) }}" method="POST" class="space-y-4">
                @csrf

                <!-- Participant Name -->
                <div>
                    <label for="guest_name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Nama Anda</label>
                    <div>
                        @if ($user)
                        <div class="flex items-center space-x-2.5 px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800">
                            <span class="w-6 h-6 rounded-full bg-teal-700 text-white flex items-center justify-center text-[10px]">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </span>
                            <span>{{ $user->name }}</span>
                            <span class="text-[10px] text-slate-400 font-semibold">(Terautentikasi)</span>
                        </div>
                        <input type="hidden" name="guest_name" value="{{ $user->name }}">
                        @else
                        <input type="text" id="guest_name" name="guest_name" required x-model="guestName" placeholder="Ketik nama tampilan Anda..."
                            class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-600 focus:border-transparent">
                        @endif
                    </div>
                </div>

                <!-- Password Check if needed -->
                @if ($requiresPassword)
                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Kata Sandi Rapat <span class="text-rose-500">*</span>
                    </label>
                    <input type="password" id="password" name="password" required placeholder="Masukkan kata sandi ruangan..."
                        class="w-full px-4 py-2.5 border border-slate-300 rounded-2xl text-xs focus:outline-none focus:ring-2 focus:ring-teal-600">
                    @error('password')
                        <p class="text-[11px] text-rose-600 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Initial mute options passed as hidden inputs synced with alpine state -->
                <input type="hidden" name="start_muted_audio" :value="!audioEnabled ? 1 : 0">
                <input type="hidden" name="start_muted_video" :value="!videoEnabled ? 1 : 0">

                <!-- Submit Join Button -->
                <div class="pt-2">
                    <button type="submit"
                        class="w-full py-3.5 px-6 bg-gradient-to-r from-teal-700 to-emerald-700 hover:from-teal-800 hover:to-emerald-800 text-white font-extrabold rounded-2xl text-sm shadow-lg shadow-teal-700/20 transition flex items-center justify-center space-x-2">
                        <span>Masuk ke Ruang Rapat</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-100 text-[11px] text-slate-400 space-y-1.5 leading-relaxed">
                <p class="flex items-center space-x-1.5">
                    <span class="text-emerald-500 font-bold">✓</span>
                    <span>Presensi waktu hadir dicatat secara otomatis.</span>
                </p>
                @if ($meeting->lobby_enabled)
                <p class="flex items-center space-x-1.5 text-amber-700 font-medium">
                    <span>⏳</span>
                    <span>Lobby aktif: Host akan menerima Anda masuk ruangan.</span>
                </p>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function prejoinLobby() {
    return {
        videoEnabled: true,
        audioEnabled: true,
        isInAppBrowser: false,
        isMobileDevice: false,
        isOffline: !navigator.onLine,
        guestName: '{{ $user ? $user->name : old('guest_name', '') }}',
        mediaStream: null,
        audioContext: null,
        analyser: null,
        dataArray: null,
        animFrameId: null,

        init() {
            window.addEventListener('offline', () => { this.isOffline = true; });
            window.addEventListener('online', () => { this.isOffline = false; });

            const ua = navigator.userAgent || navigator.vendor || window.opera || '';
            this.isInAppBrowser = /(FBAN|FBAV|Instagram|Line|WhatsApp|MicroMessenger)/i.test(ua);
            this.isMobileDevice = /Android|iPhone|iPad|iPod/i.test(ua);
            this.startMedia();
        },

        getInitials() {
            const name = this.guestName.trim() || 'Tamu';
            return name.substring(0, 1).toUpperCase();
        },

        async startMedia() {
            try {
                this.mediaStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: 1280, height: 720 },
                    audio: true
                });

                const videoEl = document.getElementById('cameraPreview');
                if (videoEl) {
                    videoEl.srcObject = this.mediaStream;
                }

                this.setupAudioMeter(this.mediaStream);
            } catch (err) {
                console.warn('Izin kamera/audio belum diberikan:', err);
                this.videoEnabled = false;
                this.audioEnabled = false;
            }
        },

        setupAudioMeter(stream) {
            try {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                this.audioContext = new AudioContext();
                const source = this.audioContext.createMediaStreamSource(stream);
                this.analyser = this.audioContext.createAnalyser();
                this.analyser.fftSize = 64;
                source.connect(this.analyser);

                this.dataArray = new Uint8Array(this.analyser.frequencyBinCount);

                const updateVolume = () => {
                    if (!this.audioEnabled) {
                        const bar = document.getElementById('micVolumeBar');
                        if (bar) bar.style.width = '0%';
                        this.animFrameId = requestAnimationFrame(updateVolume);
                        return;
                    }

                    this.analyser.getByteFrequencyData(this.dataArray);
                    let sum = 0;
                    for (let i = 0; i < this.dataArray.length; i++) {
                        sum += this.dataArray[i];
                    }
                    const average = sum / this.dataArray.length;
                    const percent = Math.min(100, Math.round((average / 128) * 100));

                    const bar = document.getElementById('micVolumeBar');
                    if (bar) {
                        bar.style.width = percent + '%';
                    }
                    this.animFrameId = requestAnimationFrame(updateVolume);
                };

                updateVolume();
            } catch (e) {
                console.error('Audio meter init error:', e);
            }
        },

        toggleAudio() {
            this.audioEnabled = !this.audioEnabled;
            if (this.mediaStream) {
                this.mediaStream.getAudioTracks().forEach(track => {
                    track.enabled = this.audioEnabled;
                });
            }
        },

        toggleVideo() {
            this.videoEnabled = !this.videoEnabled;
            if (this.mediaStream) {
                this.mediaStream.getVideoTracks().forEach(track => {
                    track.enabled = this.videoEnabled;
                });
            }
        }
    };
}
</script>
@endsection
