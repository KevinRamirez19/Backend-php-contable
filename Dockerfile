FROM php:8.2-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    zip unzip git curl \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath zip opcache \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Establecer directorio de trabajo
WORKDIR /app

# Copiar archivos composer primero (para cache de dependencias)
COPY composer.json composer.lock ./

# Instalar dependencias de PHP SIN CACHE
RUN composer clear-cache && composer install --no-dev --optimize-autoloader --no-scripts --prefer-dist

# Copiar el resto de la aplicación
COPY . .

# Configurar permisos
RUN chmod -R 775 storage bootstrap/cache

# Exponer puerto
EXPOSE $PORT

# Comando de inicio
CMD php artisan config:cache && php artisan route:cache && php -S 0.0.0.0:$PORT public/index.php