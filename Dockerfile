# ==========================================
# STAGE 1: Frontend Build (Node.js)
# ==========================================
FROM node:20 AS frontend

WORKDIR /app

# Copy package files first to cache npm install
COPY package*.json vite.config.ts ./

# Install npm dependencies
RUN npm install

# Copy the rest of the source code (specifically resources/)
COPY . .

# Build the assets (compiles to /public/build)
RUN npm run build

# ==========================================
# STAGE 2: Backend Setup (PHP/Apache)
# ==========================================
FROM php:8.2-apache

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    unzip \
    git \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions for Postgres
RUN docker-php-ext-install pdo pdo_pgsql

# 3. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 4. Configure Apache (The Robust Way)
# Instead of using 'sed' to edit files, we write a fresh config file.
# This sets the DocumentRoot to /public AND enables .htaccess overrides.
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        Options Indexes FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# 5. Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 6. Copy Application Code
COPY . .

# 7. Copy Compiled Frontend Assets from Stage 1
# (Ensure 'frontend' matches the name in Stage 1: FROM node:20 as frontend)
COPY --from=frontend /app/public/build /var/www/html/public/build

# 8. Install Backend Dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 9. Fix Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Entrypoint
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]