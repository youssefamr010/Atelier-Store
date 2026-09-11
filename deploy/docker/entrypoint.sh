#!/bin/sh

# Ensure storage and bootstrap directories exist
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache

if [ -d "public/media" ]; then
  mkdir -p storage/app/public/media
  cp -rn public/media/* storage/app/public/media/ 2>/dev/null || true
fi

# Ensure permissions without failing the container
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "Running database migrations..."
  php artisan migrate --force || echo "Migration skipped or database waiting"

  echo "Creating storage symlink..."
  php artisan storage:link || true

  echo "Optimizing cache..."
  php artisan optimize:clear || true
  php artisan optimize || true
fi

# Always execute the target process (php-fpm, worker, scheduler)
exec "$@"
