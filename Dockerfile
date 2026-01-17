# Use the requested Bitnami base image
FROM bitnami/laravel:latest

# Set working directory
WORKDIR /app

# Switch to root temporarily to install system dependencies if needed
# (Bitnami images run as a non-root user '1001' by default)
USER root

# Copy composer files first to leverage Docker cache
COPY composer.json composer.lock ./

# Install Composer dependencies
# --no-dev: Exclude dev dependencies (phpunit, mockery, etc.)
# --no-scripts: Prevent auto-scripts from running (requires DB connection which isn't ready yet)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy the rest of the application code
COPY . .

# Ensure the non-root user owns the application files
RUN chown -R 1001:1001 /app

# Switch back to the standard Bitnami non-root user
USER 1001

# Run the entrypoint script we created
ENTRYPOINT ["/app/docker-entrypoint.sh"]