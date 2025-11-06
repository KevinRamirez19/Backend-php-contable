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

WORKDIR /var/www

# Copiar solo los archivos necesarios primero
COPY composer.json composer.lock ./

# ⚠️ Copiar la carpeta database ANTES de composer install
COPY database ./database

# Instalar dependencias PHP sin dev y con autoload optimizado
RUN composer install --no-dev --optimize-autoloader

# Luego copiar el resto del código fuente
COPY . .

# Crear usuario no root
RUN useradd -G www-data,root -u 1000 -d /home/concesionario concesionario \
    && mkdir -p /home/concesionario/.composer \
    && chown -R concesionario:concesionario /home/concesionario

USER concesionario

# Exponer puerto Railway
EXPOSE 8000

# Comando por defecto (usar variable PORT de Railway)
CMD ["sh", "-c", "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
