ARG PHP_VERSION=8.4
FROM php:${PHP_VERSION}-apache

# 1. Install dependencies sistem
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    nodejs \
    npm

# Hapus cache apt setelah instalasi untuk memperkecil ukuran image
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions yang dibutuhkan Laravel
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install & aktifkan ekstensi Redis
RUN pecl install redis \
    && docker-php-ext-enable redis

# 3. Aktifkan modul mod_rewrite Apache untuk routing Laravel
RUN a2enmod rewrite

# 4. Ubah DocumentRoot Apache agar langsung mengarah ke folder /public milik Laravel
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/default-ssl.conf

# 5. Install Composer terbaru
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set direktori kerja utama
WORKDIR /var/www/html

# Menyalin seluruh kode aplikasi Anda dari repositori ke dalam container
COPY --chown=www-data:www-data . /var/www/html

# 6. Jalankan instalasi dependensi Laravel (opsional, sesuaikan dengan alur deploy Anda)
# RUN composer install --no-interaction --optimize-autoloader --no-dev

# Fix konfigurasi untuk psysh (Laravel Tinker)
RUN mkdir -p /.config/psysh && chmod 777 /.config/psysh
ENV HOME=/var/www/html

# 7. Amankan hak akses folder, berikan izin write khusus untuk folder storage & cache Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && if [ -d "/var/www/html/storage" ]; then chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache; fi

# Beralih ke user non-root demi keamanan
USER www-data

# Membuka port web 80 Apache
EXPOSE 80
CMD ["apache2-foreground"]
