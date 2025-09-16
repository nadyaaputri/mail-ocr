# Gunakan base image resmi PHP 8.2 dengan FPM (sesuaikan versi jika perlu)
FROM php:8.2-fpm

# Install dependensi sistem yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    nginx

# Install ekstensi PHP yang umum digunakan Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Hapus konfigurasi default Nginx
RUN rm /etc/nginx/nginx.conf
RUN rm /etc/nginx/sites-enabled/default

# Salin file konfigurasi Nginx dari proyek kita
COPY .docker/nginx.conf /etc/nginx/nginx.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set direktori kerja
WORKDIR /var/www

# Salin semua file proyek
COPY . .

# Install dependensi Composer (tanpa dev packages)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Atur kepemilikan file agar Nginx & PHP bisa menulis ke storage
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port 80 untuk Nginx
EXPOSE 80

# Jalankan skrip startup
CMD ["/var/www/.docker/start.sh"]
