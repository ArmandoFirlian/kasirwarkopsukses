FROM php:8.2-fpm

# Install dependencies sistem & ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Install dependency laravel
RUN composer install --no-dev --optimize-autoloader

# Atur permission storage & cache laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 10000

# Script jalan awal
CMD php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=10000