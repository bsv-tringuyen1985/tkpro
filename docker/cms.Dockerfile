# -----------------------------------------------------------------------------
# TKPRO CMS — Laravel HTTP server, port 8001
# Dùng CHUNG database tkpro với api (DB_HOST=mysql).
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

# Copy mã nguồn cms (Laravel, routes có prefix /admin)
COPY ./cms/ /var/www/html/

COPY ./docker/db-probe.php /usr/local/bin/db-probe.php
COPY ./docker/merge-media.php /usr/local/bin/merge-media.php

# Entrypoint: chờ API migrate/seed, nhập ảnh cũ, storage:link, HTTP server
COPY ./docker/cms-entrypoint.sh /usr/local/bin/cms-entrypoint.sh
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction \
    && chmod +x /usr/local/bin/cms-entrypoint.sh \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8001

ENTRYPOINT ["/usr/local/bin/cms-entrypoint.sh"]
