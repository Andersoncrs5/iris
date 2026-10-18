# syntax=docker/dockerfile:1

FROM node:24-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN npm run build


FROM php:8.5-cli-alpine AS app
WORKDIR /var/www/html

COPY --from=ghcr.io/mlocati/php-extension-installer:latest \
     /usr/bin/install-php-extensions /usr/local/bin/

RUN apk add --no-cache curl \
    && install-php-extensions \
        bcmath \
        intl \
        opentelemetry \
        pcntl \
        pdo_pgsql \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --no-progress \
        --no-scripts \
        --no-autoloader

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN APP_ENV=testing APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
        composer dump-autoload --optimize --no-dev \
    && mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && APP_ENV=testing APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= \
        php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PHP_CLI_SERVER_WORKERS=4

USER www-data
EXPOSE 8000

HEALTHCHECK --interval=20s --timeout=5s --start-period=40s --retries=5 \
    CMD curl -fsS http://127.0.0.1:8000/up || exit 1

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]