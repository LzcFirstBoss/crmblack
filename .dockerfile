# ---- Etapa 1: Composer (build) ----
FROM composer:2.7 AS build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload -o

# ---- Etapa 2: Runtime (FrankenPHP = servidor + PHP) ----
FROM dunglas/frankenphp:1.1-php8.2
WORKDIR /app

# Extensões necessárias do PHP
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    zip \
    bcmath \
    intl \
    exif \
    pcntl \
    gd

# Copia código e vendor
COPY --from=build /app /app

# Permissões e caches
RUN chown -R www-data:www-data /app && \
    mkdir -p /app/storage /app/bootstrap/cache && \
    chown -R www-data:www-data /app/storage /app/bootstrap/cache && \
    php artisan storage:link || true && \
    php artisan config:clear || true && \
    php artisan config:cache || true && \
    php artisan route:cache || true && \
    php artisan view:cache || true

# FrankenPHP escuta nesta porta
ENV SERVER_NAME=:80
EXPOSE 80

CMD ["php", "artisan", "octane:frankenphp", "--workers=4", "--max-requests=1000"]
