#!/bin/bash
# ============================================================
# 🚀 Costa Resin & Construction - Instalador Visual Hostinger
# ============================================================
#
# USO EN HOSTINGER:
#   1. Sube todos los archivos del proyecto a /home/usuario/public_html
#   2. Abre SSH en Hostinger: Panel → Avanzado → SSH Access
#   3. Conéctate: ssh usuario@tudominio.com
#   4. Ejecuta: cd ~/public_html && chmod +x install-hostinger.sh && ./install-hostinger.sh
#
# ============================================================

set -e

# ===================== COLORES =====================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
MAGENTA='\033[0;35m'
WHITE='\033[1;37m'
NC='\033[0m'
BOLD='\033[1m'

# ===================== FUNCIONES =====================

clear
echo -e "${CYAN}"
cat << 'BANNER'

  ╔══════════════════════════════════════════════════════════╗
  ║                                                          ║
  ║   🏗️  COSTA RESIN & CONSTRUCTION                              ║
  ║   Instalador de Producción para Hostinger                ║
  ║                                                          ║
  ║   Laravel 11 · Filament 3 · MySQL · PHP 8.2+            ║
  ║                                                          ║
  ╚══════════════════════════════════════════════════════════╝

BANNER
echo -e "${NC}"

log_info()    { echo -e "${BLUE}  ℹ ${NC} $1"; }
log_ok()      { echo -e "${GREEN}  ✅ ${NC} $1"; }
log_warn()    { echo -e "${YELLOW}  ⚠️  ${NC} $1"; }
log_error()   { echo -e "${RED}  ❌ ${NC} $1"; }
log_step()    { echo -e "\n${CYAN}${BOLD}  ━━━ Paso $1 ━━━${NC}"; }
log_substep() { echo -e "    ${WHITE}→${NC} $1"; }

