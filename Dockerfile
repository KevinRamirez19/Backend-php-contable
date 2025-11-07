FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev

# Instalar extensiones necesarias de PHP
RUN docker-php-ext-install pdo pdo_mysql gd zip

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php

# Limpiar e instalar dependencias desde cero
RUN rm -rf vendor composer.lock
RUN php composer.phar install --no-dev --optimize-autoloader

# 🔹 Otorgar permisos ANTES de ejecutar Artisan
RUN mkdir -p /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# 🔹 Ahora sí limpiar cachés y optimizar autoload
RUN php artisan optimize:clear && composer dump-autoload -o

# Exponer puerto
EXPOSE 8000

# Comando por defecto
CMD php artisan serve --host=0.0.0.0 --port=8000
