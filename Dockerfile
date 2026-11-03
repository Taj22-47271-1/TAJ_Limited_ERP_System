FROM php:8.3-apache

# Install system dependencies and PostgreSQL driver
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install pdo_pgsql pgsql bcmath \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configure Apache DocumentRoot and permissions
RUN printf '%s\n' \
    '<VirtualHost *:80>' \
    '    ServerAdmin webmaster@localhost' \
    '    DocumentRoot /var/www/html/public' \
    '    <Directory /var/www/html/public>' \
    '        Options -Indexes +FollowSymLinks' \
    '        AllowOverride All' \
    '        Require all granted' \
    '    </Directory>' \
    '    ErrorLog ${APACHE_LOG_DIR}/error.log' \
    '    CustomLog ${APACHE_LOG_DIR}/access.log combined' \
    '</VirtualHost>' \
    > /etc/apache2/sites-available/000-default.conf \
    && mkdir -p storage/framework/cache/data \
               storage/framework/sessions \
               storage/framework/views \
               storage/logs \
               bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copy entrypoint script and normalize line endings
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

ENV PORT=10000
ENV LOG_CHANNEL=stderr
EXPOSE 10000

CMD ["/usr/local/bin/docker-entrypoint.sh"]
