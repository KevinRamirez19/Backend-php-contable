FROM php:8.2-apache

# Instalar extensiones PHP
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev libzip-dev \
    zip git unzip curl libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring exif pcntl bcmath zip

# Instalar Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Habilitar mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Crear carpetas necesarias
RUN mkdir -p database/seeders database/factories

# Copiar composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Copiar aplicación
COPY . .

# Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Configurar document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

EXPOSE 80

# Comando simple - las migraciones se ejecutan en el Start Command
CMD ["apache2-foreground"]