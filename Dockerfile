# syntax=docker/dockerfile:1
# https://docs.docker.com/go/dockerfile-reference/
FROM php:8.4.11-apache

# install dependencies.
RUN apt-get update \
    && apt-get install -y gcc \
    && apt-get install -y libpq-dev \
    && apt-get install -y curl \
    && apt-get install -y libcurl4-openssl-dev

# enable PGSQL and curl.
RUN docker-php-ext-install curl \
 && docker-php-ext-enable curl

RUN docker-php-ext-install pgsql \
 && docker-php-ext-enable pgsql

# Expose ports.
EXPOSE 80
EXPOSE 443

# Use the default production configuration for PHP runtime arguments, see
# https://github.com/docker-library/docs/tree/master/php#configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Copy config over
COPY default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
# Enable SSL in Apache2
RUN a2enmod ssl && a2enmod rewrite
# Disable the default site and enable the SSL site
RUN a2dissite 000-default.conf && a2ensite default-ssl.conf

# Copy app files from the app directory.
COPY . /var/www/html

# Switch to a non-privileged user (defined in the base image) that the app will run under.
# See https://docs.docker.com/go/dockerfile-user-best-practices/

# IF YOU RUN INTO PERMISSIONS PROBLEMS WITH CERTIFICATE AND/OR KEY, www-data UID IS 33
# DO chown 33 <path-to-cert> 
# DO chown 33 <path-to-key> 
USER www-data
