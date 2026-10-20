# MeetHub Project State

## Ringkasan Proyek
MeetHub adalah platform rapat online (video conference) full-stack mandiri yang memadukan **Laravel 12 (Web & REST API)**, **Jitsi Meet (Self-Hosted WebRTC SFU dengan autentikasi JWT)**, dan aplikasi mobile **Flutter (Android/iOS)**.

## Status: 100% Selesai & Terverifikasi (Semua FR-01 s.d. FR-15 PRD + Full Modern UI Overhaul)

### 1. Desain Sistem & Antarmuka Modern (Executive Nordic Teal & Studio Jade/Pine Overhaul)
- **Palet Warna Bespoke (Anti-AI-Slop)**:
  - Mengganti total warna generik AI (Indigo/Violet/Purple) menjadi **Nordic Teal & Studio Jade/Pine** (`teal-600/700/800`, `emerald-500/600`, netral `slate-950/zinc`).
- **Tipografi & Desain Token (`app.css`)**:
  - Webfont Google Fonts **Plus Jakarta Sans** (teks UI/headings) + **JetBrains Mono** (kode ruangan, waktu, metrik).
  - Ambient radial gradients (`bg-ambient-pattern`), micro-glow borders, dan custom thin scrollbar teal.
- **Global Shell & Header (`layouts/app.blade.php`)**:
  - Header kaca tembus pandang (`backdrop-blur-md bg-white/85`), icon mark prism kamera teal-emerald multi-layer, active navigation pills teal, floating toast notifications, dan profil dropdown menu.
- **Dashboard Command Center (`meetings/index.blade.php`)**:
  - Hero Command Grid: Kartu Rapat Instan (deep pine / teal gelap, live pulse emerald, tombol aksen putih), Kartu Jadwalkan Rapat (clean slate), dan Kartu Gabung Ruangan (input terpadu uppercase).
  - Live Radar Banner: Menampilkan rapat yang sedang aktif secara realtime.
  - Tabel Jadwal Rapat: Ribbon kalender (tanggal, bulan, jam WIB), host avatar teal, chip durasi, dan menu aksi rapi.
- **Studio Lobi Pra-Rapat (`meetings/lobby.blade.php`)**:
  - Viewfinder kamera WebRTC 16:9 berbingkai rounded-3xl (`ring-1 ring-white/10 shadow-2xl bg-slate-950`).
  - Audio visualizer soundwave dinamis berdenyut mengikuti desibel suara mikrofon.
  - Floating device toggle pills, banner offline beranimasi, dan panduan in-app browser.
- **Ruang Konferensi Video (`meetings/room.blade.php`)**:
  - Pro-grade dark workspace (`#090d16`) dengan top bar 48px minimalis, room code copy pill teal, dan live elapsed timer.
  - Dockable glass sidebar (`backdrop-blur-2xl bg-[#0c121e]/95 border-l border-white/10`) dengan segmented control pills teal untuk Notulensi (autosave realtime), Action Items (checklist coret), dan Berkas Materi (drag-and-drop zone).
  - Modal dialog konfirmasi keluar rapat elegan (menggantikan browser confirm bawaan).
- **Detail Rapat & Hub Kolaborasi (`meetings/show.blade.php`)**:
  - Header ringkasan dengan status badges dan tombol aksi cepat.
  - Quick share dock teal dengan tombol Salin Tautan, Format WA 1-klik, dan modal QR Code SVG.
  - Segmented control navigation tabs untuk informasi rapat, tabel presensi, notulensi & tugas, dan materi.
- **Formulir & Autentikasi (`meetings/create.blade.php`, `edit.blade.php`, `auth/login.blade.php`, `register.blade.php`, `profile.blade.php`, `forgot-password.blade.php`, `reset-password.blade.php`)**:
  - Form modern dengan switch toggle bergaya iOS (`peer-checked:bg-teal-600`), input groups, dan chip login demo 1-klik (Admin, Host, Peserta).
