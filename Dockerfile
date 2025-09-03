FROM php:8.2-apache

# Install system dependencies + PostgreSQL extensions
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        zip \
        unzip \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-enable pdo_pgsql pgsql

# Enable Apache rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

