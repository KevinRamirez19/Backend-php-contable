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

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Crear usuario para la app
RUN useradd -G www-data,root -u 1000 -d /home/concesionario concesionario \
    && mkdir -p /home/concesionario/.composer \
    && chown -R concesionario:concesionario /home/concesionario

# Directorio de trabajo
WORKDIR /var/www

# Copiar código con permisos correctos
COPY --chown=concesionario:concesionario . .

# Instalar dependencias de PHP con Composer
RUN composer install --no-dev --optimize-autoloader

# Cambiar a usuario no root
USER concesionario

# Comando de inicio (Railway usará el puerto asignado automáticamente)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=${PORT}"]
