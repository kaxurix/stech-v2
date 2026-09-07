# Tutorial Deploy ke stech.ft.unsoed.ac.id

Panduan langkah demi langkah untuk mengunggah aplikasi dari laptop ke server
kampus. Alur ini **sudah pernah dijalankan dan berhasil** (7 September 2026).

Perkiraan waktu: 10–15 menit.

---

## SEBELUM MULAI — 3 syarat wajib

**1. Harus terhubung wifi kampus Unsoed.**
SSH hanya bisa dari jaringan kampus. Dari wifi rumah, koneksi akan ditolak
(`Connection refused`) karena domain resolve ke IP publik yang portnya ditutup.

Cek cepat dari terminal:

```bash
ssh unsoed-stech-ft@stech.ft.unsoed.ac.id "echo BERHASIL"
```

Kalau muncul `BERHASIL`, kamu siap. Kalau `Connection refused`, kamu belum di
jaringan kampus.

**2. Siapkan kredensial**

| Keperluan | Nilai |
|---|---|
| SSH user | `unsoed-stech-ft` |
| SSH host | `stech.ft.unsoed.ac.id` |
| Password SSH | (lihat catatan WhatsApp) |
| Folder aplikasi | `/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id` |

**3. Pastikan semua perubahan sudah di-push ke GitHub.**
Deploy mengambil kode dari GitHub, bukan dari laptop.

---

## LANGKAH 1 — Build di laptop

Aset **wajib dibuild di laptop**, tidak bisa di server (Node di server versi
18, sedangkan Vite butuh Node 20+).

```bash
cd C:\Users\MSI\Documents\project\2026\stech
npm run build
```

Tunggu sampai muncul `✓ built in ...`.

Lalu push kode:

```bash
git add -A
git commit -m "deskripsi perubahan"
git push origin main
```

Kemas asetnya jadi satu file:

```bash
tar -czf build.tgz -C public build
```

---

## LANGKAH 2 — Kirim aset ke server

```bash
scp build.tgz unsoed-stech-ft@stech.ft.unsoed.ac.id:~/tmp/
```

Masukkan password saat diminta.

> **Kenapa aset dikirim terpisah?** Folder `public/build` sengaja tidak ikut
> git (ada di `.gitignore`). Kalau langkah ini dilewat, situs akan tampil
> **polos tanpa CSS sama sekali**. Ini kesalahan paling sering terjadi.

---

## LANGKAH 3 — Masuk ke server & backup

```bash
ssh unsoed-stech-ft@stech.ft.unsoed.ac.id
```

Setelah masuk, backup dulu — **jangan dilewat**, server berisi data pendaftar:

```bash
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
BK=~/backups/predeploy-$(date +%Y%m%d-%H%M%S); mkdir -p $BK
cp .env $BK/.env.backup
mysqldump -h stech.ft.unsoed.ac.id -u stech -p 'stech-db' > $BK/db.sql
tar czf $BK/storage-app.tgz storage/app
ls -lh $BK
```

Perintah `mysqldump` akan meminta password database. Pastikan `db.sql`
ukurannya tidak 0 byte sebelum lanjut.

---

## LANGKAH 4 — Ambil kode baru

Masih di dalam SSH, di folder aplikasi:

```bash
git checkout -- bootstrap/cache/.gitignore package-lock.json
git pull origin main
composer install --no-dev --optimize-autoloader
```

`composer install` sekaligus menerbitkan aset Filament, jadi tidak perlu
perintah terpisah.

---

## LANGKAH 5 — Pasang aset frontend

```bash
rm -rf public/build
tar -xzf ~/tmp/build.tgz -C public
rm -f public/hot
ls public/build/assets | head
```

`public/hot` harus dihapus — kalau file itu ada, situs memaksa mengambil aset
dari `localhost` dan halaman jadi rusak.

---

## LANGKAH 6 — Migrasi & cache

```bash
php artisan migrate --force
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`--force` wajib karena server memakai `APP_ENV=production`.

> **Penting:** setiap kali `.env` diubah, `php artisan config:cache` **harus**
> dijalankan ulang. Kalau tidak, perubahan `.env` tidak akan terbaca.

---

## LANGKAH 7 — Verifikasi

Masih di server:

```bash
curl -s -o /dev/null -w "beranda: %{http_code}\n" https://stech.ft.unsoed.ac.id
curl -s -o /dev/null -w "admin  : %{http_code}\n" https://stech.ft.unsoed.ac.id/admin/login
```

Keduanya harus `200`.

Lalu buka di browser dan pastikan:

- [ ] Halaman depan tampil **lengkap dengan warna/gambar** (bukan teks polos)
- [ ] Timeline menampilkan 6 tahap (11 Sep – 31 Okt)
- [ ] Biaya tertulis **Rp 100.000** (bukan 75.000)
- [ ] Format tim **2–3 orang**
- [ ] Footer ada **Narahubung: Aldi**
- [ ] Coba daftar 1 tim uji, lalu hapus lagi lewat panel admin

Kalau halaman tampil polos tanpa CSS → Langkah 5 terlewat.

---

## ⚠️ CATATAN PENTING SEBELUM PENDAFTARAN DIBUKA

### 1. Panel admin masih bermasalah (belum selesai)

Antivirus server (maldet/ClamAV) menghapus otomatis file JavaScript Filament &
Livewire, sehingga panel admin hanya menampilkan bar atas tanpa isi.

**Perlu tindakan:** kirim permintaan whitelist ke admin server FT. Draft surat
sudah disiapkan di [PERMINTAAN-WHITELIST.md](PERMINTAAN-WHITELIST.md).

Setelah admin menyetujui, jalankan di server:

```bash
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
git checkout -- public/js/
php artisan livewire:publish --assets
php artisan filament:assets
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Sisi peserta (pendaftaran, unggah bukti, submit karya) **tidak terpengaruh** —
semuanya tetap berjalan normal.

