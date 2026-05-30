# syntax=docker/dockerfile:1
###############################################################################
# Single, optimized PHP 8.4-FPM image shared by every PHP-based service
# (app / queue / scheduler / reverb). Keeping ONE image avoids duplication and
# guarantees every container runs with the exact same runtime + extensions.
###############################################################################
FROM php:8.4-fpm-bookworm

# ---------------------------------------------------------------------------
# System packages required to build PHP extensions and run Composer/Node.
# ---------------------------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        ca-certificates \
        unzip \
        zip \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libsqlite3-dev \
        default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------------------
# Core PHP extensions needed by Laravel + MySQL + image handling + queues.
# (pdo_sqlite is included so the test suite can use an in-memory database.)
# ---------------------------------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        pdo_sqlite \
        mysqli \
        mbstring \
        bcmath \
        gd \
        zip \
        intl \
        exif \
        pcntl \
        opcache

# ---------------------------------------------------------------------------
# Redis extension (cache + queue + broadcasting scaling) via PECL.
# ---------------------------------------------------------------------------
RUN pecl install redis \
    && docker-php-ext-enable redis

# ---------------------------------------------------------------------------
# Composer (pinned major) copied straight from the official image.
# ---------------------------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------------------------
# Node.js 22 LTS + npm so the app image can build/serve Vite assets too.
# ---------------------------------------------------------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------------------------
# Tuned PHP ini for local development.
# ---------------------------------------------------------------------------
COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-app.ini

# ---------------------------------------------------------------------------
# Entrypoint: bootstraps the app (composer install, .env, key, permissions)
# in an idempotent way, then hands over to the container command.
# ---------------------------------------------------------------------------
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html

# Match the host bind-mount ownership to the php-fpm runtime user.
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data || true

ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

