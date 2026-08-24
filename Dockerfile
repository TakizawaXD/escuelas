FROM php:8.3-apache

# Instalar dependencias del sistema y drivers de SQLite
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    git \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . /var/www/html/

# Instalar dependencias de producción con Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

# Asegurar la existencia de carpetas críticas y asignar permisos de escritura a Apache (www-data)
RUN mkdir -p logs cache uploads \
    && chown -R www-data:www-data database logs cache uploads \
    && chmod -R 775 database logs cache uploads

EXPOSE 80
