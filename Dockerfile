# Etapa base
FROM php:8.2-fpm

# Instalar dependencias del sistema necesarias para GD, ZIP y otras librerías
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql zip bcmath

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos composer antes del resto del código (para aprovechar cache de Docker)
COPY composer.json composer.lock ./

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Instalar dependencias de producción
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar todo el proyecto al contenedor
COPY . .

# Dar permisos a carpetas necesarias
RUN chmod -R 777 storage bootstrap/cache

# Limpiar caché de Laravel y optimizar
RUN php artisan optimize:clear || true
RUN composer dump-autoload -o

# Exponer el puerto
EXPOSE 8000

# Comando para ejecutar la aplicación
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
