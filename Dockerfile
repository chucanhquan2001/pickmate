FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative --no-scripts

FROM php:8.3-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx supervisor curl ca-certificates \
    && docker-php-ext-install pdo_mysql opcache \
    && rm -rf /var/lib/apt/lists/* \
    && rm -f /etc/nginx/sites-enabled/default

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/zz-docker.conf /usr/local/etc/php-fpm.d/zz-docker.conf
COPY docker/nginx/default.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh

RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && chmod +x /entrypoint.sh

COPY --from=vendor /app /var/www/html

WORKDIR /var/www/html

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
CMD ["web"]
