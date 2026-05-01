#!/usr/bin/env bash
set -uo pipefail

# ─── Detect docker compose command ───
if docker compose version > /dev/null 2>&1; then
  DC="docker compose"
elif docker-compose version > /dev/null 2>&1; then
  DC="docker-compose"
else
  echo "✗ Neither 'docker compose' nor 'docker-compose' found."
  exit 1
fi

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
DUMP_FILE="$APP_DIR/dump.sql"

# ─── Usage ───
if [ $# -lt 1 ]; then
  echo "Usage: $0 <site-url>"
  echo ""
  echo "  <site-url>  L'URL du site cible (ex: https://soma.tempo-hub.fr)"
  echo ""
  echo "Examples:"
  echo "  $0 https://soma.tempo-hub.fr"
  echo "  $0 http://localhost:8090"
  exit 1
fi

SITE_URL="$1"
# Retirer le slash final
SITE_URL="${SITE_URL%/}"

echo "╔══════════════════════════════════════╗"
echo "║  SOMA Avignon — Import BDD          ║"
echo "╚══════════════════════════════════════╝"
echo ""
echo "  URL cible: $SITE_URL"
echo "  Dump:      $DUMP_FILE"
echo ""

if [ ! -f "$DUMP_FILE" ]; then
  echo "✗ Fichier dump.sql introuvable"
  exit 1
fi

# ─── Wait for DB ───
echo "→ Attente de la base de données..."
for i in $(seq 1 20); do
  if $DC exec -T db mariadb -u soma -psomapass -e "SELECT 1" soma_wp > /dev/null 2>&1; then
    break
  fi
  sleep 2
done

# ─── Replace URLs and import ───
echo "→ Remplacement des URLs (localhost:8090 → $SITE_URL)..."
sed "s|http://localhost:8090|$SITE_URL|g" "$DUMP_FILE" > /tmp/soma-import.sql

echo "→ Import de la base de données..."
$DC exec -T db mariadb -u soma -psomapass soma_wp < /tmp/soma-import.sql

rm -f /tmp/soma-import.sql

# ─── Copy versioned uploads ───
if [ -d "$APP_DIR/wp-content/uploads" ]; then
  echo "→ Copie des uploads versionnés..."
  # Create target dirs and copy file by file for maximum compatibility
  $DC exec -T wordpress mkdir -p /var/www/html/wp-content/uploads/2026/04/
  for f in "$APP_DIR"/wp-content/uploads/2026/04/*.webp; do
    [ -f "$f" ] || continue
    $DC cp "$f" "wordpress:/var/www/html/wp-content/uploads/2026/04/$(basename "$f")"
  done
  $DC exec -T wordpress chown -R www-data:www-data /var/www/html/wp-content/uploads/
  echo "  ✓ $(ls "$APP_DIR"/wp-content/uploads/2026/04/*.webp 2>/dev/null | wc -l) fichiers copiés"
fi

# ─── Install WP-CLI and flush ───
echo "→ Flush du cache WordPress..."
$DC exec -T wordpress bash -c 'which wp > /dev/null 2>&1 || (curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp)'
$DC exec -T wordpress wp cache flush --allow-root 2>/dev/null || true
$DC exec -T wordpress wp rewrite flush --allow-root 2>/dev/null || true

echo ""
echo "✓ Base de données importée avec succès !"
echo "  Site: $SITE_URL"
echo ""
