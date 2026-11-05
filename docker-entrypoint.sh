#!/bin/sh
set -e

# 1. Pastikan seluruh folder penyimpanan yang dibutuhkan ada
mkdir -p /app/storage/framework/cache/data \
         /app/storage/framework/sessions \
         /app/storage/framework/views \
         /app/storage/logs \
         /app/storage/app/documents \
         /app/storage/app/public \
         /app/bootstrap/cache

# 2. Perbaiki kepemilikan dan hak akses volume storage untuk www-data
chown -R www-data:www-data /app/storage /app/bootstrap/cache 2>/dev/null || true
chmod -R 775 /app/storage /app/bootstrap/cache 2>/dev/null || true

# 3. Jalankan migrasi database otomatis (membuat tabel case_lawyers, document_versions, dll)
echo "Menjalankan database migration..."
php artisan migrate --force || true

# 4. Jalankan seeder basis pengetahuan hukum jika dibutuhkan oleh Asisten Hukum
echo "Menjalankan seeder basis data pengetahuan..."
php artisan db:seed --class=KnowledgeSourceSeeder --force || true

# 5. Pastikan tautan storage publik terpasang
php artisan storage:link 2>/dev/null || true

# 6. Bersihkan dan optimalkan cache Laravel
php artisan optimize:clear || true
php artisan optimize || true

# 7. Jalankan web server FrankenPHP
echo "Memulai web server FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile
