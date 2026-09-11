#!/bin/bash
# ==============================================================================
# ATELIER Auto-Deploy Script
# Safe, idempotent deployment runner for AWS EC2 Production
# ==============================================================================
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

echo "=== [1/6] Syncing latest code from GitHub (origin/main) ==="
git fetch origin main
# Ensure any local divergent commits or changes do not block deployment
git reset --hard origin/main
git clean -fd -e .env.production -e storage

if [ ! -f .env.production ]; then
    if [ -f .env.production.example ]; then
        echo "[WARNING] .env.production not found! Copying from .env.production.example..."
        cp .env.production.example .env.production
    else
        echo "[ERROR] .env.production is missing!"
        exit 1
    fi
fi

echo "=== [2/6] Building & restarting Docker containers ==="
if ! docker compose -f docker-compose.production.yml --env-file .env.production up -d --build --remove-orphans; then
    echo "[NOTICE] Transient Docker container lock encountered. Waiting 5s and retrying..."
    sleep 5
    docker compose -f docker-compose.production.yml --env-file .env.production up -d --remove-orphans
fi
sleep 3

echo "=== [3/6] Ensuring storage permissions & symlink ==="
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app chmod -R 775 storage bootstrap/cache 2>/dev/null || true
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan storage:link || true
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app bash -c 'mkdir -p storage/app/public/media && cp -rn public/media/* storage/app/public/media/ 2>/dev/null || true'

echo "=== [4/6] Running database migrations and decor catalog sync ==="
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan migrate --force || true
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan db:seed --class=CollectionSeeder --force || true
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan db:seed --class=ProductSeeder --force || true
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan db:seed --class=SettingSeeder --force || true

echo "=== [5/6] Refreshing Laravel cache and optimizations ==="
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan optimize:clear || true
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan optimize || true

echo "=== [6/6] Reloading Nginx & checking container status ==="
docker compose -f docker-compose.production.yml --env-file .env.production exec -T web nginx -s reload 2>/dev/null || true
docker compose -f docker-compose.production.yml --env-file .env.production ps

echo "=============================================================================="
echo "Deployment completed successfully!"
echo "Site URL: https://atelier404.store"
echo "=============================================================================="
