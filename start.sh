#!/bin/bash
set -e

echo "==> Waiting for database..."
until php artisan db:show --json > /dev/null 2>&1; do
    echo "    DB not ready, retrying in 3s..."
    sleep 3
done
echo "    DB is ready."

echo "==> Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear || true

echo "==> Caching config, routes and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Seeding database..."
php artisan db:seed --force

echo "==> Creating storage link..."
php artisan storage:link || true

echo "==> Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