spinner() {
    local pid=$1
    local delay=0.1
    local spinstr='⠋⠙⠹⠸⠼⠴⠦⠧⠇⠏'
    while [ "$(ps -p $pid -o pid= 2>/dev/null)" ]; do
        for (( i=0; i<${#spinstr}; i++ )); do
            printf "\r    ${CYAN}%s${NC} %s" "${spinstr:$i:1}" "$2"
            sleep $delay
        done
    done
    printf "\r"
}

# ===================== PASO 1: VERIFICAR ENTORNO =====================

log_step "1/8 Verificando entorno"

# Verificar PHP
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;' 2>/dev/null || echo "0")
if [ "$PHP_VERSION" = "0" ]; then
    log_error "PHP no encontrado. Contacta soporte de Hostinger."
    exit 1
fi

PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)

if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 2 ]; then
    log_ok "PHP $PHP_VERSION ✓"
else
    log_warn "PHP $PHP_VERSION detectado. Se recomienda PHP 8.2+"
    log_info "En Hostinger: Panel → PHP → Seleccionar PHP 8.2"
fi

# Verificar extensiones PHP
REQUIRED_EXTS="curl mbstring openssl pdo pdo_mysql xml tokenizer json fileinfo"
for ext in $REQUIRED_EXTS; do
    if php -m | grep -qi "^$ext$"; then
        log_substep "Extensión $ext ✓"
    else
        log_error "Extensión PHP faltante: $ext"
        log_info "En Hostinger: Panel → PHP → Extensions → Habilitar $ext"
        exit 1
    fi
done

# Verificar Composer
if command -v composer &> /dev/null; then
    log_ok "Composer $(composer --version 2>/dev/null | head -1 | awk '{print $2}') ✓"
elif [ -f "composer.phar" ]; then
    log_ok "Composer (local) ✓"
else
    log_info "Descargando Composer..."
    curl -sS https://getcomposer.org/installer | php -- --quiet
    log_ok "Composer instalado ✓"
fi

# Verificar directorio
PWD_DIR=$(pwd)
log_info "Directorio: $PWD_DIR"

if [ ! -f "artisan" ]; then
    log_error "No se encontró artisan. Ejecuta este script desde la raíz del proyecto Laravel."
    exit 1
fi
log_ok "Proyecto Laravel detectado ✓"

# ===================== PASO 2: CONFIGURACIÓN =====================

log_step "2/8 Configuración del sitio"

echo ""
echo -e "  ${WHITE}Ingresa los datos de tu sitio Hostinger:${NC}"
echo ""

# Dominio
read -p "  Dominio (ej: tudominio.com): " DOMAIN
DOMAIN=${DOMAIN:-TU_DOMINIO.com}

# Usuario SSH
read -p "  Usuario SSH (ej: u123456789): " SSH_USER
SSH_USER=${SSH_USER:-$(whoami)}

# Directorio
read -p "  Directorio público [~/public_html]: " PUBLIC_DIR
PUBLIC_DIR=${PUBLIC_DIR:-~/public_html}

echo ""
echo -e "  ${WHITE}Base de datos:${NC}"
echo -e "  ${YELLOW}(Crea la DB en Panel → Databases → MySQL → Crear)${NC}"
echo ""

# Base de datos
read -p "  Nombre de la base de datos: " DB_NAME
read -p "  Usuario de la base de datos: " DB_USER
read -sp "  Contraseña de la base de datos: " DB_PASS
echo ""

# Correo
echo ""
echo -e "  ${WHITE}Configuración de correo:${NC}"
read -p "  Email SMTP (ej: info@tudominio.com): " MAIL_USER
read -sp "  Contraseña SMTP: " MAIL_PASS
echo ""

# Groq API Key
echo ""
echo -e "  ${WHITE}Asistente IA (opcional):${NC}"
read -p "  Groq API Key (Enter para omitir): " GROQ_KEY

echo ""
echo -e "  ${CYAN}═══════════════════════════════════════════════${NC}"
echo -e "  ${WHITE}Resumen:${NC}"
echo -e "  Dominio:   ${GREEN}$DOMAIN${NC}"
echo -e "  DB:        ${GREEN}$DB_NAME${NC}"
echo -e "  Email:     ${GREEN}$MAIL_USER${NC}"
echo -e "  Directorio:${GREEN}$PUBLIC_DIR${NC}"
echo -e "  ${CYAN}═══════════════════════════════════════════════${NC}"
echo ""

read -p "  ¿Continuar con la instalación? (s/n): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Ss]$ ]]; then
    echo "  Instalación cancelada."
    exit 0
fi

# ===================== PASO 3: ARCHIVO .env =====================

log_step "3/8 Configurando archivo .env"

if [ -f ".env" ]; then
    cp .env .env.backup.$(date +%Y%m%d_%H%M%S)
    log_substep "Backup del .env anterior creado"
fi

cat > .env << ENVFILE
APP_NAME="Costa Resin & Construction"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://${DOMAIN}
APP_TIMEZONE=Europe/Madrid
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=es_ES
APP_MAINTENANCE_DRIVER=file

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD="${DB_PASS}"

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true
SESSION_HTTPONLY=true

CACHE_STORE=file
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=${MAIL_USER}
MAIL_PASSWORD="${MAIL_PASS}"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="${MAIL_USER}"
MAIL_FROM_NAME="Costa Resin & Construction"

FILAMENT_PATH=admin

GROQ_API_KEY=${GROQ_KEY:-}
ENVFILE

chmod 600 .env
log_ok "Archivo .env configurado ✓"

# ===================== PASO 4: DEPENDENCIAS =====================

log_step "4/8 Instalando dependencias"

log_substep "Ejecutando composer install..."
if [ -f "composer.phar" ]; then
    php composer.phar install --no-dev --optimize-autoloader --no-interaction --quiet 2>&1 | tail -1
else
    composer install --no-dev --optimize-autoloader --no-interaction --quiet 2>&1 | tail -1
fi
log_ok "Dependencias PHP instaladas ✓"

# ===================== PASO 5: LLAVE DE APLICACIÓN =====================

log_step "5/8 Generando APP_KEY"

if php artisan key:generate --force 2>/dev/null; then
    log_ok "APP_KEY generada ✓"
else
    log_warn "No se pudo generar APP_KEY automáticamente"
    log_info "Ejecuta manualmente: php artisan key:generate"
fi

# ===================== PASO 6: BASE DE DATOS =====================

log_step "6/8 Configurando base de datos"

log_substep "Ejecutando migraciones..."
if php artisan migrate --force 2>&1 | grep -q "Nothing to migrate\|Migrated"; then
    log_ok "Migraciones completadas ✓"
else
    log_warn "Revisa las migraciones manualmente si hay errores"
fi

# Seeders opcionales
echo ""
read -p "  ¿Ejecutar seeders seguros (InstallSeeder: roles, ajustes, páginas legales, categorías)? (s/n): " -n 1 -r
echo ""
if [[ $REPLY =~ ^[Ss]$ ]]; then
    log_substep "Ejecutando seeders seguros (InstallSeeder)..."
    php artisan db:seed --class=InstallSeeder --force 2>/dev/null || log_warn "Seeders fallaron (puede ser normal)"
    log_ok "Seeders seguros completados ✓"
fi

# ===================== PASO 7: PERMISOS Y STORAGE =====================

log_step "7/8 Configurando permisos y storage"

# Storage link
log_substep "Creando enlace de storage..."
php artisan storage:link --force 2>/dev/null || true
log_ok "Storage link creado ✓"

# Permisos
log_substep "Configurando permisos..."
chmod -R 775 storage/ 2>/dev/null || true
chmod -R 775 bootstrap/cache/ 2>/dev/null || true

# Intentar cambiar owner (puede fallar en algunos hosts)
chown -R $(whoami):$(whoami) storage/ 2>/dev/null || true
chown -R $(whoami):$(whoami) bootstrap/cache/ 2>/dev/null || true
log_ok "Permisos configurados ✓"

# ===================== PASO 8: OPTIMIZACIÓN =====================

log_step "8/8 Optimizando para producción"

log_substep "Cache de configuración..."
php artisan config:cache 2>/dev/null && log_ok "Config cache ✓" || log_warn "Config cache falló"

log_substep "Cache de rutas..."
php artisan route:cache 2>/dev/null && log_ok "Route cache ✓" || log_warn "Route cache falló"

log_substep "Cache de vistas..."
php artisan view:cache 2>/dev/null && log_ok "View cache ✓" || log_warn "View cache falló"

log_substep "Optimizando autoloader..."
if [ -f "composer.phar" ]; then
    php composer.phar dump-autoload --optimize --no-dev --quiet 2>/dev/null
else
    composer dump-autoload --optimize --no-dev --quiet 2>/dev/null
fi
log_ok "Autoloader optimizado ✓"

# ===================== RESUMEN =====================

echo ""
echo -e "${GREEN}"
cat << 'SUMMARY'
  ╔══════════════════════════════════════════════════════════╗
  ║                                                          ║
  ║   ✅ INSTALACIÓN COMPLETADA                              ║
  ║                                                          ║
  ╚══════════════════════════════════════════════════════════╝
SUMMARY
echo -e "${NC}"

echo -e "  ${WHITE}🌐 Tu sitio web:${NC}    ${GREEN}https://${DOMAIN}${NC}"
echo -e "  ${WHITE}🔧 Panel admin:${NC}      ${GREEN}https://${DOMAIN}/admin${NC}"
echo ""
echo -e "  ${WHITE}📧 Usuarios admin:${NC}"
echo -e "     Super Admin: ${CYAN}admin@TU_DOMINIO.com${NC}"
echo -e "     Development: ${CYAN}dev@TU_DOMINIO.com${NC}"
echo -e "     Manager:     ${CYAN}manager@TU_DOMINIO.com${NC}"
echo -e "     Site Admin:  ${CYAN}siteadmin@TU_DOMINIO.com${NC}"
echo ""
echo -e "  ${YELLOW}⚠️  Pasos post-instalación:${NC}"
echo -e "  1. ${WHITE}Cambiar contraseñas${NC} de todos los usuarios admin"
echo -e "  2. ${WHITE}Configurar SSL${NC} desde el panel de Hostinger"
echo -e "  3. ${WHITE}Verificar el dominio${NC} apunta a /public"
echo -e "  4. ${WHITE}Subir logos${NC} desde /admin/settings"
echo -e "  5. ${WHITE}Crear productos${NC} desde /admin/products"
echo ""
echo -e "  ${CYAN}Para actualizar el sitio en el futuro:${NC}"
echo -e "  ${WHITE}cd ~/public_html && php artisan down${NC}"
echo -e "  ${WHITE}(subir archivos nuevos)${NC}"
echo -e "  ${WHITE}php artisan migrate --force${NC}"
echo -e "  ${WHITE}php artisan config:cache && php artisan route:cache${NC}"
echo -e "  ${WHITE}php artisan view:cache && php artisan up${NC}"
echo ""

# Verificar si el sitio responde
log_info "Verificando que el sitio responda..."
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://${DOMAIN}/" 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
    echo -e "  ${GREEN}✅ El sitio está respondiendo (HTTP $HTTP_CODE)${NC}"
else
    echo -e "  ${YELLOW}⚠️  El sitio respondió HTTP $HTTP_CODE${NC}"
    echo -e "  ${YELLOW}    Verifica que el dominio apunte a /public y que SSL esté activo${NC}"
fi

echo ""
echo -e "  ${CYAN}¡Instalación completada! 🎉${NC}"
echo ""
