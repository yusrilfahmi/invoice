# Gunakan base image PHP 8.2 FPM yang ringan (Alpine)
FROM php:8.2-fpm-alpine AS base

# Install dependensi sistem dan ekstensi PHP yang umum untuk Laravel
# Termasuk gd (untuk gambar) dan pdo_mysql/pgsql untuk database
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libzip-dev \
    libjpeg-turbo-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install \
    pdo pdo_mysql pdo_pgsql bcmath gd zip

# Set direktori kerja di dalam container
WORKDIR /var/www/html

# Ambil Composer (package manager untuk PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Tahap Builder ---
# Di tahap ini kita hanya fokus menginstall dependensi
FROM base AS builder
COPY . .
# Install dependensi composer dan optimalkan autoloader
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist \
    && composer dump-autoload --optimize

# --- Tahap Final ---
# Ini adalah image final yang akan dijalankan
FROM base AS final
COPY --from=builder /var/www/html .

# Expose port yang akan digunakan oleh Laravel
EXPOSE 8000

# Perintah untuk menjalankan server saat container dimulai
CMD php artisan serve --host=0.0.0.0 --port=8000