#!/bin/bash
# ============================================================
# ⚡ Deploy Rápido - Solo actualización de código
# ============================================================
# Para actualizaciones menores (cambios en código, no DB)
# ============================================================

set -e

REMOTE_HOST="${DEPLOY_HOST:-tudominio.com}"
REMOTE_USER="${DEPLOY_USER:-u123456789}"
REMOTE_DIR="${DEPLOY_DIR:-/home/u123456789/public_html}"
SSH_PORT=22

echo "⚡ Deploy rápido a ${REMOTE_HOST}..."

# Build local
echo "📦 Build local..."
composer install --no-dev --optimize-autoloader --no-interaction 2>/dev/null
npm run build 2>/dev/null || true

# Sync
echo "🔄 Sincronizando archivos..."
rsync -avz --delete \
    -e "ssh -p ${SSH_PORT}" \
    --exclude='.git' \
    --exclude='node_modules' \
    --exclude='.env' \
    --exclude='storage/' \
    --exclude='bootstrap/cache/*.php' \
    --exclude='tests' \
    ./ \
    ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/

# Remote optimize
echo "⚙️ Optimizando..."
ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
    cd ${REMOTE_DIR}
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo '✅ Deploy completado'
"

echo "✅ ¡Listo! https://${REMOTE_HOST}"
