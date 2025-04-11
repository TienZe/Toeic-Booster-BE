FROM php:8.2-cli

# Install extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    nano \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application files
COPY . /srv/app

WORKDIR /srv/app
