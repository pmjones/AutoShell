FROM composer:2.4.4 as composer
FROM php:8.2-fpm

COPY ./extra.ini /usr/local/etc/php/conf.d/

# Update
RUN apt update -y && \
    apt install -y \
        apt-utils \
        git \
        libzip-dev \
        nano \
        sudo \
        wget \
        zip

# PECL Packages
RUN pecl install xdebug

RUN docker-php-ext-install zip

# Install PHP extensions
RUN docker-php-ext-enable xdebug

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
# Bash script with helper aliases
COPY ./.bashrc /root/.bashrc

CMD ["php-fpm"]
