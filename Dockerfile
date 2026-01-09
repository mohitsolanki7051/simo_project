FROM dunglas/frankenphp:php8.2

# System dependencies + MongoDB extension
RUN apt-get update && apt-get install -y \
    git unzip libssl-dev pkg-config \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy app code
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install PHP deps & cache Laravel for production
RUN composer install --no-dev --optimize-autoloader --ignore-platform-req=ext-mongodb \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# FrankenPHP entrypoint
CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
