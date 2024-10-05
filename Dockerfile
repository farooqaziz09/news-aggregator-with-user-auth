FROM php:8.2-fpm

# COPY COMPOSER
COPY composer.lock composer.json /var/www/

# Set Working Dir
WORKDIR /var/www

# Install system dependencies
#  * git zip unzip - Required by Composer
#  * mysql-client - Required for db migration
#  * libicu-dev - Required for php int ext

RUN apt-get update && apt-get install -y  git zip unzip curl libicu-dev

# Clear apt cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
#  * bcmath pdo_mysql mbstring intl - Required by laravel/framework
#  * pcntl - Required by laravel/horizon
#  * exif - Required by spatie/image
RUN docker-php-ext-install bcmath pdo_mysql mbstring intl pcntl exif

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Add user for laravel application
RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

# Copy existing application directory contents
COPY . /var/www

# Copy existing application directory permissions
COPY --chown=www:www . /var/www

# Change current user to www
USER www

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]




