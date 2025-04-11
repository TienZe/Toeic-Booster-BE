FROM php:8.2-apache

# Install extensions
RUN apt-get update && apt-get install -y \
    unzip \
    zip \
    curl \
    nano \
    libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite headers

# Copy the application files
COPY ./src /srv/src

WORKDIR /srv/src
