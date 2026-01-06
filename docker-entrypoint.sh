#!/bin/bash
# Don't exit on error - we want Apache to start even if migrations fail
set +e

echo "🚀 Starting Laravel Bengkel application..."

# Wait a bit for services to be ready
sleep 3

# Test database connection (but don't fail if it doesn't work)
echo "⏳ Testing database connection..."
DB_CONNECTED=0

for i in {1..3}; do
    if php artisan db:show 2>/dev/null; then
        echo "✅ Database connected successfully!"
        DB_CONNECTED=1
        break
    else
        echo "⚠️  Database connection attempt $i/3 failed, retrying..."
        sleep 3
    fi
done

if [ $DB_CONNECTED -eq 0 ]; then
    echo "❌ Database connection failed after 3 attempts"
    echo "⚠️  Will start Apache anyway. Check database credentials!"
fi

# Only run migrations if database is connected
if [ $DB_CONNECTED -eq 1 ]; then
    echo "🔧 Running migrations..."
    php artisan migrate --force --no-interaction 2>&1 || echo "⚠️  Migration failed, continuing..."
else
    echo "⏭️  Skipping migrations (database not connected)"
fi

# Clear caches (suppress errors)
echo "📦 Clearing caches..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link 2>/dev/null || true

# Show application info
echo "📋 Application Info:"
php artisan --version 2>/dev/null || echo "Laravel (version check failed)"
echo "Environment: ${APP_ENV:-unknown}"
echo "URL: ${APP_URL:-unknown}"

# Configure Apache port dynamically
APACHE_PORT=${PORT:-10000}
echo "🔧 Configuring Apache to listen on port $APACHE_PORT..."

# Update Apache ports.conf
sed -i "s/Listen 10000/Listen $APACHE_PORT/g" /etc/apache2/ports.conf
sed -i "s/Listen 80/Listen $APACHE_PORT/g" /etc/apache2/ports.conf

# Update VirtualHost
sed -i "s/:10000/:$APACHE_PORT/g" /etc/apache2/sites-available/000-default.conf
sed -i "s/:80/:$APACHE_PORT/g" /etc/apache2/sites-available/000-default.conf

# CRITICAL: Start Apache (this MUST succeed)
echo "✅ Starting Apache on port $APACHE_PORT..."
set -e  # Exit on error from here
exec apache2-foreground

