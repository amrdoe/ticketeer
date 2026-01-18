# ==========================================
# STAGE 1: Frontend Build (Node.js)
# ==========================================
FROM node:20 as frontend

WORKDIR /app

# Copy package files first to cache npm install
COPY package*.json vite.config.js ./

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

# 3. Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# 4. Enable mod_rewrite
RUN a2enmod rewrite

# 5. Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 6. Copy Application Code (PHP files)
COPY . .

# 7. Copy Compiled Frontend Assets from Stage 1
# This puts the css/js files exactly where Laravel expects them
COPY --from=frontend /app/public/build /var/www/html/public/build

# 8. Install Backend Dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# 9. Fix Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 10. Entrypoint
ENTRYPOINT ["/var/www/html/docker-entrypoint.sh"]