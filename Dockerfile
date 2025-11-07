# 🧱 Etapa 1: Construcción con Composer
FROM composer:2 AS build

WORKDIR /app

# Copiar solo los archivos necesarios para instalar dependencias
COPY composer.json composer.lock ./

# Instalar dependencias de producción (sin dev)
RUN composer install --no-dev --optimize-autoloader

# Copiar el resto del proyecto
COPY . .

# 🧱 Etapa 2: Imagen base PHP con extensiones de Laravel
FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copiar composer desde la imagen anterior
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar archivos desde la etapa build
WORKDIR /var/www
COPY --from=build /app .

# 🔹 Regenerar autoload y limpiar cachés
RUN composer dump-autoload -o
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan route:clear || true
RUN php artisan view:clear || true
RUN php artisan optimize:clear || true

# 🔹 Permisos de almacenamiento y caché
RUN chmod -R 775 storage bootstrap/cache && chown -R www-data:www-data storage bootstrap/cache

# Puerto expuesto
EXPOSE 8000

# Comando de inicio (usa JSON array para evitar errores de señales)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
