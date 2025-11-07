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

# Instalar Composer globalmente
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Limpiar e instalar dependencias desde cero
RUN rm -rf vendor composer.lock
RUN composer install --no-dev --optimize-autoloader

# Otorgar permisos correctos
RUN chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Exponer puerto (Railway ignora el número, pero es buena práctica)
EXPOSE 8000

# 🔹 Comando de arranque
# Limpiamos y optimizamos caches al iniciar el contenedor, no al construirlo
CMD php artisan optimize:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=8000
