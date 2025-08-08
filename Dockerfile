# MindMate - PHP 8.2 + Apache for Railway
FROM php:8.2-apache

# Install PHP extensions needed by the app (mysqli, pdo_mysql)
RUN docker-php-ext-install mysqli pdo pdo_mysql \
    && a2enmod rewrite

# Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

# Set working directory
WORKDIR /var/www/html

# Copy composer files first to leverage Docker layer caching
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev) with optimized autoloader
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

# Copy application source
COPY . .

# Ensure Apache can read the app
RUN chown -R www-data:www-data /var/www/html

# Environment
ENV APP_ENV=production

# Expose default Apache port (Railway will map this automatically)
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
