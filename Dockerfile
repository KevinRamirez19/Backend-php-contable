# ==============================
# Etapa 1: Builder (instalación de dependencias)
# ==============================
FROM php:8.2-fpm AS builder

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    zip unzip git libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd mbstring exif pcntl bcmath xml zip

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copiar solo los archivos de Composer primero (para aprovechar cache de Docker)
COPY composer.json composer.lock ./

# Instalar dependencias sin dev
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --optimize-autoloader

# Copiar el resto del código fuente
COPY . .

# Crear directorios necesarios
RUN mkdir -p database/seeders database/factories \
    storage/framework/{sessions,views,cache} \
    bootstrap/cache


# ==============================
# Etapa 2: Producción
# ==============================
FROM php:8.2-fpm

# Instalar extensiones necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd mbstring exif pcntl bcmath xml zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copiar vendor desde el builder
COPY --from=builder /var/www/vendor /var/www/vendor

# Copiar el resto del código
COPY . .

# Asegurar permisos correctos
RUN mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Limpiar cachés de Laravel (si artisan está disponible)
RUN php artisan config:clear || true \
    && php artisan cache:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true

# Exponer puerto para Railway
EXPOSE 8080

# ✅ Comando correcto para iniciar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
