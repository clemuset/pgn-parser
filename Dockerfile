FROM composer:2 AS composer_stage

FROM php:8.4-cli AS app
WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer_stage /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-progress --ansi

COPY . .

RUN composer dump-autoload --optimize
