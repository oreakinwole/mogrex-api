#!/usr/bin/env bash
# start.sh

set -o errexit  # exit on error

echo "Starting Laravel application..."

# Wait for database to be ready
echo "Waiting for database connection..."
until php artisan tinker --execute="DB::connection()->getPdo(); echo 'Database connected!';" 2>/dev/null; do
    echo "Database not ready, waiting 2 seconds..."
    sleep 2
done

echo "Database connection established!"

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Create storage symlink if it doesn't exist
echo "Creating storage symlink..."
php artisan storage:link || true

# Clear and cache everything for production
echo "Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper file permissions
chmod -R 755 storage bootstrap/cache

echo "Laravel application started successfully!"

# Start the PHP built-in server
echo "Starting PHP server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000} --no-reload
