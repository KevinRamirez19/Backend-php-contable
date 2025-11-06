FROM php:8.2-fpm

# 1️⃣ Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2️⃣ Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 3️⃣ Crear usuario
RUN useradd -G www-data,root -u 1000 -d /home/concesionario concesionario \
    && mkdir -p /home/concesionario/.composer \
    && chown -R concesionario:concesionario /home/concesionario

# 4️⃣ Directorio de trabajo
WORKDIR /var/www

# 5️⃣ Copiar solo composer.json y composer.lock primero
COPY composer.json composer.lock ./

# 6️⃣ Instalar dependencias antes de copiar el código (más rápido y limpio)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 7️⃣ Copiar el resto del código
COPY --chown=concesionario:concesionario . .

# 8️⃣ Cambiar a usuario sin privilegios
USER concesionario

# 9️⃣ Comando de inicio (usa el puerto dinámico de Railway)
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=${PORT}"]
