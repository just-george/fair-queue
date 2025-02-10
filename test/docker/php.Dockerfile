ARG PHP_VERSION

FROM composer:2.8.5 AS composer

FROM php:${PHP_VERSION}-cli-alpine AS php
ARG PHP_VERSION
WORKDIR /app
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN apk add --no-cache $PHPIZE_DEPS
RUN yes '' | pecl install --force --onlyreqdeps redis-6.1.0 \
    &&  docker-php-ext-enable redis
RUN apk del $PHPIZE_DEPS
RUN apk add --no-cache make
