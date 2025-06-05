FROM php:8.2-apache

# Install extensions and Apache modules
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    curl \
    nano \
    iputils-ping \
    libpng-dev libonig-dev libxml2-dev zip unzip \
    apache2-utils \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Enable Apache modules
RUN a2enmod rewrite headers

# RUN pecl install xdebug

# Copy the application files
COPY ./src /srv/src

WORKDIR /srv/src

RUN composer install

# Grant write permission to user www-data of apache2
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache
