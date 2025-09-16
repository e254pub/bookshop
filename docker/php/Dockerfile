FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    libzip-dev \
    zip \
    unzip \
    g++ \
    make \
    autoconf \
    file \
    icu-dev \
    && docker-php-ext-install -j$(nproc) opcache pdo_mysql zip intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN mkdir -p var/cache var/log var/sessions \
    && chmod -R 777 var/cache var/log var/sessions

CMD ["php-fpm"]