# Etapa 1: Construcción (instalación de dependencias)
FROM php:8.2-fpm AS builder

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    zip unzip git libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd mbstring exif pcntl bcmath xml zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar solo los archivos necesarios para instalar dependencias
COPY composer.json composer.lock ./

# Instalar dependencias de producción
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress

# Etapa 2: Producción
FROM php:8.2-fpm

WORKDIR /var/www

# Copiar dependencias desde el builder
COPY --from=builder /var/www/vendor /var/www/vendor

# Copiar todo el código fuente del proyecto
COPY . .

# Crear carpetas necesarias (en caso de que falten)
RUN mkdir -p database/seeders storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Optimizar Laravel
RUN php artisan config:clear || true \
    && php artisan cache:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true \
    && composer dump-autoload --optimize

EXPOSE 9000
CMD ["php-fpm"]
