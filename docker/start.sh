set -e

echo "🚀 Starting Laravel Transaction API..."


echo "⏳ Waiting for database connection..."
max_attempts=30
attempt=1

while [ $attempt -le $max_attempts ]; do
    if php artisan tinker --execute="DB::connection()->getPdo(); echo 'Connected!';" 2>/dev/null; then
        echo "✅ Database connected!"
        break
    fi

    echo "🔄 Database not ready, attempt $attempt/$max_attempts..."
    sleep 2
    attempt=$((attempt + 1))
done

if [ $attempt -gt $max_attempts ]; then
    echo "❌ Failed to connect to database after $max_attempts attempts"
    exit 1
fi

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Clear any existing cache
echo "🧹 Clearing Laravel caches..."
php artisan config:clear
# php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force --no-interaction

# Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link || true

# Cache configuration for production
echo "⚡ Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set final permissions
echo "🔐 Setting final permissions..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create log files
mkdir -p /var/log/nginx
touch /var/log/nginx/access.log /var/log/nginx/error.log /var/log/php-fpm.log

echo "✅ Laravel application setup completed!"
echo "🌐 Starting web services..."

# Start supervisor which manages nginx, php-fpm, and queue workers
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
