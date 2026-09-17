FROM php:8.4-fpm-alpine

RUN apk add --no-cache bash postgresql-client libpq-dev icu-dev libzip-dev zip unzip git curl autoconf g++ make \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql bcmath intl zip opcache pcntl \
    && pecl install redis && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000

CMD ["php-fpm"]