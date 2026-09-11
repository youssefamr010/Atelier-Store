#!/bin/bash
###############################################################################
#  ATELIER 404 – One-Click Server Setup for Oracle Cloud Free Tier
#  Run this script on the Oracle Cloud VM AFTER uploading the project files.
#
#  Usage:
#    chmod +x setup-server.sh
#    sudo ./setup-server.sh
###############################################################################
set -euo pipefail

echo "========================================================"
echo "  ATELIER 404 – Oracle Cloud Server Setup"
echo "  Domain: atelier404.store"
echo "========================================================"
echo ""

# ─── 1. System Update ────────────────────────────────────────────────────────
echo "[1/7] Updating system packages..."
apt-get update -y && apt-get upgrade -y

# ─── 2. Install Docker ───────────────────────────────────────────────────────
echo "[2/7] Installing Docker..."
if ! command -v docker &> /dev/null; then
    curl -fsSL https://get.docker.com | sh
    systemctl enable docker
    systemctl start docker
    usermod -aG docker ubuntu
    echo "  ✅ Docker installed"
else
    echo "  ✅ Docker already installed"
fi

if ! docker compose version &> /dev/null; then
    echo "  Installing Docker Compose plugin..."
    apt-get install -y docker-compose-plugin
fi
echo "  Docker Compose version: $(docker compose version --short)"

# ─── 3. Open Firewall (iptables for Oracle Cloud) ────────────────────────────
echo "[3/7] Configuring firewall rules..."
iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT
netfilter-persistent save 2>/dev/null || iptables-save > /etc/iptables/rules.v4
echo "  ✅ Ports 80 and 443 opened"

# ─── 4. Check Project Directory ──────────────────────────────────────────────
echo "[4/7] Setting up project directory..."
PROJECT_DIR="/home/ubuntu/atelier"

if [ ! -d "$PROJECT_DIR" ]; then
    echo "  ⚠️  Project directory not found at $PROJECT_DIR"
    echo "  Please upload your project files first, then re-run this script."
    exit 1
fi

cd "$PROJECT_DIR"
echo "  ✅ Project found at $PROJECT_DIR"

# ─── 5. Generate Secure Passwords & Create .env.production ───────────────────
echo "[5/7] Creating production environment file..."

DB_PASSWORD=$(openssl rand -base64 24 | tr -d '/+=' | head -c 32)
DB_ROOT_PASSWORD=$(openssl rand -base64 24 | tr -d '/+=' | head -c 32)

if [ ! -f ".env.production" ]; then
    cp .env.production.example .env.production
    sed -i "s/CHANGE_THIS_TO_A_LONG_RANDOM_PASSWORD/$DB_PASSWORD/" .env.production
    sed -i "s/CHANGE_THIS_TO_ANOTHER_LONG_RANDOM_PASSWORD/$DB_ROOT_PASSWORD/" .env.production
    echo "  ✅ .env.production created with secure passwords"
    echo ""
    echo "  ╔══════════════════════════════════════════════════════╗"
    echo "  ║  SAVE THESE PASSWORDS SOMEWHERE SAFE!               ║"
    echo "  ║  DB User Password: $DB_PASSWORD"
    echo "  ║  DB Root Password: $DB_ROOT_PASSWORD"
    echo "  ╚══════════════════════════════════════════════════════╝"
    echo ""
else
    echo "  ✅ .env.production already exists, skipping"
fi

# ─── 6. Fix Line Endings & Permissions ───────────────────────────────────────
echo "[6/7] Fixing line endings and permissions..."
find . -name "*.sh" -exec sed -i 's/\r$//' {} +
find . -name "Caddyfile" -exec sed -i 's/\r$//' {} +
find . -name "*.conf" -exec sed -i 's/\r$//' {} +
find . -name "*.ini" -exec sed -i 's/\r$//' {} +
find . -name "Dockerfile" -exec sed -i 's/\r$//' {} +
find . -name "docker-compose*" -exec sed -i 's/\r$//' {} +
find . -name ".env*" -exec sed -i 's/\r$//' {} +
chmod +x deploy/docker/entrypoint.sh
echo "  ✅ Line endings fixed, entrypoint executable"

# ─── 7. Build and Launch ─────────────────────────────────────────────────────
echo "[7/7] Building and launching all services..."
echo "  This may take 5-10 minutes on first build..."

docker compose -f docker-compose.production.yml --env-file .env.production up -d --build

# Generate APP_KEY if empty
CURRENT_KEY=$(grep "^APP_KEY=" .env.production | cut -d= -f2)
if [ -z "$CURRENT_KEY" ] || [ "$CURRENT_KEY" = "" ]; then
    echo "  Generating APP_KEY..."
    sleep 15  # Wait for app container to be ready
    APP_KEY=$(docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan key:generate --show)
    sed -i "s|^APP_KEY=.*|APP_KEY=$APP_KEY|" .env.production
    docker compose -f docker-compose.production.yml --env-file .env.production down
    docker compose -f docker-compose.production.yml --env-file .env.production up -d
    echo "  ✅ APP_KEY set and services restarted"
fi

echo ""
echo "========================================================"
echo "  ✅ DEPLOYMENT COMPLETE!"
echo "========================================================"
echo ""
echo "  🌐 Site will be live at: https://atelier404.store"
echo "     (after DNS propagation + Caddy SSL certificate)"
echo ""
echo "  📋 Useful commands:"
echo "     Status:   docker compose -f docker-compose.production.yml --env-file .env.production ps"
echo "     Logs:     docker compose -f docker-compose.production.yml --env-file .env.production logs -f"
echo "     Restart:  docker compose -f docker-compose.production.yml --env-file .env.production restart"
echo "     Stop:     docker compose -f docker-compose.production.yml --env-file .env.production down"
echo ""
