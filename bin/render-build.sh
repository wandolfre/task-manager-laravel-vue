#!/usr/bin/env bash
set -e

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing Node dependencies..."
npm install

echo "Building frontend assets..."
npm run build

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Creating SQLite database if not exists..."
touch /var/data/database.sqlite

echo "Running migrations..."
php artisan migrate --force

echo "Build complete!"
