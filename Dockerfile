# Dockerfile - Simplified version if the main one still fails
FROM composer:2.6 as composer-stage

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install \
    --no-scripts \
    --no-autoloader \
    --no-dev \
    --no-interaction

# Production stage
FROM php:8.2-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy vendor from composer stage
COPY --from=composer-stage /app/vendor ./vendor

# Copy application code
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize --no-dev

# Create directories and set permissions
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 755 storage bootstrap/cache

# Copy configs
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 8080

CMD ["/usr/local/bin/start.sh"]
