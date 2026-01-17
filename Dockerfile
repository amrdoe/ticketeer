# Use the official PHP image with Apache (a real web server)
FROM php:8.2-apache

# 1. Install system dependencies for the Postgres driver and Composer
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install the PHP extensions for PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# 3. Configure Apache DocumentRoot to point to Laravel's /public folder
# (Apache defaults to /var/www/html, but Laravel needs /var/www/html/public)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 4. Enable Apache mod_rewrite for Laravel routes
RUN a2enmod rewrite

# 5. Install Composer (Copy it directly from the official Composer image)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# 6. Copy application files
COPY . .

# 7. Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 8. Fix permissions (Apache runs as 'www-data')
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Use the entrypoint script
# Make sure your script is executable (chmod +x docker-entrypoint.sh)
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]