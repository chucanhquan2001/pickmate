#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

wait_for_database() {
    echo "Waiting for MySQL..."
    attempt=0

    until php -r 'try { new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Throwable $e) { fwrite(STDERR, $e->getMessage().PHP_EOL); exit(1); }'; do
        attempt=$((attempt + 1))

        if [ "$attempt" -ge 30 ]; then
            echo "MySQL did not become ready." >&2
            exit 1
        fi

        sleep 2
    done
}

if [ "${1:-web}" = "web" ]; then
    if [ -z "${APP_KEY:-}" ]; then
        echo "APP_KEY is empty. Set it in .env before starting the container." >&2
        exit 1
    fi

    wait_for_database
    php artisan migrate --force --no-interaction
    php artisan db:seed --force --no-interaction
    php artisan storage:link || true
    php artisan config:cache
    exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
fi

wait_for_database
exec "$@"
