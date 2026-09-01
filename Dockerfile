# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Estágio 1 — dependências PHP
# Instaladas sem dev e com o autoloader otimizado. Fica em um estágio próprio
# para que o Composer não vá junto na imagem final.
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader

# ---------------------------------------------------------------------------
# Estágio 2 — assets do front-end
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---------------------------------------------------------------------------
# Estágio 3 — imagem final
# Só o que a aplicação precisa para rodar: PHP-FPM, Nginx e o código já com as
# dependências e os assets compilados vindos dos estágios anteriores. Os
# pacotes -dev são removidos no mesmo RUN para não ficarem em uma camada.
# ---------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS production

RUN apk add --no-cache nginx supervisor \
        libpng-dev libzip-dev icu-dev oniguruma-dev postgresql-dev \
    && docker-php-ext-install \
        pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd zip intl opcache \
    && apk del libpng-dev libzip-dev icu-dev oniguruma-dev postgresql-dev \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/php/supervisord.conf /etc/supervisord.conf

RUN chmod +x /usr/local/bin/entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
