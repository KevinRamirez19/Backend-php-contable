FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev

# Instalar extensiones necesarias de PHP
RUN docker-php-ext-install pdo pdo_mysql gd

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de Composer
RUN curl -sS https://getcomposer.org/installer | php
RUN php composer.phar install --no-dev --optimize-autoloader

# Asignar permisos a Laravel
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Exponer puerto
EXPOSE 8000

# Ejecutar Laravel
CMD php artisan serve --host=0.0.0.0 --port=8000
