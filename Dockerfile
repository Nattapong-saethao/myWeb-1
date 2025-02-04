FROM php:8.2-apache

# Copy application
COPY . /var/www/html/

# Install mysqli extension
RUN docker-php-ext-install mysqli

# Install Composer
RUN apt-get update && apt-get install -y curl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy Apache config
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Enable site
RUN a2ensite 000-default.conf

# Set document root for apache
WORKDIR /var/www/html