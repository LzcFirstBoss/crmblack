# ---------- BUILD (Composer) ----------
FROM composer:2.7 AS build
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress
COPY . .
RUN composer dump-autoload -o

# ---------- RUNTIME (PHP-FPM + Nginx + Supervisor) ----------
FROM php:8.2-fpm-alpine

# Pacotes do sistema
RUN apk add --no-cache bash git unzip nginx supervisor curl libpng-dev oniguruma-dev libzip-dev mariadb-client

# Extensões PHP necessárias pro Laravel
RUN docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl bcmath gd

# Composer (copiado do estágio build)
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Código da aplicação (com vendor já pronto do build)
WORKDIR /var/www
COPY --from=build /app /var/www

# Nginx config
COPY deploy/nginx.conf /etc/nginx/http.d/default.conf

# Supervisor config
COPY deploy/supervisord.conf /etc/supervisord.conf

# Permissões
RUN chown -R www-data:www-data /var/www && \
    mkdir -p /var/www/storage /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Otimizações Laravel (não falha se variáveis ainda não estiverem setadas)
RUN php artisan storage:link || true && \
    php artisan config:clear || true && \
    php artisan route:clear || true && \
    php artisan view:clear || true

EXPOSE 80

# Sobe Nginx + PHP-FPM
CMD ["/usr/bin/supervisord","-c","/etc/supervisord.conf"]
