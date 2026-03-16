FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libsqlite3-dev libpq-dev \
    nodejs npm \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql pdo_pgsql mbstring xml bcmath \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Install Node dependencies and build frontend
COPY package.json package-lock.json ./
RUN npm install

# Copy application code
COPY . .

# Run post-install scripts and build
RUN composer dump-autoload --optimize \
    && npm run build \
    && php artisan config:clear

# Create SQLite database directory
RUN mkdir -p /var/data && touch /var/data/database.sqlite

# Set permissions
RUN chown -R www-data:www-data /app /var/data

EXPOSE 10000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}
