FROM php:8.2-cli

# Instalar extensiones PHP necesarias
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev zip unzip curl git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring zip

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Directorio de trabajo
WORKDIR /app

# Copiar archivos de composer primero
COPY composer.json composer.lock ./

# Instalar dependencias
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el resto de la aplicación
COPY . .

# Crear carpetas necesarias
RUN mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Puerto
EXPOSE 8080

# Comando simple y directo
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public", "public/index.php"]