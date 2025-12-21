FROM php:8.2-cli

# System dependencies
RUN apt-get update && apt-get install -y \
    git unzip curl libpng-dev libonig-dev libxml2-dev zip \
    ca-certificates gnupg

# Install Node.js 18 (AMAN untuk React/Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring bcmath gd

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Backend dependencies
RUN composer install --no-dev --optimize-autoloader

# Frontend build
RUN npm install
RUN npm run build

# Permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

# Start Laravel (PUBLIC)
CMD php artisan migrate --force --seed --host=0.0.0.0 --port=8080



