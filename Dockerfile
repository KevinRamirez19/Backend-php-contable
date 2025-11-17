# Etapa base: PHP con extensiones necesarias
FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP requeridas por Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    git \
    unzip \
    curl \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath zip opcache

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar los archivos composer primero (para aprovechar la caché de dependencias)
COPY composer.json composer.lock ./

# Crear carpetas necesarias antes de instalar dependencias
RUN mkdir -p database/seeders database/factories

# Instalar dependencias de Laravel (sin las de desarrollo)
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

# Copiar todo el código fuente
COPY . .

# Crear y asignar permisos correctos
RUN mkdir -p storage/framework/{sessions,views,cache} \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Exponer el puerto 8080
EXPOSE 8000

# Comando por defecto para ejecutar Laravel
CMD ["php-fpm"]
