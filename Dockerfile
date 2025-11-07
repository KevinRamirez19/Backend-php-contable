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

# Copiar TODO el código fuente primero
COPY . .

# Crear directorios adicionales si no existen
RUN mkdir -p database/seeders database/factories storage/framework/{sessions,views,cache} bootstrap/cache

# Instalar dependencias de producción
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

# Etapa 2: Producción
FROM php:8.2-fpm

# Instalar solo las extensiones necesarias en producción
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd mbstring exif pcntl bcmath xml zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copiar vendor desde el builder
COPY --from=builder /var/www/vendor /var/www/vendor

# Copiar el código fuente completo
COPY . .

# Asegurar que existen las carpetas necesarias con permisos correctos
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Limpiar cachés de Laravel
RUN php artisan config:clear || true \
    && php artisan cache:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]