# Imagen base PHP con FPM
FROM php:8.2-fpm

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Crear usuario
RUN useradd -G www-data,root -u 1000 -d /home/concesionario concesionario \
    && mkdir -p /home/concesionario/.composer \
    && chown -R concesionario:concesionario /home/concesionario

# Establecer directorio de trabajo
WORKDIR /var/www

# Copiar archivos del proyecto
COPY . .

# 👇 Instalar dependencias de Composer como root para evitar errores de permisos
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Dar permisos correctos
RUN chown -R concesionario:www-data /var/www

# Cambiar al usuario no root
USER concesionario

# Comando para iniciar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=${PORT}"]
