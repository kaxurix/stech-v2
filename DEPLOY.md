# Alur Deploy ke stech.ft.unsoed.ac.id

Panduan upload aplikasi dari laptop lokal ke server kampus.

- **Server**: `stech.ft.unsoed.ac.id` (nginx)
- **SSH**: `unsoed-stech-ft@stech.ft.unsoed.ac.id`
- **Database**: `stech-db` / user `stech` (MySQL di server)

> **Catatan jaringan**: SSH hanya bisa diakses dari **jaringan kampus Unsoed**.
> Dari luar (mis. wifi rumah), domain resolve ke IP publik `103.9.22.82` yang
> port SSH-nya tertutup. Di dalam kampus, resolve ke IP internal `172.25.0.65`
> dan port 22 terbuka. Jadi deploy hanya bisa dilakukan saat terhubung wifi Unsoed.

---

## Kondisi saat ini (per 7 Sep 2026)

Versi yang jalan di server **tertinggal jauh** dari lokal:

| | Server (live) | Lokal (siap deploy) |
|---|---|---|
| Panel admin | Inertia/Vue lama | **Filament** (`/admin/login` di server masih 404) |
| Asset | `app-Dtq65eGY.js` | `app-Cv6xXiK4.js` |
| Timeline | Jun–Jul 2026 | 11 Sep – 31 Okt 2026 |
| Guidebook | placeholder | link Google Docs asli |

Konsekuensinya: deploy ini **wajib menjalankan `composer install`** di server,
karena Filament dan Livewire adalah dependency baru yang belum ada di sana.
Kalau hanya upload file tanpa `composer install`, aplikasi akan error total
(class not found).

---

## Langkah 0 — Cek kondisi server (WAJIB, sekali saja)

Metode deploy berbeda tergantung server pakai Docker atau native. Jalankan ini
dulu dan catat hasilnya:

```bash
ssh unsoed-stech-ft@stech.ft.unsoed.ac.id "pwd; ls -la ~; for c in php composer node npm git docker; do printf '%s: ' \$c; command -v \$c || echo NO; done; php -v | head -1; docker ps 2>/dev/null || echo 'no docker'"
```

Yang perlu dipastikan:

1. **Di mana folder aplikasinya?** (cari folder yang berisi `artisan`)
2. **Ada `composer`?** Kalau tidak ada, lihat bagian Troubleshooting.
3. **Ada `docker`?** Kalau ada dan `docker ps` menampilkan container `stech-*`,
   pakai **Jalur B**. Kalau tidak, pakai **Jalur A**.
4. **Versi PHP** minimal 8.3 (composer.json mensyaratkan `^8.3`).

---

## Jalur A — Deploy native (nginx + PHP-FPM)

Ini jalur yang paling mungkin untuk hosting kampus.

### A1. Build asset di lokal

Asset **di-build di laptop**, bukan di server — server kampus biasanya tidak
punya Node.js, dan build butuh RAM besar.

```bash
npm run build
```

Hasilnya masuk ke `public/build/`. (Sudah saya jalankan, hasilnya terbaru.)

### A2. Upload file ke server

Ganti `<PATH_APP>` dengan folder aplikasi hasil Langkah 0.

**Opsi 1 — `git pull` di server (paling rapi, kalau server sudah clone repo):**

```bash
# di laptop: commit & push dulu
git add -A
git commit -m "feat: panel admin Filament, timeline baru, guidebook"
git push origin main

# lalu di server
ssh unsoed-stech-ft@stech.ft.unsoed.ac.id
cd <PATH_APP>
git pull origin main
```

> **JEBAKAN — baca ini.** `/public/build` ada di `.gitignore` (baris 17), jadi
> asset hasil build **TIDAK ikut** `git push`. Kalau di server hanya `git pull`,
> halaman akan tampil polos tanpa CSS/JS sama sekali. Asset wajib diupload
> terpisah:
>
> ```bash
> scp -r public/build unsoed-stech-ft@stech.ft.unsoed.ac.id:<PATH_APP>/public/
> ```
>
> Alternatifnya, hapus baris `/public/build` dari `.gitignore` supaya asset ikut
> ter-commit — repo jadi sedikit lebih besar, tapi deploy cukup `git pull` saja
> dan tidak ada risiko lupa upload asset.

**Opsi 2 — `scp` (upload langsung tanpa git):**

```bash
# dari folder project di laptop
scp -r public/build unsoed-stech-ft@stech.ft.unsoed.ac.id:<PATH_APP>/public/
scp -r app resources routes database config unsoed-stech-ft@stech.ft.unsoed.ac.id:<PATH_APP>/
scp composer.json composer.lock unsoed-stech-ft@stech.ft.unsoed.ac.id:<PATH_APP>/
```

> Jangan upload `vendor/` dan `node_modules/` — besar dan lambat. `vendor/`
> dibuat ulang oleh `composer install` di server (langkah A4).
>
> Jangan upload `.env` lokal — lihat langkah A3.

### A3. Samakan `.env` di server

Saya sudah siapkan [`.env.production`](.env.production) di lokal sebagai acuan
(file ini di-gitignore, jadi tidak ikut ter-commit).

