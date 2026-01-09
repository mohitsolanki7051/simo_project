FROM dunglas/frankenphp:php8.2

RUN apt-get update && apt-get install -y \
    git unzip libssl-dev pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

WORKDIR /app

COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-mongodb

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
