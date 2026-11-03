#!/bin/sh
set -e

PORT="${PORT:-10000}"

# Configure Apache listening port
sed -i "s/Listen [0-9]*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Ensure Laravel storage and cache directories exist and are writable
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink if not already linked
php artisan storage:link --no-interaction || true

# Run database migrations safely if requested
if [ "${RUN_MIGRATIONS:-false}" = "true" ] || [ "${AUTO_MIGRATE:-false}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --isolated || echo "Migration encountered an issue, continuing startup."
fi

exec apache2-foreground