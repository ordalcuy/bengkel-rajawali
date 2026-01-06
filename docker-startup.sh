#!/bin/bash

# Set environment untuk production
export APP_ENV=production
export APP_DEBUG=false

# Generate key jika belum ada
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Run migrations
php artisan migrate --force

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
exec apache2-foreground