FROM dunglas/frankenphp:php8.2

RUN apt-get update && apt-get install -y \
    git unzip libssl-dev pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-mongodb \
    && php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

EXPOSE 8080

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
