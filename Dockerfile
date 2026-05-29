# === STAGE 1: Frontend Builder ===
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
# Menggunakan npm ci untuk install dependensi yang presisi dan cepat
RUN npm ci
COPY . .
RUN npm run build

# === STAGE 2: PHP Runtime ===
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www

# Install dependensi sistem dasar Alpine
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev

# Install PHP extensions menggunakan script helper resmi (sangat andal dan rapi)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql pdo_sqlite zip bcmath intl opcache pcntl

# Salin Composer dari image resmi composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin kode aplikasi (tanpa ignore files karena sudah didefinisikan di .dockerignore)
COPY --chown=www-data:www-data . .

# Jalankan composer install untuk mengunduh package Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Salin aset frontend yang sudah dikompilasi dari Stage 1
COPY --from=frontend-builder --chown=www-data:www-data /app/public/build ./public/build

# Gunakan konfigurasi PHP production bawaan
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Set permissions untuk folder storage dan bootstrap/cache agar bisa ditulis oleh server web
RUN chmod -R 775 storage bootstrap/cache

# Jalankan PHP-FPM secara default
EXPOSE 9000
CMD ["php-fpm"]
