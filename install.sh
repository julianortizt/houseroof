#!/bin/bash
# ============================================================
# Costa Resin & Construction - Script de Instalación
# ============================================================
# Ejecutar desde la raíz del proyecto:
#   chmod +x install.sh && ./install.sh
# ============================================================

set -e

echo "🚀 Costa Resin & Construction - Instalación"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check PHP
if ! command -v php &> /dev/null; then
    echo -e "${RED}❌ PHP no encontrado. Instala PHP 8.2+ primero.${NC}"
    exit 1
fi

PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
echo -e "${GREEN}✅ PHP $PHP_VERSION detectado${NC}"

# Check Composer
if ! command -v composer &> /dev/null; then
    echo -e "${YELLOW}⚠️  Composer no encontrado. Instalando...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi
echo -e "${GREEN}✅ Composer detectado${NC}"

# Check Node.js
if ! command -v node &> /dev/null; then
    echo -e "${YELLOW}⚠️  Node.js no encontrado. Los assets no se compilarán.${NC}"
fi

echo ""
echo "📦 Instalando dependencias PHP..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo ""
echo "⚙️  Configurando entorno..."
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
    echo -e "${GREEN}✅ .env creado y APP_KEY generada${NC}"
else
    echo -e "${YELLOW}⚠️  .env ya existe, omitiendo${NC}"
fi

echo ""
echo "🗄️  Creando directorios de almacenamiento..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/app/public
mkdir -p storage/logs
mkdir -p bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo ""
echo "🔗 Creando enlace de almacenamiento..."
php artisan storage:link 2>/dev/null || true

echo ""
echo "📊 Configurando base de datos..."
echo -e "${YELLOW}⚠️  Asegúrate de que MySQL esté ejecutándose y la base de datos exista.${NC}"
echo -e "${YELLOW}   Edita .env con tus credenciales de BD antes de continuar.${NC}"
echo ""

read -p "¿Ejecutar migraciones y seeders ahora? (s/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Ss]$ ]]; then
    php artisan migrate:fresh --seed --force
    echo -e "${GREEN}✅ Migraciones y seeders completados${NC}"
else
    echo -e "${YELLOW}⚠️  Saltando migraciones. Ejecuta manualmente:${NC}"
    echo "   php artisan migrate:fresh --seed"
fi

echo ""
echo "🎨 Compilando assets..."
if command -v node &> /dev/null; then
    npm install
    npm run build
    echo -e "${GREEN}✅ Assets compilados${NC}"
else
    echo -e "${YELLOW}⚠️  Node.js no disponible. Los assets precompilados ya están en public/build/${NC}"
fi

echo ""
echo "============================================================"
echo -e "${GREEN}✅ ¡Instalación completada!${NC}"
echo "============================================================"
echo ""
echo "📌 Próximos pasos:"
echo "   1. Asegúrate de que MySQL esté corriendo"
echo "   2. php artisan migrate:fresh --seed (si no lo ejecutaste)"
echo "   3. php artisan serve"
echo ""
echo "🌐 URLs:"
echo "   • Sitio web:    http://localhost:8000"
echo "   • Panel admin:  http://localhost:8000/admin"
echo ""
echo "👤 Credenciales admin:"
echo "   • Email:    admin@TU_DOMINIO.com"
echo "   • Password: password"
echo ""
echo "📌 Para Hostinger (producción):"
echo "   1. Sube todos los archivos al servidor vía SSH/FTP"
echo "   2. Edita .env con credenciales reales"
echo "   3. php artisan migrate --force"
echo "   4. php artisan db:seed --force"
echo "   5. php artisan config:cache"
echo "   6. Añade cron: * * * * * cd /path/to/app && php artisan schedule:run"
echo ""
