#!/usr/bin/env bash
# build.sh

set -o errexit  # exit on error

echo "Starting build process..."

# Update package lists
echo "Updating system packages..."

# Install PHP extensions if needed
echo "Checking PHP extensions..."

# Install Node.js dependencies if you have any frontend assets
if [ -f "package.json" ]; then
    echo "Installing Node.js dependencies..."
    npm install
    npm run build
fi

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Clear any existing cached config
echo "Clearing Laravel caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan view:clear || true
php artisan route:clear || true

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --no-interaction
fi

# Cache config and routes for better performance
echo "Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create necessary directories
echo "Creating storage directories..."
mkdir -p storage/logs
mkdir -p storage/app/public
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "Build completed successfully!"
