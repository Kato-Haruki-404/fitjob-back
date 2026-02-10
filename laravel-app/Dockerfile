FROM php:8.4

WORKDIR /workdir/laravel-app

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update \
    && apt-get install -y zip unzip git libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Generate config cache at runtime when env vars are available
CMD php artisan config:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT}
