FROM php:7.3-cli-alpine

RUN apk add autoconf automake make gcc g++
RUN pecl install redis \
    && docker-php-ext-enable redis

RUN curl -sS "https://getcomposer.org/installer" | php
RUN mv composer.phar /usr/local/bin/composer
