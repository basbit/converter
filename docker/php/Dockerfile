FROM php:8.0.3-fpm as php

ENV PHPREDIS_VERSION="5.3.2" \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS="0" \
    PHP_OPCACHE_MAX_ACCELERATED_FILES="10000" \
    PHP_OPCACHE_MEMORY_CONSUMPTION="192" \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE="10"

RUN apt-get update && apt-get install -y git make libzip-dev zip curl libpq5 libpq-dev; \
    docker-php-ext-install zip pdo pdo_pgsql; \
    apt-get autoremove --purge -y libpq-dev; \
    apt-get clean; \
    rm -rf /var/lib/apt

RUN pecl install redis-$PHPREDIS_VERSION;\
    pecl install; \
    docker-php-ext-enable redis

RUN curl -sS https://getcomposer.org/installer | php -- \
    --filename=composer --install-dir=/usr/local/bin; \
    echo "alias composer='composer'" >> /root/.bashrc

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /app

COPY composer.json /app
COPY composer.lock /app
COPY symfony.lock /app

RUN composer install --no-ansi --no-scripts --no-interaction --no-progress --optimize-autoloader

COPY bin /app/bin
COPY config /app/config
COPY src /app/src
COPY public /app/public
