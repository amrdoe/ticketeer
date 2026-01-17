#!/bin/bash
set -e

echo "🚀 Starting deployment..."

# 1. Cache configuration and routes for production speed
echo "🔥 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Run Database Migrations
# The --force flag is required to run migrations in production
echo "📦 Running migrations..."
php artisan migrate --force

# 3. Start the Server
# Render assigns a PORT env variable (default 10000). We must tell artisan to listen on it.
echo "🌍 Starting Server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT