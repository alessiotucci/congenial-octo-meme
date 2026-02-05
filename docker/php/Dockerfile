# **************************************************************************** #
#     File: Dockerfile                                                         #
#     Author: atucci <atucci@student.42.fr>                                    #
#     Created: 2026/01/25 18:03:51                                             #
#     Updated: 2026/02/05 15:17:49                                             #
#     System: Linux [e4r3p4.42roma.it]                                         #
#     Hardware: Intel Core i5-8600 | RAM: 15GB                                 #
# **************************************************************************** #

FROM php:8.1-apache

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache Rewrite Module (Critical for .htaccess)
RUN a2enmod rewrite

# Allow .htaccess to override config
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Set working directory
WORKDIR /var/www/html
