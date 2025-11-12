# Etapa 1: Builder
FROM php:8.2-fpm AS builder

# Instalar dependencias del sistema y extensiones necesarias para Composer y Laravel
RUN apt-get update && apt-get install -y \
    zip unzip git libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath opcache zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar archivos composer
COPY composer.json composer.lock ./

# Crear carpetas para evitar error de autoload
RUN mkdir -p database/seeders database/factories

# Instalar dependencias (ahora sí detecta GD)
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader


# Etapa 2: Producción
FROM php:8.2-fpm

# Instalar extensiones requeridas por Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath opcache zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copiar vendor del builder
COPY --from=builder /app/vendor ./vendor

# Copiar el resto del proyecto
COPY . .

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
