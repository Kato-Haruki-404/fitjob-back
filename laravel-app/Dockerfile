FROM php:8.4
WORKDIR /workdir/laravel-app
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN apt-get update
RUN apt-get install -y zip

RUN docker-php-ext-install pdo_mysql

COPY . .
WORKDIR /workdir/laravel-app
RUN composer install
CMD ["php", "artisan", "serve", "--host", "0.0.0.0"]
EXPOSE 8000