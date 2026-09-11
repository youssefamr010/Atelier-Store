#!/bin/bash
# ==============================================================================
# ATELIER Auto-Sync Poller (GitHub -> AWS Auto Deploy)
# Checks if origin/main has new commits; if so, triggers deploy.sh
# Can be run via cron (e.g., every 2 minutes) or as a background service
# ==============================================================================
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_DIR"

git fetch origin main -q

LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse origin/main)

if [ "$LOCAL" != "$REMOTE" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] New commit detected on origin/main ($LOCAL -> $REMOTE). Initiating deployment..."
    bash "$PROJECT_DIR/deploy.sh" >> "$PROJECT_DIR/storage/logs/deploy.log" 2>&1
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deployment completed successfully."
fi
