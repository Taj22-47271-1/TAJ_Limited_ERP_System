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
         /var/www/html/storage/app/public/products \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Ensure public storage symlink is cleanly created in Linux
rm -rf /var/www/html/public/storage
php artisan storage:link --no-interaction || true

# Automatically run database migrations and seed system defaults
if [ "${SKIP_MIGRATIONS:-false}" != "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --isolated || echo "WARNING: Migration failed or database not reachable. Continuing startup."

    echo "Seeding system roles and settings..."
    php artisan db:seed --force || echo "WARNING: Seeding skipped or encountered an issue."
fi

exec apache2-foreground