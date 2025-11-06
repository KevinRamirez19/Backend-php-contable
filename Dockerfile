FROM php:8.2-fpm

# 1️⃣ Instalar dependencias del sistema
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

# 2️⃣ Instalar extensiones PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# 3️⃣ Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4️⃣ Crear usuario para la app
RUN useradd -G www-data,root -u 1000 -d /home/concesionario concesionario \
    && mkdir -p /home/concesionario/.composer \
    && chown -R concesionario:concesionario /home/concesionario

# 5️⃣ Directorio de trabajo
WORKDIR /var/www

# 6️⃣ Copiar el código del proyecto antes de instalar dependencias
COPY . .

# 7️⃣ Instalar dependencias PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 8️⃣ Cambiar permisos
RUN chown -R concesionario:www-data /var/www

# 9️⃣ Cambiar a usuario no root
USER concesionario

# 🔟 Comando para iniciar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=${PORT}"]