**Jangan langsung menimpa `.env` server.** Bandingkan dulu:

```bash
ssh unsoed-stech-ft@stech.ft.unsoed.ac.id
cd <PATH_APP>
cp .env .env.backup-$(date +%F)   # backup dulu
nano .env
```

Pastikan nilainya:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://stech.ft.unsoed.ac.id
DB_DATABASE=stech-db
DB_USERNAME=stech
DB_PASSWORD=<password dari panitia>
SESSION_SECURE_COOKIE=true
```

> **`APP_KEY` jangan diubah** kalau server sudah punya. Mengganti APP_KEY
> membuat semua sesi login lama tidak terbaca (semua user ter-logout).

### A4. Install dependency & migrasi

```bash
cd <PATH_APP>

# Install package PHP versi produksi (tanpa dev tools, lebih cepat & aman)
composer install --no-dev --optimize-autoloader

# Publish asset Filament (WAJIB — Filament baru pertama kali dipasang di server)
php artisan filament:assets

# Jalankan migrasi
php artisan migrate --force
```

`--force` diperlukan karena di `APP_ENV=production` Laravel meminta konfirmasi
interaktif sebelum migrasi.

### A5. Cache untuk produksi

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ini mempercepat aplikasi drastis. **Ingat**: setiap kali `.env` diubah,
`config:cache` harus dijalankan ulang, kalau tidak perubahan `.env` tidak terbaca.

### A6. Permission folder

```bash
chmod -R 775 storage bootstrap/cache
```

Kalau muncul error "Permission denied" saat upload bukti bayar, jalankan ini lagi.

### A7. Verifikasi

```bash
curl -I https://stech.ft.unsoed.ac.id
curl -I https://stech.ft.unsoed.ac.id/admin/login   # harus 200, bukan 404
```

Lalu buka di browser:

- Halaman depan → timeline harus menampilkan 6 tahap (11 Sep – 31 Okt)
- `/admin/login` → halaman login Filament
- Login admin → dashboard dengan statistik & tabel peserta

---

## Jalur B — Deploy Docker

Kalau Langkah 0 menunjukkan Docker aktif dengan container `stech-app` /
`stech-web` / `stech-db`:

```bash
ssh unsoed-stech-ft@stech.ft.unsoed.ac.id
cd <PATH_APP>
git pull origin main
docker compose build app
docker compose up -d
docker compose exec app php artisan migrate --force
docker compose exec app php artisan filament:assets
docker compose exec app php artisan config:cache && \
docker compose exec app php artisan route:cache && \
docker compose exec app php artisan view:cache
```

Catatan khusus Docker di project ini: nginx dan app berbagi `public/build` +
`vendor` lewat named volume `stech-assets`. Setelah build ulang, volume itu
perlu di-refresh supaya nginx menyajikan asset baru — kalau halaman tampil
tanpa CSS setelah deploy, itu penyebabnya:

```bash
docker compose down
docker volume rm stech_stech-assets   # nama bisa berbeda, cek: docker volume ls
docker compose up -d
```

Kalau pakai Docker, `DB_HOST` di `.env` **bukan** `127.0.0.1` melainkan nama
service database (`stech-db`).

---

## Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| `Class "Filament\..." not found` | `composer install` belum dijalankan di server (langkah A4) |
| Halaman tampil tanpa CSS/JS | `public/build/` belum terupload, atau `public/hot` tertinggal di server → hapus file `public/hot` |
| Perubahan `.env` tidak berpengaruh | `php artisan config:cache` belum dijalankan ulang |
| Error 500 tanpa keterangan | `APP_DEBUG=false` (memang disengaja). Lihat detailnya di `storage/logs/laravel.log` |
| `Permission denied` saat upload bukti | `chmod -R 775 storage bootstrap/cache` |
| Panel admin 404 | `php artisan route:cache` perlu dijalankan ulang setelah deploy |
| `composer` tidak ada di server | Pakai composer.phar: `curl -sS https://getcomposer.org/installer \| php` lalu `php composer.phar install --no-dev --optimize-autoloader` |
| Login admin gagal padahal password benar | `SESSION_SECURE_COOKIE=true` tapi diakses lewat `http://` (bukan https) |

---

## Perintah ringkas (setelah setup pertama beres)

Untuk deploy berikutnya:

```bash
# di laptop
npm run build && git add -A && git commit -m "update" && git push

# di server
ssh unsoed-stech-ft@stech.ft.unsoed.ac.id
cd <PATH_APP> && git pull origin main && \
composer install --no-dev --optimize-autoloader && \
php artisan migrate --force && \
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## Akun admin

Setelah deploy, akun admin dibuat dengan:

```bash
php artisan db:seed --class=AdminSeeder
```

Kredensial default: `admin@stech.id` / `admin123`.

> **Ganti password ini sebelum situs dipakai pendaftaran sungguhan.** Password
> default sudah tertulis di repositori, jadi siapa pun yang melihat kode bisa
> masuk sebagai admin.

> **Jangan jalankan `DemoParticipantsSeeder` di server** — seeder itu menghapus
> seluruh akun peserta lalu mengisi data dummy. Itu hanya untuk lokal.
