# # Use official PHP with Apache
# FROM php:8.2-apache

# # Enable Apache rewrite module
# RUN a2enmod rewrite

# # Copy project files into container
# COPY . /var/www/html/

# # Set working directory
# WORKDIR /var/www/html

# # Give Apache proper permissions
# RUN chown -R www-data:www-data /var/www/html \
#     && chmod -R 755 /var/www/html

# # Expose port 80
# EXPOSE 80
FROM php:8.2-apache

# Install system dependencies + mysqli + pdo_mysql
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
        zip \
        unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

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
