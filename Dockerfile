# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy your PHP files into the container
COPY . /var/www/html/

# Enable Apache rewrite module (if needed)
RUN a2enmod rewrite

# Set proper permissions (optional)
RUN chown -R www-data:www-data /var/www/html
