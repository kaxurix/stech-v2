# Alur Deploy ke stech.ft.unsoed.ac.id

Panduan upload aplikasi dari laptop ke server kampus.
Alur di bawah **sudah diuji dan berhasil** pada 7 September 2026.

---

## Kondisi server (terverifikasi)

| | |
|---|---|
| Path aplikasi | `/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id` |
| SSH | `unsoed-stech-ft@stech.ft.unsoed.ac.id` (port 22) |
| Web server | nginx (native) |
| **Docker** | **TIDAK dipakai.** `docker-compose.yml` & `Dockerfile` di repo hanya sisa percobaan lama — abaikan saja |
| PHP | 8.4.22 |
| Composer | 2.9.3 |
| Node | 18.19.1 — **terlalu tua untuk Vite 8**, jadi build wajib dari laptop |
| Git | repo aktif, remote `github.com/kaxurix/stech-v2`, branch `main` |
| Database | `stech-db`, user `stech`, host `stech.ft.unsoed.ac.id:3306` |
| `.env` server | sudah benar — **jangan ditimpa** |

> **Hanya bisa dari jaringan kampus.** Di dalam kampus, domain resolve ke IP
> internal `172.25.0.65` dan port 22 terbuka. Dari luar (wifi rumah), resolve ke
> IP publik `103.9.22.82` yang port SSH-nya ditutup — SSH akan gagal.

> `.env` di server sudah dikonfigurasi benar, jadi [`.env.production`](.env.production)
> di repo hanya acuan/cadangan. Tidak perlu diupload.

---

## Alur deploy

### 1. Di laptop — build & push

```bash
npm run build
git add -A
git commit -m "pesan perubahan"
git push origin main
```

### 2. Backup di server (jangan dilewat)

Server berisi **data pendaftar sungguhan**. Selalu backup sebelum menyentuh apa pun:

```bash
ssh unsoed-stech-ft@stech.ft.unsoed.ac.id
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
BK=~/backups/predeploy-$(date +%Y%m%d-%H%M%S); mkdir -p $BK
cp .env $BK/.env.backup
mysqldump -h stech.ft.unsoed.ac.id -u stech -p'PASSWORD_DB' 'stech-db' > $BK/db.sql
tar czf $BK/storage-app.tgz storage/app
```

`storage/app` berisi bukti pembayaran peserta — ikut dibackup.

### 3. Ambil kode baru

```bash
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
git checkout -- bootstrap/cache/.gitignore package-lock.json   # buang perubahan sepele
git pull origin main
composer install --no-dev --optimize-autoloader
```

`composer install` otomatis mem-publish asset Filament, jadi tidak perlu
`php artisan filament:assets` terpisah.

### 4. Kirim asset frontend (WAJIB — tidak ikut git)

`/public/build` ada di `.gitignore`, jadi **tidak ikut `git pull`**. Kalau
langkah ini dilewat, situs tampil polos tanpa CSS sama sekali.

```bash
# di laptop
tar -czf build.tgz -C public build
scp build.tgz unsoed-stech-ft@stech.ft.unsoed.ac.id:~/tmp/

# di server
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
rm -rf public/build && tar -xzf ~/tmp/build.tgz -C public
rm -f public/hot     # kalau file ini ada, situs memaksa ambil asset dari localhost
```

### 5. Migrasi, permission, cache

```bash
php artisan migrate --force
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`--force` wajib karena `APP_ENV=production` meminta konfirmasi interaktif.
**Setiap kali `.env` diubah, `config:cache` harus diulang** — kalau tidak,
perubahan `.env` tidak terbaca.

### 6. Verifikasi

```bash
curl -s -o /dev/null -w "%{http_code}\n" https://stech.ft.unsoed.ac.id
curl -s -o /dev/null -w "%{http_code}\n" https://stech.ft.unsoed.ac.id/admin/login
```

Keduanya harus `200`. Lalu buka di browser: halaman depan harus tampil lengkap
dengan CSS, dan `/admin/login` menampilkan form login Filament.

---

## Troubleshooting

| Gejala | Penyebab & solusi |
|---|---|
| `Class "Filament\..." not found` | `composer install` belum dijalankan (langkah 3) |
| Halaman polos tanpa CSS/JS | `public/build` belum diupload (langkah 4), atau file `public/hot` tertinggal → hapus |
| Perubahan `.env` tidak berpengaruh | `php artisan config:cache` belum diulang |
| Error 500 tanpa keterangan | Normal — `APP_DEBUG=false`. Lihat detail di `storage/logs/laravel.log` |
| `Permission denied` saat upload bukti | `chmod -R 775 storage bootstrap/cache` |
| Panel admin 404 | `php artisan route:cache` perlu diulang |
| SSH `Connection refused` | Kamu tidak sedang di jaringan kampus |
| Login admin gagal padahal password benar | Situs diakses lewat `http://`, bukan `https://` (`SESSION_SECURE_COOKIE=true`) |

---

## Deploy berikutnya (ringkas)

```bash
# laptop
npm run build && git add -A && git commit -m "update" && git push origin main
tar -czf build.tgz -C public build
scp build.tgz unsoed-stech-ft@stech.ft.unsoed.ac.id:~/tmp/

# server
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id && \
git pull origin main && \
composer install --no-dev --optimize-autoloader && \
rm -rf public/build && tar -xzf ~/tmp/build.tgz -C public && \
php artisan migrate --force && \
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## Keamanan — WAJIB dibaca

**Password admin masih `admin123`** (default dari `AdminSeeder`), sedangkan
panel admin kini aktif di `/admin/login` dan repo GitHub bersifat publik.
Artinya siapa pun yang membaca `database/seeders/AdminSeeder.php` bisa masuk
sebagai admin dan melihat data pendaftar beserta bukti pembayaran mereka.

Ganti segera:

```bash
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
php artisan tinker --execute="\$u=App\Models\User::where('email','admin@stech.id')->first(); \$u->password=Illuminate\Support\Facades\Hash::make('PASSWORD_BARU_YANG_KUAT'); \$u->save(); echo 'diganti';"
```

**Jangan jalankan `DemoParticipantsSeeder` di server** — seeder itu menghapus
seluruh akun peserta lalu menggantinya dengan data dummy. Itu hanya untuk lokal.
