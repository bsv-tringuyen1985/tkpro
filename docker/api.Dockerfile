# -----------------------------------------------------------------------------
# TKPRO API — Laravel HTTP server, port 8000
# -----------------------------------------------------------------------------
FROM php:8.2-fpm

# Cài extension PHP mà Laravel cần
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip curl libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libonig-dev libxml2-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mbstring exif pcntl bcmath zip gd xml intl fileinfo \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy mã nguồn api (Laravel: public/index.php)
COPY ./api/ /var/www/html/

COPY ./docker/db-probe.php /usr/local/bin/db-probe.php

# Entrypoint: composer install (nếu vendor thiếu), storage:link, migrate,
# seed khi DB trống, rồi khởi động HTTP server
COPY ./docker/api-entrypoint.sh /usr/local/bin/api-entrypoint.sh
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && chmod +x /usr/local/bin/api-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/api-entrypoint.sh"]
