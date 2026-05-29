#!/bin/sh
set -e

# =========================================================================
# DOCKER ENTRYPOINT FOR LARAVEL (OTOMATISASI STORAGE & PERMISSIONS)
# =========================================================================
# Script ini berjalan otomatis setiap kali kontainer dinyalakan.
# Berguna untuk membuat subfolder Laravel yang hilang dan memperbaiki hak akses.
# =========================================================================

echo "🚀 Memulai inisialisasi kontainer..."

# 1. Buat folder-folder storage yang dibutuhkan Laravel jika belum ada
echo "📁 Membuat folder storage yang diperlukan..."
mkdir -p storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

# 2. Atur permission dan ownership agar bisa ditulis oleh user www-data (internal PHP-FPM)
echo "🔒 Mengatur hak akses folder storage dan bootstrap/cache..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✅ Inisialisasi selesai. Menjalankan PHP-FPM..."

# Jalankan perintah utama yang dilewatkan (misal: php-fpm)
exec "$@"
