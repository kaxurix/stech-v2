# Draft Permintaan Whitelist ke Admin Server FT Unsoed

Salin isi di bawah ini (bagian setelah garis) dan kirim ke pengelola server
`stech.ft.unsoed.ac.id`. Sudah disusun agar admin bisa langsung bertindak
tanpa perlu bolak-balik bertanya.

---

**Perihal: Permintaan pengecualian antivirus untuk folder aset aplikasi stech.ft.unsoed.ac.id**

Selamat pagi/siang Bapak/Ibu,

Saya Kautsar, pengelola aplikasi pendaftaran S-Tech di `stech.ft.unsoed.ac.id`
(akun `unsoed-stech-ft`). Mohon bantuannya untuk satu kendala teknis.

**Masalah**

Setelah pembaruan aplikasi, panel admin tidak dapat berjalan karena beberapa
berkas JavaScript milik framework (Filament dan Livewire) terhapus otomatis
beberapa menit setelah dibuat. Berkas yang terdampak:

```
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/js/filament/filament/app.js
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/js/filament/filament/echo.js
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/js/filament/forms/components/code-editor.js
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/js/filament/forms/components/file-upload.js
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/js/filament/forms/components/markdown-editor.js
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/js/filament/forms/components/rich-editor.js
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/vendor/livewire/livewire.min.js
```

**Hasil pengujian yang sudah saya lakukan**

| Pengujian | Hasil |
|---|---|
| Berkas dipulihkan, langsung diakses | Normal (HTTP 200) |
| Berkas yang sama, 2 menit kemudian | Hilang (HTTP 404) |
| Salinan dengan nama berbeda | Ikut terhapus |
| Salinan dengan ekstensi `.txt` | Ikut terhapus |
| Salinan identik di luar folder web (`~/tmp/`) | **Tidak terhapus** |
| `clamdscan` manual atas berkas sumber | **Bersih, 0 infected** |

Kesimpulan: pemicunya adalah isi berkas (bukan nama/ekstensi), dan hanya
terjadi di dalam folder web. Karena pemindaian manual justru menyatakan bersih,
kemungkinan besar ini **deteksi keliru (false positive)** pada berkas library
open-source resmi.

Pada log `/usr/local/maldetect/logs/event_log` terlihat monitor memindai setiap
±30 detik, dan berkas hilang tepat setelah siklus pemindaian.

**Permohonan**

Mohon ditambahkan pengecualian pemindaian (whitelist) untuk dua folder berikut:

```
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/js/
/home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id/public/vendor/
```

Pada maldet, biasanya cukup ditambahkan ke `/usr/local/maldetect/ignore_paths`
lalu monitor di-restart.

**Catatan keamanan**

- Kedua folder tersebut **hanya berisi berkas library** dari framework, dan
  tidak pernah menerima input/unggahan dari pengunjung.
- Berkas unggahan peserta (bukti pembayaran) disimpan di folder terpisah
  `storage/app/private/` dan **tidak termasuk dalam permintaan ini**, sehingga
  tetap dipindai sebagaimana mestinya.
- Pengecualian ini justru **mengurangi beban CPU server**, karena pemindaian
  berulang atas berkas besar tersebut tidak perlu lagi dilakukan.

Jika Bapak/Ibu memandang perlu, saya bersedia menyediakan checksum berkas atau
mengirimkan berkasnya untuk diperiksa lebih dulu.

Terima kasih banyak atas bantuannya.

Hormat saya,
Kautsar Rifqi
Pengelola aplikasi S-Tech — Himpunan Mahasiswa Informatika Unsoed

---

## Setelah whitelist disetujui

Jalankan di server:

```bash
cd /home/unsoed-stech-ft/htdocs/stech.ft.unsoed.ac.id
git checkout -- public/js/
php artisan livewire:publish --assets
php artisan filament:assets
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Lalu buka `https://stech.ft.unsoed.ac.id/admin` — sidebar dan dashboard
seharusnya sudah tampil normal.
