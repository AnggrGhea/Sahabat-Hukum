
# ============================================================
# Stage 1: PHP Dependencies
# ============================================================
FROM dunglas/frankenphp:1-php8.4-alpine AS php-builder

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    zip \
    gd \
    opcache \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ============================================================
# Stage 2: Frontend Build
# ============================================================
FROM node:22-alpine AS frontend-builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

RUN npm run build

# ============================================================
# Stage 3: Production
# ============================================================
FROM dunglas/frankenphp:1-php8.4-alpine AS production

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    zip \
    gd \
    opcache \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

COPY --from=php-builder /app/vendor ./vendor

COPY --from=frontend-builder /app/public/build ./public/build

COPY Caddyfile /etc/caddy/Caddyfile
COPY docker-entrypoint.sh /app/docker-entrypoint.sh
RUN chmod +x /app/docker-entrypoint.sh

RUN php artisan package:discover --ansi

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/documents \
    storage/app/public \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 80

CMD ["/app/docker-entrypoint.sh"]
