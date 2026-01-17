FROM bitnami/laravel:latest

# Set working directory
WORKDIR /app

# Switch to root
USER root

# 1. Use 'install_packages' instead of apt-get
# This installs the PostgreSQL system library required for the driver
RUN install_packages libpq-dev

# 2. Enable the PostgreSQL PHP extensions
RUN sed -i 's/;extension=pdo_pgsql/extension=pdo_pgsql/' /opt/bitnami/php/etc/php.ini && \
    sed -i 's/;extension=pgsql/extension=pgsql/' /opt/bitnami/php/etc/php.ini

# Copy composer files
COPY composer.json composer.lock ./

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application files
COPY . .

# Fix permissions
RUN chown -R 1001:1001 /app

# Switch back to non-root user
USER 1001

# Entrypoint
ENTRYPOINT ["/app/docker-entrypoint.sh"]