- **Panel Administrasi (`admin/users.blade.php`, `meetings.blade.php`, `settings.blade.php`, `audit_logs.blade.php`)**:
  - Tab navigasi admin, stat cards terjadwal teal, filter, dan tombol simpan perubahan.

### 2. Backend & REST API (Laravel 12 + MySQL 8)
- **Autentikasi & Akun**: Multi-role RBAC (`admin`, `host`, `user`), brute-force rate limiter 5 percobaan/menit, alur reset password, dan profil timezone.
- **Layanan Jitsi JWT & WebRTC Dinamis (`JitsiJwtService`)**: Default ke server WebRTC terbuka bebas login (`fairmeeting.net`) tanpa blokir iframe (`X-Frame-Options`), dengan dukungan token HS256 dinamis untuk deployment Jitsi self-hosted dari database `settings`.
- **Presensi Otomatis**: Heartbeat 20 detik, auto-leave beacon, ekspor CSV UTF-8 BOM, dan summary report PDF-ready.
- **Cron Scheduler**: `meethub:send-reminders` (pengingat 15 menit) & `meethub:close-abandoned-sessions` (clean up timeout > 2 menit).
- **Server Health Check**: `GET /api/v1/health` (PDO database check, storage check, domain Jitsi, status 200).
- **Integrasi Kalender Standar (RFC 5545 iCalendar)**: `CalendarService` untuk payload `.ics` (VEVENT, alarm pengingat 15 menit, UID terisolasi) dan generator Google Calendar 1-klik via endpoint `GET /meetings/{code}/calendar.ics`.
- **Generator Dokumen PDF Presensi Resmi**: Menggunakan `barryvdh/laravel-dompdf` pada `MeetingAttendanceController::downloadPdf` dengan format kop surat resmi, rekap durasi akumulatif, tabel kehadiran dengan status, notulensi, dan kolom tanda tangan via endpoint `GET /meetings/{code}/attendance/pdf`.
- **Email Undangan Rapat Otomatis (Queue & Mailable)**: `MeetingInvitationMail` dengan antrean background (`ShouldQueue`), template email HTML responsif Nordic Teal, tombol akses langsung, dan lampiran kalender `.ics` otomatis.
- **Webhook Receiver Event Jitsi / Prosody**: `JitsiWebhookController` pada endpoint `POST /api/v1/webhooks/jitsi` dengan verifikasi dual-signature (HMAC-SHA256 & Bearer Token) untuk otomatisasi mutasi status rapat (`room_created`, `room_destroyed`) serta pelacakan presensi SFU server-side (`participant_joined`, `participant_left`).
- **Manajemen Rekaman Rapat (Video Streaming & Storage Service)**: Tabel `meeting_recordings`, model `MeetingRecording`, dan `MeetingRecordingController` untuk penyajian video terproteksi dengan header HTTP `Range` (seekable byte streaming), download terverifikasi, sinkronisasi webhook otomatis dari perekam Jibri, dan hak akses eksklusif peserta.

### 3. Aplikasi Mobile (Flutter)
Terletak pada direktori `mobile/`:
- Dikonfigurasi dengan `jitsi_meet_flutter_sdk` dan Laravel Sanctum API.
- Layar: Splash, Login (demo quick login), Home (Jadwal, Riwayat, Profil), Join With Code, dan Buat Rapat.

### 4. Server Video Jitsi & Coturn (Self-Hosted)
Terletak pada direktori `docker/jitsi/`:
- `docker-compose.yml`, `coturn/turnserver.conf`, `nginx/meethub-jitsi.conf`, dan `DEPLOYMENT_GUIDE.md`.

---

## Verifikasi & Kualitas Kode
- **PHPUnit / Pest Test Suite**: 35 pengujian unit & fitur (155 assertions) — **100% PASS**.
- **Frontend Assets**: Vite v7 + Tailwind CSS v4 compiled successfully (`public/build`).
- **Database**: 12 tabel MySQL dengan index, relasi foreign keys, dan tabel pengaturan sistem.
