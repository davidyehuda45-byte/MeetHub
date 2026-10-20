<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $meeting->title }} - MeetHub Room</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Tailwind CSS & Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Jitsi Meet External API -->
    <script src="https://{{ $domain }}/external_api.js"></script>
</head>
<body class="h-full flex flex-col overflow-hidden text-slate-100 font-sans select-none bg-[#090d16]" x-data="meetingRoomApp()">
    <!-- Top Bar -->
    <header class="h-12 bg-[#090d16] border-b border-white/10 px-4 flex items-center justify-between z-30 flex-shrink-0">
        <div class="flex items-center space-x-3">
            <!-- Brand Icon -->
            <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-teal-700 to-emerald-600 flex items-center justify-center text-white font-extrabold text-xs shadow-md shadow-teal-900/40">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
            </div>

            <!-- Title & Badges -->
            <div class="flex items-center space-x-2">
                <h1 class="text-xs sm:text-sm font-bold text-slate-100 max-w-[140px] sm:max-w-xs md:max-w-sm truncate">{{ $meeting->title }}</h1>
                <button type="button" @click="navigator.clipboard.writeText('{{ $meeting->room_code }}'); codeCopied = true; setTimeout(() => codeCopied = false, 2000)"
                    class="hidden sm:inline-flex items-center space-x-1 px-2 py-0.5 rounded-md text-[10px] font-mono bg-white/5 hover:bg-white/10 text-slate-300 border border-white/10 transition">
                    <span>{{ $meeting->room_code }}</span>
                    <span x-text="codeCopied ? '✓' : '⧉'" class="text-[9px] text-teal-400"></span>
                </button>
                @if ($isModerator)
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                    Host
                </span>
                @endif
            </div>
        </div>

        <!-- Controls -->
        <div class="flex items-center space-x-2">
            <!-- Open in Pop-up / Tab Baru -->
            <button @click="openExternalWindow()" type="button"
                title="Buka ruang rapat di jendela baru atau tab terpisah"
                class="hidden sm:inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-slate-300 hover:text-white bg-white/5 hover:bg-white/10 border border-white/10 transition">
                <svg class="w-3.5 h-3.5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                <span>Pop-up Video</span>
            </button>

            <!-- Sidepanel Toggle Button -->
            <button @click="sidepanelOpen = !sidepanelOpen" type="button"
                :class="sidepanelOpen ? 'bg-teal-700 text-white border-teal-600' : 'bg-white/5 hover:bg-white/10 text-slate-300 border-white/10'"
                class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center space-x-1.5 border">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="hidden sm:inline">Panel Kolaborasi</span>
                <span x-show="tasks.length > 0 || files.length > 0" class="px-1.5 py-0.2 rounded-full bg-teal-600/40 text-[10px] text-white font-mono" x-text="tasks.length + files.length"></span>
            </button>

            <!-- Leave Meeting Button -->
            <button @click="showLeaveModal = true" type="button"
                class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow-sm shadow-rose-600/20">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                <span>Keluar</span>
            </button>
        </div>
    </header>

    <!-- Offline Connectivity Banner -->
    <div x-show="isOffline" x-cloak
        class="fixed top-14 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-rose-600/90 backdrop-blur-md text-white text-xs font-semibold rounded-2xl shadow-2xl flex items-center space-x-2 border border-rose-400/30 animate-pulse">
        <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-2.828-5.657m2.828 5.657L3 21m0-18l18 18"></path></svg>
        <span>Koneksi internet terputus. Mencoba menghubungkan kembali...</span>
    </div>

    <!-- Main Workspace (Video + Dockable Sidepanel) -->
    <div class="flex-1 w-full h-[calc(100%-3rem)] flex overflow-hidden relative">
        <!-- Jitsi IFrame Container & Overlays -->
        <div class="flex-1 h-full relative overflow-hidden bg-[#050811]">
            <div id="jitsi-container" class="w-full h-full"></div>

            <!-- Sleek Loading Curtain (Fades out when Jitsi connects) -->
            <div x-show="isLoadingVideo" 
                class="absolute inset-0 z-10 flex flex-col items-center justify-center bg-[#090d16] text-center p-6 transition-opacity duration-300">
                <div class="relative w-16 h-16 mb-4 flex items-center justify-center">
                    <div class="absolute inset-0 rounded-2xl bg-teal-500/20 animate-ping"></div>
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-teal-700 to-emerald-600 flex items-center justify-center text-white shadow-xl shadow-teal-900/50">
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-sm font-bold text-slate-100 mb-1">Menghubungkan ke Ruang Rapat...</h3>
                <p class="text-xs text-slate-400 max-w-sm mb-4">Menginisialisasi sesi WebRTC MeetHub dengan server <span class="font-mono text-teal-400 font-bold" x-text="domain"></span></p>
                <div class="flex items-center space-x-2 text-[11px] text-slate-400">
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Menyiapkan perangkat kamera & mikrofon...</span>
                </div>
            </div>

            <!-- Fallback Helper Pill (Shows if iframe blocked or takes long) -->
            <div x-show="showFallbackHelp && !videoConnected" x-cloak
                class="absolute bottom-5 left-1/2 -translate-x-1/2 z-20 max-w-md w-[92%] p-3.5 bg-slate-900/95 backdrop-blur-xl border border-teal-500/30 rounded-2xl shadow-2xl flex items-center justify-between space-x-3 text-xs">
                <div class="flex items-center space-x-2.5 overflow-hidden">
                    <div class="w-7 h-7 rounded-xl bg-teal-500/20 text-teal-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-slate-300 truncate">Video belum muncul? Browser mungkin memblokir iframe.</span>
                </div>
                <button @click="openExternalWindow()" class="px-3 py-1.5 bg-teal-600 hover:bg-teal-500 text-white rounded-xl font-bold flex-shrink-0 shadow transition">
                    Buka Pop-up
                </button>
            </div>
        </div>

        <!-- In-Meeting Sidepanel (Docked on Right) -->
        <aside x-show="sidepanelOpen" x-cloak
            class="w-full sm:w-80 md:w-96 h-full bg-[#0c121e]/95 backdrop-blur-2xl border-l border-white/10 flex flex-col z-20 shadow-2xl transition-all">
            <!-- Sidepanel Header & Segmented Tabs -->
            <div class="border-b border-white/10 p-3.5 bg-black/20">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-xs font-extrabold text-slate-200 uppercase tracking-wider flex items-center space-x-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-teal-400"></span>
                        <span>Ruang Kolaborasi</span>
                    </span>
                    <button @click="sidepanelOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg text-xs hover:bg-white/5 transition">
                        &times; Tutup
                    </button>
                </div>
                <div class="grid grid-cols-3 gap-1 bg-white/5 p-1 rounded-xl text-xs font-bold border border-white/5">
                    <button @click="sideTab = 'notes'"
                        :class="sideTab === 'notes' ? 'bg-teal-700 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                        class="py-1.5 rounded-lg transition text-center">
                        Notulensi
                    </button>
                    <button @click="sideTab = 'tasks'"
                        :class="sideTab === 'tasks' ? 'bg-teal-700 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                        class="py-1.5 rounded-lg transition text-center">
                        Tugas (<span x-text="tasks.length">0</span>)
                    </button>
                    <button @click="sideTab = 'files'"
                        :class="sideTab === 'files' ? 'bg-teal-700 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200'"
                        class="py-1.5 rounded-lg transition text-center">
                        Berkas (<span x-text="files.length">0</span>)
                    </button>
                </div>
            </div>

            <!-- Tab 1: Notes (Autosaved) -->
            <div x-show="sideTab === 'notes'" class="flex-1 flex flex-col p-4 overflow-hidden">
                <div class="flex justify-between items-center text-[11px] text-slate-400 mb-2">
                    <span class="font-medium">Notulensi Kolaboratif</span>
                    <span x-text="noteSaveStatus" class="font-mono text-emerald-400 font-semibold">Tersimpan</span>
                </div>
                <textarea x-model="notesContent" @input="debouncedSaveNotes()"
                    placeholder="Tuliskan poin-poin hasil rapat atau rangkuman pembahasan di sini..."
                    class="flex-1 w-full bg-[#070b13] border border-white/10 rounded-2xl p-3.5 text-xs text-slate-200 leading-relaxed resize-none focus:outline-none focus:border-teal-500 focus:ring-1 focus:ring-teal-500 font-sans"></textarea>
            </div>

            <!-- Tab 2: Action Items -->
            <div x-show="sideTab === 'tasks'" class="flex-1 flex flex-col p-4 overflow-y-auto space-y-3">
                <!-- Add Task Form -->
                <form @submit.prevent="addTask()" class="flex space-x-2">
                    <input type="text" x-model="newTaskTitle" placeholder="Tindak lanjut baru... (tekan Enter)" required
                        class="flex-1 bg-[#070b13] border border-white/10 rounded-xl px-3.5 py-2 text-xs text-slate-200 focus:outline-none focus:border-teal-500">
                    <button type="submit" class="px-3.5 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-xl text-xs font-bold shadow transition">
                        + Tambah
                    </button>
                </form>

                <!-- Tasks List -->
                <div class="space-y-2 text-xs pt-1">
                    <template x-for="task in tasks" :key="task.id">
                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/5 flex items-start space-x-2.5 hover:bg-white/10 transition">
                            <input type="checkbox" :checked="task.is_done" @change="toggleTask(task)"
                                class="mt-0.5 h-4 w-4 rounded bg-slate-900 border-white/20 text-teal-600 focus:ring-0 cursor-pointer">
                            <span :class="task.is_done ? 'line-through text-slate-500' : 'text-slate-200'" class="flex-1 leading-tight break-words font-medium" x-text="task.title"></span>
                            <button @click="deleteTask(task.id)" class="text-slate-500 hover:text-rose-400 p-0.5 text-xs font-bold">&times;</button>
                        </div>
                    </template>
                    <div x-show="tasks.length === 0" class="py-12 text-center text-slate-500 text-xs">
                        <span class="block mb-1 text-slate-400 font-semibold">Belum ada tugas</span>
                        <span>Tambahkan item checklist untuk tindak lanjut rapat.</span>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Shared Files -->
            <div x-show="sideTab === 'files'" class="flex-1 flex flex-col p-4 overflow-y-auto space-y-4">
                <!-- Upload Dropzone -->
                <div class="p-4 bg-[#070b13] border border-dashed border-white/15 rounded-2xl text-center hover:border-teal-500/60 transition group">
                    <label class="cursor-pointer block">
                        <input type="file" @change="uploadFile($event)" class="hidden" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.txt">
                        <div class="w-8 h-8 mx-auto rounded-xl bg-teal-500/10 text-teal-400 flex items-center justify-center mb-2 group-hover:scale-110 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </div>
                        <span class="text-xs font-bold text-teal-400 hover:text-teal-300 block">
                            Unggah Materi Rapat
                        </span>
                        <span class="text-[10px] text-slate-500 block mt-0.5">Maks. 25 MB (PDF, Dokumen, Gambar, ZIP)</span>
                    </label>
                    <div x-show="isUploading" class="text-xs text-amber-400 mt-2 font-mono animate-pulse">Sedang mengunggah...</div>
                </div>

                <!-- Files List -->
                <div class="space-y-2 text-xs">
                    <template x-for="file in files" :key="file.id">
                        <div class="p-3 bg-white/5 border border-white/5 rounded-2xl flex items-center justify-between hover:bg-white/10 transition">
                            <div class="overflow-hidden mr-2">
                                <span class="font-bold text-slate-200 block truncate" x-text="file.name"></span>
                                <span class="text-[10px] text-slate-500 font-mono" x-text="file.size"></span>
                            </div>
                            <div class="flex items-center space-x-1.5 flex-shrink-0">
                                <a :href="file.download_url" class="p-2 bg-white/10 hover:bg-white/20 rounded-xl text-slate-300 transition" title="Unduh">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                                <template x-if="file.can_delete">
                                    <button @click="deleteFile(file.id)" class="p-2 hover:bg-rose-900/40 text-slate-500 hover:text-rose-400 rounded-xl transition" title="Hapus">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                    <div x-show="files.length === 0" class="py-12 text-center text-slate-500 text-xs">
                        <span class="block text-slate-400 font-semibold mb-1">Belum ada materi</span>
                        <span>Unggah slide atau dokumen presentasi.</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Leave Meeting Modal Dialog -->
    <div x-show="showLeaveModal" x-cloak class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="showLeaveModal = false" class="bg-[#0f172a] border border-white/10 rounded-3xl p-6 max-w-sm w-full text-center shadow-2xl">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            </div>
            <h3 class="font-extrabold text-white text-base mb-1">Tinggalkan Ruang Rapat?</h3>
            <p class="text-xs text-slate-400 mb-6">Waktu kehadiran Anda akan dihitung dan disimpan otomatis.</p>
            <div class="flex space-x-2">
                <button @click="showLeaveModal = false" class="flex-1 py-2.5 bg-white/10 hover:bg-white/15 text-slate-300 rounded-xl text-xs font-bold transition">
                    Batal
                </button>
                <button @click="confirmLeave()" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/30 transition">
                    Ya, Keluar
                </button>
            </div>
        </div>
    </div>

    <!-- Jitsi Integration & Sidepanel Logic -->
    <script>
        const domain = @json($domain);
        const roomName = @json($jitsiRoomName);
        const roomCode = @json($meeting->room_code);
        const jwt = @json($jwt);
        const isPublicDomain = @json($isPublicDomain);
        const displayName = @json($displayName);
        const sessionToken = @json($sessionToken);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const startAudioMuted = @json((bool) $startWithAudioMuted);
        const startVideoMuted = @json((bool) $startWithVideoMuted);

        function meetingRoomApp() {
            return {
                sidepanelOpen: false,
                sideTab: 'notes',
                notesContent: '',
                noteSaveStatus: 'Tersimpan',
                saveTimeout: null,
                tasks: [],
                newTaskTitle: '',
                files: [],
                isUploading: false,
                isOffline: !navigator.onLine,
                codeCopied: false,
                showLeaveModal: false,
                isLoadingVideo: true,
                videoConnected: false,
                showFallbackHelp: false,
                api: null,

                init() {
                    window.addEventListener('offline', () => { this.isOffline = true; });
                    window.addEventListener('online', () => { this.isOffline = false; });

                    this.loadInitialData();
                    this.initJitsi();
                    this.startHeartbeat();
                },

                async loadInitialData() {
                    try {
                        const noteRes = await fetch(`/meetings/${roomCode}/notes`);
                        const noteData = await noteRes.json();
                        if (noteData.status === 'success') {
                            this.notesContent = noteData.content || '';
                            this.tasks = noteData.action_items || [];
                        }

                        const filesRes = await fetch(`/meetings/${roomCode}/files`);
                        const filesData = await filesRes.json();
                        if (filesData.status === 'success') {
                            this.files = filesData.files || [];
                        }
                    } catch (e) {
                        console.warn('Initial sidepanel load error:', e);
                    }
                },

                debouncedSaveNotes() {
                    this.noteSaveStatus = 'Menyimpan...';
                    clearTimeout(this.saveTimeout);
                    this.saveTimeout = setTimeout(async () => {
                        try {
                            const res = await fetch(`/meetings/${roomCode}/notes`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({ content: this.notesContent })
                            });
                            const data = await res.json();
                            if (data.status === 'success') {
                                this.noteSaveStatus = 'Tersimpan ' + data.saved_at;
                            }
                        } catch (e) {
                            this.noteSaveStatus = 'Gagal menyimpan';
                        }
                    }, 1000);
                },

                async addTask() {
                    if (!this.newTaskTitle.trim()) return;
                    try {
                        const res = await fetch(`/meetings/${roomCode}/action-items`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ title: this.newTaskTitle.trim() })
                        });
                        const data = await res.json();
                        if (data.status === 'success') {
                            this.tasks.unshift(data.item);
                            this.newTaskTitle = '';
                        }
                    } catch (e) {
                        alert('Gagal menambah tugas.');
                    }
                },

                async toggleTask(task) {
                    try {
                        const res = await fetch(`/action-items/${task.id}/toggle`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        const data = await res.json();
                        if (data.status === 'success') {
                            task.is_done = data.is_done;
                        }
                    } catch (e) {
                        task.is_done = !task.is_done;
                    }
                },

                async deleteTask(id) {
                    try {
                        const res = await fetch(`/action-items/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        const data = await res.json();
                        if (data.status === 'success') {
                            this.tasks = this.tasks.filter(t => t.id !== id);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                async uploadFile(event) {
                    const fileInput = event.target;
                    if (!fileInput.files.length) return;
                    const file = fileInput.files[0];
                    const formData = new FormData();
                    formData.append('file', file);

                    this.isUploading = true;
                    try {
                        const res = await fetch(`/meetings/${roomCode}/files`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken },
                            body: formData
                        });
                        const data = await res.json();
                        if (data.status === 'success') {
                            this.files.unshift(data.file);
                            fileInput.value = '';
                        } else {
                            alert(data.message || 'Gagal mengunggah berkas.');
                        }
                    } catch (e) {
                        alert('Gagal mengunggah berkas.');
                    } finally {
                        this.isUploading = false;
                    }
                },

                async deleteFile(id) {
                    if (!confirm('Hapus berkas ini?')) return;
                    try {
                        const res = await fetch(`/files/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });
                        const data = await res.json();
                        if (data.status === 'success') {
                            this.files = this.files.filter(f => f.id !== id);
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                async initJitsi() {
                    const container = document.getElementById('jitsi-container');
                    if (!container) return;

                    // Tunggu modul External API siap (maksimal 6 detik jika koneksi lambat)
                    let attempts = 0;
                    while (typeof JitsiMeetExternalAPI === 'undefined' && attempts < 30) {
                        await new Promise(r => setTimeout(r, 200));
                        attempts++;
                    }

                    if (typeof JitsiMeetExternalAPI === 'undefined') {
                        container.innerHTML = `
                            <div class="h-full flex flex-col items-center justify-center p-6 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-rose-500/15 text-rose-400 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <h3 class="text-sm font-bold text-white mb-1">Gagal Memuat Modul Video</h3>
                                <p class="text-xs max-w-sm mb-4 text-slate-400">Tidak dapat memuat modul WebRTC dari server ${domain}. Pastikan koneksi internet aktif dan tidak diblokir adblocker.</p>
                                <button onclick="window.location.reload()" class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-xl text-xs transition">
                                    Muat Ulang Halaman
                                </button>
                            </div>
                        `;
                        return;
                    }

                    const options = {
                        roomName: roomName,
                        parentNode: container,
                        width: '100%',
                        height: '100%',
                        configOverwrite: {
                            startWithAudioMuted: startAudioMuted,
                            startWithVideoMuted: startVideoMuted,
                            enableWelcomePage: false,
                            prejoinPageEnabled: false,
                            prejoinConfig: { enabled: false },
                            disableDeepLinking: true,
                            toolbarButtons: [
                                'camera',
                                'chat',
                                'closedcaptions',
                                'desktop',
                                'filmstrip',
                                'fullscreen',
                                'hangup',
                                'microphone',
                                'participants-pane',
                                'profile',
                                'raisehand',
                                'recording',
                                'security',
                                'select-background',
                                'settings',
                                'shareaudio',
                                'sharedvideo',
                                'tileview',
                                'toggle-camera',
                                'videoquality',
                                'whiteboard'
                            ]
                        },
                        interfaceConfigOverwrite: {
                            SHOW_JITSI_WATERMARK: false,
                            SHOW_WATERMARK_FOR_GUESTS: false,
                            DEFAULT_BACKGROUND: '#050811',
                            APP_NAME: 'MeetHub'
                        },
                        userInfo: {
                            displayName: displayName
                        }
                    };

                    // meet.jit.si adalah public server yang menolak custom HS256 JWT.
                    // JWT hanya dilampirkan jika menggunakan Jitsi self-hosted / private server.
                    if (!isPublicDomain && jwt) {
                        options.jwt = jwt;
                    }

                    try {
                        this.api = new JitsiMeetExternalAPI(domain, options);
                        this.api.addEventListener('videoConferenceJoined', () => {
                            this.isLoadingVideo = false;
                            this.videoConnected = true;
                        });
                        this.api.addEventListener('participantJoined', () => {
                            this.isLoadingVideo = false;
                            this.videoConnected = true;
                        });
                        this.api.addEventListener('readyToClose', () => this.leaveRoom());

                        // Beri waktu 2 detik untuk rendering frame lalu hilangkan tirai loading
                        setTimeout(() => {
                            this.isLoadingVideo = false;
                        }, 2000);

                        // Tampilkan tombol bantuan jika dalam 6 detik belum terdeteksi terhubung
                        setTimeout(() => {
                            if (!this.videoConnected) {
                                this.showFallbackHelp = true;
                            }
                        }, 6000);
                    } catch (err) {
                        console.error('Inisialisasi Jitsi Meet error:', err);
                        this.isLoadingVideo = false;
                        this.showFallbackHelp = true;
                    }

                    window.addEventListener('beforeunload', () => {
                        navigator.sendBeacon('/attendance/leave', new Blob([JSON.stringify({
                            session_token: sessionToken,
                            _token: csrfToken
                        })], { type: 'application/json' }));
                    });
                },

                openExternalWindow() {
                    const url = `https://${domain}/${roomName}`;
                    window.open(url, 'MeetHubVideo_' + roomCode, 'width=1150,height=750,menubar=no,toolbar=no,location=no,status=no,resizable=yes');
                },

                confirmLeave() {
                    this.showLeaveModal = false;
                    if (this.api) {
                        this.api.dispose();
                    }
                    this.leaveRoom();
                },

                startHeartbeat() {
                    setInterval(() => {
                        fetch('/attendance/heartbeat', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            body: JSON.stringify({ session_token: sessionToken })
                        }).catch(e => console.warn('Heartbeat error:', e));
                    }, 20000);
                },

                leaveRoom() {
                    navigator.sendBeacon('/attendance/leave', new Blob([JSON.stringify({
                        session_token: sessionToken,
                        _token: csrfToken
                    })], { type: 'application/json' }));

                    window.location.href = @json(route('meetings.show', $meeting->room_code));
                }
            };
        }
    </script>
</body>
</html>
