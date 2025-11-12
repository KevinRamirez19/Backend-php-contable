# Etapa base: PHP con extensiones necesarias
FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP
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

# Copiar composer antes del código para aprovechar caché de dependencias
COPY composer.json composer.lock ./

# Crear carpetas necesarias antes de instalar dependencias
RUN mkdir -p database/seeders database/factories

# Instalar dependencias de Laravel (sin las de desarrollo)
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

# Copiar todo el código fuente
COPY . .

# Crear y asignar permisos a las carpetas necesarias
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Exponer el puerto 8080
EXPOSE 9000

# Comando por defecto para ejecutar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
