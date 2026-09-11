#!/bin/bash
# ==============================================================================
# Installer for ATELIER 1-Minute Auto-Deploy Cron on AWS EC2
# ==============================================================================
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SYNC_SCRIPT="$PROJECT_DIR/deploy/auto-sync.sh"
chmod +x "$SYNC_SCRIPT"
chmod +x "$PROJECT_DIR/deploy.sh"

CRON_JOB="* * * * * bash $SYNC_SCRIPT > /dev/null 2>&1"

# Check if cron job already exists
if crontab -l 2>/dev/null | grep -Fq "$SYNC_SCRIPT"; then
    echo "[OK] Auto-deploy cron job is already installed and running!"
else
    (crontab -l 2>/dev/null; echo "$CRON_JOB") | crontab -
    echo "[SUCCESS] Auto-deploy cron job installed successfully!"
    echo "AWS EC2 will now check GitHub every minute and auto-deploy any new push."
fi

echo "Current crontab:"
crontab -l
