#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# 1. Cache configuration
echo "🔥 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Run Database Migrations
echo "📦 Running migrations..."
php artisan migrate --force

# 3. Start Apache
# The 'exec' command replaces the shell with the Apache process, 
# ensuring it receives signals correctly.
echo "🌍 Starting Apache..."
exec apache2-foreground