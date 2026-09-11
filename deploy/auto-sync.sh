#!/bin/bash
# ==============================================================================
# ATELIER Auto-Sync Script for AWS EC2
# Checks GitHub main branch; if new commits exist, runs deploy.sh automatically.
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/.." && pwd)"
cd "$PROJECT_DIR"

LOG_FILE="$PROJECT_DIR/storage/logs/auto-deploy.log"
mkdir -p "$(dirname "$LOG_FILE")"

# Fetch latest commits quietly from GitHub
git fetch origin main > /dev/null 2>&1 || exit 0

LOCAL=$(git rev-parse HEAD 2>/dev/null || echo "")
REMOTE=$(git rev-parse origin/main 2>/dev/null || echo "")

if [ -n "$REMOTE" ] && [ "$LOCAL" != "$REMOTE" ]; then
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] New update detected on GitHub ($LOCAL -> $REMOTE). Starting auto-deploy..." >> "$LOG_FILE"
    bash "$PROJECT_DIR/deploy.sh" >> "$LOG_FILE" 2>&1
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] Auto-deploy finished successfully." >> "$LOG_FILE"
fi
