FROM bitnami/laravel:latest

# Set working directory
WORKDIR /app

# Switch to root to install system dependencies
USER root

# 1. Install PostgreSQL client libraries (Photon OS version)
# 'libpq-dev' is for Debian; 'postgresql-libs' is for Photon/RPM
RUN install_packages postgresql-libs

# 2. Enable the PostgreSQL PHP extensions in php.ini
# We uncomment the lines for pdo_pgsql and pgsql
RUN sed -i 's/;extension=pdo_pgsql/extension=pdo_pgsql/' /opt/bitnami/php/etc/php.ini && \
    sed -i 's/;extension=pgsql/extension=pgsql/' /opt/bitnami/php/etc/php.ini

# Copy composer files
COPY composer.json composer.lock ./

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the application code
COPY . .

# Fix permissions for the non-root user '1001'
RUN chown -R 1001:1001 /app

# Switch back to the non-root user
USER 1001

# Run the entrypoint script
ENTRYPOINT ["/app/docker-entrypoint.sh"]