#!/bin/sh
###############################################################################
# Container bootstrap.
#
# Only the container running with CONTAINER_ROLE=app performs the one-time
# bootstrap (composer install, .env creation, key generation, permissions).
# Every other PHP container (queue/scheduler/reverb) WAITS for the bootstrap
# marker before starting its command. This prevents race conditions such as
# multiple containers generating the application key at the same time.
###############################################################################
set -e
cd /var/www/html
MARKER="storage/framework/.bootstrapped"
ROLE="${CONTAINER_ROLE:-worker}"
if [ "$ROLE" = "app" ]; then
    # Fresh marker each boot so dependents wait for THIS bootstrap to finish.
    rm -f "$MARKER"
    # 1. Install PHP dependencies if vendor/ is missing (fresh clone).
    if [ ! -f vendor/autoload.php ]; then
        echo "[entrypoint] Installing composer dependencies..."
        composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
    fi
    # 2. Ensure an .env file exists.
    if [ ! -f .env ]; then
        echo "[entrypoint] Creating .env from .env.example..."
        cp .env.example .env
    fi
    # 3. Generate the application key only if it has not been set yet.
    if ! grep -q "^APP_KEY=base64:" .env; then
        echo "[entrypoint] Generating application key..."
        php artisan key:generate --force
    fi
    # 4. Ensure runtime directories exist and are writable by php-fpm.
    mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/testing \
        storage/logs \
        bootstrap/cache
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    # Signal that bootstrap is complete.
    touch "$MARKER"
    echo "[entrypoint] Bootstrap complete."
else
    echo "[entrypoint] Waiting for app bootstrap to finish..."
    i=0
    while [ ! -f "$MARKER" ] && [ "$i" -lt 150 ]; do
        sleep 2
        i=$((i + 1))
    done
fi
echo "[entrypoint] Starting: $*"
exec "$@"
