FROM php:8.4

WORKDIR /workdir/laravel-app

# composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# OS deps
RUN apt-get update \
    && apt-get install -y zip \
    && docker-php-ext-install pdo_mysql

# app copy
COPY . .

# install deps
RUN composer install --no-dev --optimize-autoloader

# Render 用（最重要）
CMD php artisan serve --host=0.0.0.0 --port=${PORT}
