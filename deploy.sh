#!/usr/bin/env bash
set -uo pipefail

# ─── Configuration ───
APP_DIR="$(cd "$(dirname "$0")" && pwd)"

# ─── Usage ───
usage() {
  echo "Usage: $0 <version>"
  echo ""
  echo "  <version>  Tag, branch or commit to deploy (e.g. v1.0.0, main, ada0d09)"
  echo ""
  echo "Examples:"
  echo "  $0 v1.2.0        # Deploy tag v1.2.0"
  echo "  $0 main          # Deploy latest main"
  echo "  $0 abc1234       # Deploy specific commit"
  exit 1
}

# ─── Validate args ───
if [ $# -lt 1 ]; then
  usage
fi

VERSION="$1"

echo "╔══════════════════════════════════════╗"
echo "║  SOMA Avignon — Deploy              ║"
echo "╚══════════════════════════════════════╝"
echo ""
echo "  Version:   $VERSION"
echo "  Directory: $APP_DIR"
echo ""

cd "$APP_DIR"

# ─── Fetch latest from GitHub ───
echo "→ Fetching latest from origin..."
git fetch origin --tags --force --prune || echo "  (fetch warning, continuing...)"

# ─── Reset local changes and checkout ───
echo "→ Checking out $VERSION..."
git reset --hard
git clean -fd
git checkout "$VERSION" || { echo "✗ Failed to checkout $VERSION"; exit 1; }

# ─── If on a branch, pull latest ───
if git symbolic-ref -q HEAD > /dev/null 2>&1; then
  echo "→ Pulling latest changes..."
  git pull origin "$VERSION" || true
fi

# ─── Rebuild and restart containers ───
echo "→ Building and restarting containers..."
docker compose down
docker compose build --no-cache
docker compose up -d

# ─── Verify ───
echo ""
echo "→ Waiting for containers to start..."
sleep 5

if docker compose ps | grep -qE "running|Up"; then
  echo ""
  echo "✓ Deploy complete — $VERSION is live"
  echo ""
  docker compose ps
else
  echo ""
  echo "✗ Container failed to start. Logs:"
  docker compose logs --tail=30
  exit 1
fi
