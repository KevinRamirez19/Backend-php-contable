# Etapa 1: Builder
FROM composer:2 AS builder

WORKDIR /app

# Copiar composer.json y composer.lock primero (mejor caching)
COPY composer.json composer.lock ./

# Crear carpetas vacías para evitar errores de autoload
RUN mkdir -p database/seeders database/factories

# Instalar dependencias sin las de desarrollo
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader


# Etapa 2: Aplicación PHP
FROM php:8.2-fpm AS app

# Instalar extensiones necesarias para Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    git \
    unzip \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath opcache

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar dependencias instaladas desde builder
COPY --from=builder /app/vendor ./vendor

# Copiar el resto del código fuente
COPY . .

# Dar permisos a storage y bootstrap
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Exponer puerto del servidor PHP-FPM
EXPOSE 9000

# Comando de inicio del contenedor
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
