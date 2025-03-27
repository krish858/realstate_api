FROM php:8.1-apache

# Install dependencies and MongoDB extension
RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    pkg-config \
    libssl-dev \
    libz-dev \
    zip \
    git \
    curl \
    unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy composer.json and install dependencies
WORKDIR /var/www/html
COPY composer.json .
RUN composer install

# Expose port 80
EXPOSE 80
