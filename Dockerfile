FROM bitnami/laravel:latest

# Set working directory
WORKDIR /app

# Switch to root to install extensions and modify config
USER root

# 1. Install PostgreSQL system libraries (Required for the PHP extension to work)
RUN apt-get update && apt-get install -y libpq-dev

# 2. Enable the PostgreSQL PHP extensions in php.ini
# Bitnami ships with these extensions but comments them out by default.
# We use 'sed' to find the lines starting with ';extension=...' and remove the semicolon.
RUN sed -i 's/;extension=pdo_pgsql/extension=pdo_pgsql/' /opt/bitnami/php/etc/php.ini && \
    sed -i 's/;extension=pgsql/extension=pgsql/' /opt/bitnami/php/etc/php.ini

# Copy composer files first
COPY composer.json composer.lock ./

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the application code
COPY . .

# Fix permissions for the non-root user
RUN chown -R 1001:1001 /app

# Switch back to the non-root user
USER 1001

# Run the entrypoint script
ENTRYPOINT ["/app/docker-entrypoint.sh"]