FROM dunglas/frankenphp:1-php8.4-bookworm AS base

# Extensions required by Laravel 11, Filament, Spatie Media Library and the
# HTTP libSQL client (sqlite3 is used for value escaping).
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && install-php-extensions \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        pdo_mysql \
        pdo_sqlite \
        sqlite3 \
        zip \
    && printf 'upload_max_filesize=64M\npost_max_size=64M\nmemory_limit=512M\nmax_execution_time=120\n' \
        > "$PHP_INI_DIR/conf.d/zz-costa-resin.ini"

WORKDIR /app

FROM base AS dependencies

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --no-scripts \
        --optimize-autoloader \
        --prefer-dist

COPY . .
RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/app/public \
        bootstrap/cache \
    && composer dump-autoload --no-dev --classmap-authoritative \
    && php artisan event:cache \
    && php artisan view:cache

FROM base AS runtime

COPY --from=dependencies --chown=www-data:www-data /app /app
COPY --chown=www-data:www-data Caddyfile /etc/frankenphp/Caddyfile

RUN setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R www-data:www-data /config/caddy /data/caddy \
    && chmod -R 775 /app/storage /app/bootstrap/cache

ENV PORT=80 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=libsql \
    SESSION_DRIVER=database \
    CACHE_STORE=database \
    QUEUE_CONNECTION=sync \
    FILESYSTEM_DISK=public \
    PUBLIC_DISK_DRIVER=s3 \
    MEDIA_DISK=public

USER www-data

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
