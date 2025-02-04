FROM php:8.2-apache

# Copy application
COPY . /var/www/html/

# Optionally install required php extension
# RUN docker-php-ext-install pdo pdo_pgsql
# Install Composer
RUN apt-get update && apt-get install -y curl
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set document root for apache
<VirtualHost *:80>
DocumentRoot /var/www/html/page/
<Directory /var/www/html/page>
Options Indexes FollowSymLinks MultiViews
AllowOverride All
Require all granted
</Directory>
ErrorLog ${APACHE_LOG_DIR}/error.log
CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>


WORKDIR /var/www/html