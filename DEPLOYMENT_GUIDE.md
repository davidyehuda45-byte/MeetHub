# Panduan Deployment Produksi MeetHub (Web & Mobile)

Panduan lengkap untuk men-deploy backend Laravel MeetHub, server Jitsi Meet mandiri (self-hosted dengan JWT & Coturn), serta membangun aplikasi mobile Flutter.

---

## 1. Arsitektur Infrastruktur Target

```text
               [Peserta Web / Browser]        [Aplikasi Mobile Android/iOS]
                          |                                 |
              HTTPS 443   |                     HTTPS 443   |
                          v                                 v
                 [Nginx Reverse Proxy / Let's Encrypt SSL]
                 |                                      |
         (meet.domain.com)                      (app.domain.com)
                 |                                      |
                 v                                      v
       [Docker: Jitsi Meet Stack]               [Laravel 12 Backend]
       ├── Web (Nginx)                          ├── PHP 8.2+ (PHP-FPM)
       ├── Prosody (mod_auth_token JWT)         ├── MySQL 8.0
       ├── Jicofo (Focus)                       ├── Redis (Cache & Queue)
       └── JVB (Videobridge: UDP 10000)         └── Storage File (Materi)
                 |
                 v
       [Coturn TURN Server (UDP/TCP 3478, TLS 5349)]
```

---

## 2. Persiapan Server & Spesifikasi

- **OS**: Ubuntu 22.04 LTS / 24.04 LTS
- **Spesifikasi Minimal**: 4 vCPU, 8 GB RAM, 50 GB SSD (untuk ~50 peserta aktif)
- **Domain**:
  - `app.domainanda.com` (Laravel Web & REST API)
  - `meet.domainanda.com` (Jitsi Meet Server)
  - `turn.domainanda.com` (Coturn Server)

### Buka Port Firewall (UFW)
```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 10000/udp   # Jitsi Videobridge Media
sudo ufw allow 4443/tcp    # JVB TCP Fallback
sudo ufw allow 3478/udp    # STUN/TURN
sudo ufw allow 5349/tcp    # TURNS (TLS)
sudo ufw allow 49152:65535/udp # Media Relay Coturn
sudo ufw enable
```

---

## 3. Langkah 1: Deploy Server Video Jitsi (Docker)

1. Masuk ke direktori `docker/jitsi`:
   ```bash
   cd /var/www/meethub/docker/jitsi
   cp .env.example .env
   ```

2. Konfigurasi `.env`:
   - `PUBLIC_URL=https://meet.domainanda.com`
   - `DOCKER_HOST_ADDRESS=<IP_PUBLIK_SERVER>`
   - `JWT_APP_ID=meethub`
   - `JWT_APP_SECRET=<32_KARAKTER_SECRET_SAMA_DENGAN_LARAVEL>`

3. Jalankan container:
   ```bash
   docker compose up -d
   ```

4. Verifikasi container berjalan:
   ```bash
   docker compose ps
   ```

---

## 4. Langkah 2: Deploy Backend Laravel & Web

1. Salin source code ke server:
   ```bash
   cd /var/www/meethub
   composer install --no-dev --optimize-autoloader
   npm install && npm run build
   ```

2. Atur file `.env` Laravel:
   ```env
   APP_NAME=MeetHub
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://app.domainanda.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=meethub
   DB_USERNAME=meethub_user
   DB_PASSWORD=PasswordKuatDb123!

   # Integrasi Jitsi Meet
   JITSI_DOMAIN=meet.domainanda.com
   JITSI_APP_ID=meethub
   JITSI_APP_SECRET=<SECRET_YANG_SAMA_DENGAN_PROSODY>
   JITSI_JWT_ALGO=HS256
   ```

3. Jalankan migrasi dan optimasi:
   ```bash
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. Atur Cron Pengingat Rapat (`crontab -e`):
   ```bash
   * * * * * cd /var/www/meethub && php artisan schedule:run >> /dev/null 2>&1
   ```

---

## 5. Langkah 3: Konfigurasi Coturn (TURN Server)

1. Pasang coturn:
   ```bash
   sudo apt install coturn -y
   ```

2. Salin konfigurasi:
   ```bash
   sudo cp docker/jitsi/coturn/turnserver.conf /etc/turnserver.conf
   ```

3. Aktifkan service:
   ```bash
   sudo systemctl restart coturn
   sudo systemctl enable coturn
   ```

---

## 6. Langkah 4: Build Aplikasi Mobile (Flutter)

1. Masuk ke direktori `mobile`:
   ```bash
   cd mobile
   flutter pub get
   ```

2. Konfigurasi alamat server produksi di `lib/config/constants.dart`:
   ```dart
   static const String defaultBaseUrl = 'https://app.domainanda.com/api/v1';
   ```

3. Build APK Android:
   ```bash
   flutter build apk --release
   ```
   File hasil build berada di:
   `mobile/build/app/outputs/flutter-apk/app-release.apk`

4. Build iOS:
   ```bash
   flutter build ipa --release
   ```