### 2. Ganti password admin

Password masih `admin123` dan tertulis di repo GitHub yang bersifat publik.
Ganti sebelum pendaftaran dibuka:

```bash
php artisan tinker
```

Lalu ketik (ganti dengan password pilihanmu):

```php
$u = App\Models\User::where('email','admin@stech.id')->first();
$u->password = Hash::make('PASSWORD_BARU_YANG_KUAT');
$u->save();
exit
```

### 3. Nomor rekening masih "Menyusul"

Saat ini dashboard peserta menampilkan "Menyusul" dan meminta peserta menunggu
pengumuman. Begitu rekening sudah ada, ubah di
[`resources/js/Pages/Dashboard.vue`](resources/js/Pages/Dashboard.vue) — cari
kata `Menyusul` — lalu `npm run build` dan ulangi Langkah 1–6.

### 4. Jadwal pendaftaran sudah otomatis

Pendaftaran hanya menerima pendaftar **11 Sep – 11 Okt 2026**, dan pengumpulan
karya ditutup **11 Okt 2026**. Di luar itu, halaman depan menampilkan
keterangan dan tombol daftar dinonaktifkan — server juga menolak walau ada yang
mencoba mengirim data langsung.

> **Yang perlu diperhatikan saat deploy:** hari ini pendaftaran **belum dibuka**
> (baru mulai 11 Sep). Jadi setelah deploy, wajar kalau halaman depan
> menampilkan *"Pendaftaran dibuka mulai 11 September 2026"* dan tombol daftar
> tidak bisa diklik. Itu bukan error.

Kalau jadwal berubah, **tidak perlu ubah kode** — cukup tambahkan di `.env`
server lalu jalankan `php artisan config:cache`:

```
STECH_REG_OPENS_AT="2026-09-11 00:00:00"
STECH_REG_CLOSES_AT="2026-10-18 23:59:59"
STECH_SUBMISSION_CLOSES_AT="2026-10-18 23:59:59"
```

Untuk membuka pendaftaran lebih awal saat uji coba, ubah `STECH_REG_OPENS_AT`
ke tanggal yang sudah lewat.

### 5. Jangan jalankan seeder demo di server

```
JANGAN:  php artisan db:seed --class=DemoParticipantsSeeder
```

Perintah itu **menghapus seluruh akun peserta** lalu menggantinya dengan data
dummy. Hanya untuk komputer lokal.

---

## Kalau terjadi masalah

| Gejala | Penyebab & solusi |
|---|---|
| Halaman polos tanpa CSS | Langkah 5 terlewat, atau `public/hot` masih ada |
| `Class "Filament\..." not found` | `composer install` belum dijalankan (Langkah 4) |
| Perubahan `.env` tidak berpengaruh | `php artisan config:cache` belum diulang |
| Error 500 tanpa keterangan | Normal (debug dimatikan). Lihat `storage/logs/laravel.log` |
| `Permission denied` saat unggah bukti | Ulangi `chmod -R 775 storage bootstrap/cache` |
| Panel admin 404 | Ulangi `php artisan route:cache` |
| SSH `Connection refused` | Belum terhubung wifi kampus |
| Login admin gagal padahal benar | Situs dibuka lewat `http://`, harus `https://` |

### Cara mengembalikan (rollback)

Kalau deploy bermasalah berat, kembalikan dari backup Langkah 3:

```bash
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
git log --oneline -5                      # cari commit sebelumnya
git reset --hard <commit-sebelumnya>
composer install --no-dev --optimize-autoloader
mysql -h stech.ft.unsoed.ac.id -u stech -p 'stech-db' < ~/backups/predeploy-XXX/db.sql
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## Ringkasan perintah (untuk deploy berikutnya)

```bash
# --- di laptop ---
npm run build
git add -A && git commit -m "update" && git push origin main
tar -czf build.tgz -C public build
scp build.tgz unsoed-stech-ft@stech.ft.unsoed.ac.id:~/tmp/

# --- di server (setelah ssh) ---
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
BK=~/backups/predeploy-$(date +%Y%m%d-%H%M%S); mkdir -p $BK && cp .env $BK/
mysqldump -h stech.ft.unsoed.ac.id -u stech -p 'stech-db' > $BK/db.sql
git pull origin main
composer install --no-dev --optimize-autoloader
rm -rf public/build && tar -xzf ~/tmp/build.tgz -C public && rm -f public/hot
php artisan migrate --force
chmod -R 775 storage bootstrap/cache
php artisan config:cache && php artisan route:cache && php artisan view:cache
curl -s -o /dev/null -w "%{http_code}\n" https://stech.ft.unsoed.ac.id
```

---

## Info server (referensi)

| | |
|---|---|
| Web server | nginx (native, **bukan Docker**) |
| PHP | 8.4.22 |
| Composer | 2.9.3 |
| Node di server | 18.19.1 (terlalu tua untuk build) |
| Database | `stech-db`, user `stech`, host `stech.ft.unsoed.ac.id:3306` |
| `.env` server | sudah benar, **jangan ditimpa** |

> `docker-compose.yml` dan `Dockerfile` di repo **tidak dipakai** di server ini.
> Keduanya sisa percobaan deploy lama di VPS. Abaikan saja.
