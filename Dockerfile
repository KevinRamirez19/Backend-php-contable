FROM php:8.2-cli

# Usar mirrors más rápidos y instalar solo lo esencial
RUN sed -i 's/deb.debian.org/debian.mirrors.clouvider.net/g' /etc/apt/sources.list && \
    apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev zip unzip curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Copiar solo lo necesario para cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar el resto
COPY . .

# Permisos básicos
RUN chmod -R 755 storage bootstrap/cache

EXPOSE $PORT

CMD php -S 0.0.0.0:$PORT -t public public/index.php