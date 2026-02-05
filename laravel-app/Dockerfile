FROM php:8.4

WORKDIR /workdir/laravel-app

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y zip unzip git \
    && docker-php-ext-install pdo_mysql

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}
