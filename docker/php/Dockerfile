# **************************************************************************** #
#     File: Dockerfile                                                         #
#     Author: atucci <atucci@student.42.fr>                                    #
#     Created: 2026/01/25 18:03:51                                             #
#     Updated: 2026/01/25 18:03:55                                             #
#     System: Linux [e4r3p4.42roma.it]                                         #
#     Hardware: Intel Core i5-8600 | RAM: 15GB                                 #
# **************************************************************************** #

FROM php:8.1-apache

# Install PDO MySQL driver
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache Mod Rewrite (For clean URLs later)
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html
