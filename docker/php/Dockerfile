ARG PHP_VERSION=8.4
#FROM php:${PHP_VERSION}-fpm
FROM php:${PHP_VERSION}-apache

# Install dependencies
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

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip
# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html
# Fix untuk psysh (tinker)
RUN mkdir -p /.config/psysh && chmod 777 /.config/psysh
ENV HOME=/var/www/html
# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

USER www-data

#EXPOSE 9000
#CMD ["php-fpm"]
EXPOSE 80
CMD ["apache2-foreground"]
