#!/bin/bash
# ==============================================================================
# Setup Auto-Deploy Cron on AWS EC2
# Usage: bash scripts/setup-auto-deploy-cron.sh
# ==============================================================================
set -e

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
POLL_SCRIPT="$PROJECT_DIR/scripts/poll-deploy.sh"
chmod +x "$PROJECT_DIR/deploy.sh" "$POLL_SCRIPT"

# Ensure storage/logs exists
mkdir -p "$PROJECT_DIR/storage/logs"

# Check if cron already exists
CRON_CMD="*/2 * * * * bash $POLL_SCRIPT >/dev/null 2>&1"

(crontab -l 2>/dev/null | grep -Fv "$POLL_SCRIPT" ; echo "$CRON_CMD") | crontab -

echo "=============================================================================="
echo "Auto-Deploy cron installed successfully!"
echo "The server will now check GitHub every 2 minutes for new commits on 'main'"
echo "and automatically run deploy.sh when updates are found."
echo "Log file: $PROJECT_DIR/storage/logs/deploy.log"
echo "=============================================================================="
