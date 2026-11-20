#!/bin/bash
# ============================================================
# 🚀 Costa Resin & Construction - Script de Despliegue Hostinger
# ============================================================
#
# USO:
#   chmod +x deploy.sh
#   ./deploy.sh
#
# PREREQUISITOS en Hostinger:
#   - SSH habilitado en el panel de Hostinger
#   - PHP 8.2+ disponible
#   - Composer instalado (o acceso a php composer.phar)
#   - MySQL con base de datos creada
# ============================================================

set -e

# ===================== CONFIGURACIÓN =====================
# Edita estas variables según tu servidor

REMOTE_HOST="TU_DOMINIO.com"        # Sin dominio aún (sistema base): define el dominio antes de desplegar
REMOTE_USER="u123456789"              # Usuario SSH de Hostinger
REMOTE_DIR="/home/u123456789/public_html"  # Directorio raíz del sitio
SSH_PORT=22
LOCAL_PROJECT="./"                     # Directorio local del proyecto

# Base de datos
DB_NAME="u123456789_costaresin"
DB_USER="u123456789_admin"
DB_PASS="TU_CONTRASENA_DB"
DB_HOST="127.0.0.1"

# Git (opcional - si usas repositorio)
GIT_REPO="git@github.com:tu-usuario/costaresin-website.git"
GIT_BRANCH="main"

# ===================== COLORES =====================
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# ===================== FUNCIONES =====================

print_banner() {
    echo -e "${CYAN}"
    echo "╔══════════════════════════════════════════════════════╗"
    echo "║   🚀 Costa Resin & Construction - Deploy Hostinger                 ║"
    echo "║   Laravel 11 + Filament 3 + MySQL                  ║"
    echo "╚══════════════════════════════════════════════════════╝"
    echo -e "${NC}"
}

log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

check_local_requirements() {
    log_info "Verificando requisitos locales..."

    if ! command -v php &> /dev/null; then
        log_error "PHP no encontrado localmente"
        exit 1
    fi

    if ! command -v composer &> /dev/null; then
        log_error "Composer no encontrado localmente"
        exit 1
    fi

    if ! command -v ssh &> /dev/null; then
        log_error "SSH no encontrado"
        exit 1
    fi

    if ! command -v rsync &> /dev/null; then
        log_error "rsync no encontrado"
        exit 1
    fi

    log_success "Todos los requisitos locales verificados"
}

test_ssh_connection() {
    log_info "Probando conexión SSH a ${REMOTE_HOST}..."

    if ssh -p ${SSH_PORT} -o ConnectTimeout=10 -o BatchMode=yes ${REMOTE_USER}@${REMOTE_HOST} "echo 'SSH OK'" 2>/dev/null; then
        log_success "Conexión SSH establecida"
    else
        log_error "No se pudo conectar por SSH a ${REMOTE_HOST}"
        log_warning "Verifica:"
        echo "  1. SSH está habilitado en el panel de Hostinger"
        echo "  2. El host/user son correctos"
        echo "  3. Tienes una clave SSH configurada"
        echo ""
        echo "  Para configurar SSH en Hostinger:"
        echo "  Panel → Avanzado → SSH Access → Habilitar"
        echo ""
        exit 1
    fi
}

backup_remote() {
    log_info "Creando backup del servidor..."
    BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S)"

    ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
        cd ${REMOTE_DIR}
        if [ -f .env ]; then
            tar -czf /tmp/${BACKUP_NAME}.tar.gz .env storage/ 2>/dev/null || true
            echo 'Backup creado: /tmp/${BACKUP_NAME}.tar.gz'
        fi
    " 2>/dev/null || log_warning "Backup no creado (site nuevo o sin datos)"

    log_success "Backup completado"
}

prepare_local_build() {
    log_info "Preparando build local..."

    cd ${LOCAL_PROJECT}

    # Composer install
    log_info "Instalando dependencias PHP..."
    composer install --no-dev --optimize-autoloader --no-interaction

    # NPM build
    if [ -f "package.json" ]; then
        log_info "Compilando assets..."
        npm ci --silent 2>/dev/null || npm install --silent
        npm run build 2>/dev/null || log_warning "Build de assets falló (no crítico)"
    fi

    # Limpiar caches
    php artisan config:clear 2>/dev/null || true
    php artisan route:clear 2>/dev/null || true
    php artisan view:clear 2>/dev/null || true
    php artisan cache:clear 2>/dev/null || true

    log_success "Build local preparado"
}

sync_files() {
    log_info "Sincronizando archivos al servidor..."

    # Excluir archivos innecesarios
    rsync -avz --progress \
        -e "ssh -p ${SSH_PORT}" \
        --exclude='.git' \
        --exclude='node_modules' \
        --exclude='.env' \
        --exclude='storage/logs/*' \
        --exclude='storage/framework/cache/*' \
        --exclude='storage/framework/sessions/*' \
        --exclude='storage/framework/views/*' \
        --exclude='bootstrap/cache/*.php' \
        --exclude='tests' \
        --exclude='.env.example' \
        --exclude='phpunit.xml' \
        --exclude='vite.config.js' \
        --exclude='tailwind.config.js' \
        --exclude='postcss.config.js' \
        --exclude='README.md' \
        --exclude='composer' \
        --exclude='deploy.sh' \
        --exclude='install.sh' \
        --exclude='php.ini' \
        ${LOCAL_PROJECT}/ \
        ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/

    log_success "Archivos sincronizados"
}

sync_env_file() {
    log_info "Sincronizando archivo .env..."

    if [ -f ".env.production" ]; then
        scp -P ${SSH_PORT} .env.production ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_DIR}/.env
        log_success ".env.production copiado como .env"
    else
        log_error "No se encontró .env.production; se cancela para no subir configuración local"
        log_warning "Crea .env.production con las credenciales y valores de Hostinger antes de desplegar"
        exit 1
    fi
}

setup_remote() {
    log_info "Configurando entorno en el servidor..."

    ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
        cd ${REMOTE_DIR}

        # Verificar PHP
        echo '--- PHP Version ---'
        php -v | head -1

        # Verificar/instalar Composer
        if [ ! -f composer.phar ]; then
            echo 'Descargando Composer...'
            curl -sS https://getcomposer.org/installer | php
        fi

        # Composer install
        echo '--- Instalando dependencias ---'
        php composer.phar install --no-dev --optimize-autoloader --no-interaction

        # Generar APP_KEY si no existe
        if ! grep -q 'base64:[A-Za-z0-9]\{32,\}' .env 2>/dev/null; then
            echo 'Generando APP_KEY...'
            php artisan key:generate --force
        fi

        # Storage link
        echo '--- Storage link ---'
        php artisan storage:link --force 2>/dev/null || true

        # Permisos
        echo '--- Configurando permisos ---'
        chmod -R 775 storage/ 2>/dev/null || true
        chmod -R 775 bootstrap/cache/ 2>/dev/null || true
        chown -R \$(whoami):\$(whoami) storage/ 2>/dev/null || true
        chown -R \$(whoami):\$(whoami) bootstrap/cache/ 2>/dev/null || true

        echo '✓ Configuración remota completada'
    "
    log_success "Entorno configurado en el servidor"
}

run_migrations() {
    log_info "Ejecutando migraciones..."

    read -p "¿Ejecutar migraciones en el servidor? (s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
            cd ${REMOTE_DIR}
            php artisan migrate --force
        "
        log_success "Migraciones completadas"
    else
        log_warning "Migraciones saltadas"
    fi
}

run_seeders() {
    log_info "¿Ejecutar seeders (poblar base de datos)?"

    read -p "¿Ejecutar seeders? Esto BORRA datos existentes (s/n): " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Ss]$ ]]; then
        log_warning "⚠️  Esto ejecutará migrate:fresh --seed (borra todo)"
        read -p "¿Confirmar? (s/n): " -n 1 -r
        echo ""
        if [[ $REPLY =~ ^[Ss]$ ]]; then
            ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
                cd ${REMOTE_DIR}
                php artisan migrate:fresh --seed --force
            "
            log_success "Seeders ejecutados"
        fi
    fi
}

optimize_remote() {
    log_info "Optimizando para producción..."

    ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
        cd ${REMOTE_DIR}

        # Cache de configuración
        php artisan config:cache

        # Cache de rutas
        php artisan route:cache

        # Cache de vistas
        php artisan view:cache

        # Cache de eventos
        php artisan event:cache 2>/dev/null || true

        # Optimizar autoloader
        composer dump-autoload --optimize --no-dev 2>/dev/null || true

        echo '✓ Optimizaciones de producción completadas'
    "
    log_success "Sitio optimizado para producción"
}

setup_cron() {
    log_info "Configurando cron job para queue worker..."

    ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
        # Verificar si el cron ya existe
        CRON_LINE='* * * * * cd ${REMOTE_DIR} && php artisan schedule:run >> /dev/null 2>&1'

        # Obtener cron actual
        CURRENT_CRON=\$(crontab -l 2>/dev/null || echo '')

        if echo \"\$CURRENT_CRON\" | grep -q 'artisan schedule:run'; then
            echo 'Cron ya configurado'
        else
            # Agregar cron job
            (echo \"\$CURRENT_CRON\"; echo \"\$CRON_LINE\") | crontab -
            echo 'Cron job configurado'
        fi

        echo '--- Cron actual ---'
        crontab -l 2>/dev/null | tail -3
    "
    log_success "Cron configurado"
}

setup_ssl() {
    log_info "Verificando SSL..."

    ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
        # Hostinger normalmente configura SSL automáticamente
        if [ -f /etc/letsencrypt/live/${REMOTE_HOST}/fullchain.pem ]; then
            echo 'SSL activo'
        else
            echo 'SSL no detectado - configura desde el panel de Hostinger'
            echo 'Panel → Dominios → SSL → Habilitar'
        fi
    "
}

verify_deployment() {
    log_info "Verificando despliegue..."

    # Test HTTP
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://${REMOTE_HOST}/" 2>/dev/null || echo "000")

    if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "302" ]; then
        log_success "Sitio web respondiendo (HTTP ${HTTP_CODE})"
    else
        log_warning "Sitio respondió con HTTP ${HTTP_CODE} - verifica la configuración"
    fi

    # Test Admin
    ADMIN_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://${REMOTE_HOST}/admin" 2>/dev/null || echo "000")

    if [ "$ADMIN_CODE" = "200" ] || [ "$ADMIN_CODE" = "302" ]; then
        log_success "Panel admin accesible (HTTP ${ADMIN_CODE})"
    else
        log_warning "Panel admin respondió HTTP ${ADMIN_CODE}"
    fi
}

print_summary() {
    echo ""
    echo -e "${CYAN}╔══════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║          ✅ DESPLIEGUE COMPLETADO                   ║${NC}"
    echo -e "${CYAN}╚══════════════════════════════════════════════════════╝${NC}"
    echo ""
    echo -e "  🌐 Sitio web:    ${GREEN}https://${REMOTE_HOST}${NC}"
    echo -e "  🔧 Panel admin:  ${GREEN}https://${REMOTE_HOST}/admin${NC}"
    echo -e "  📧 Admin login:  admin@TU_DOMINIO.com"
    echo -e "  🔑 Admin pass:   password"
    echo ""
    echo -e "  ${YELLOW}Post-despliegue:${NC}"
    echo "  1. Cambiar contraseña de admin en el panel"
    echo "  2. Configurar claves Stripe reales en .env"
    echo "  3. Configurar API key de Groq/OpenAI en .env"
    echo "  4. Configurar SMTP real en .env"
    echo "  5. Subir imágenes via panel de Filament"
    echo ""
}

# ===================== MENÚ PRINCIPAL =====================

print_banner

echo "Selecciona el tipo de despliegue:"
echo ""
echo "  1) Despliegue completo (primera vez)"
echo "  2) Actualización de código (solo archivos)"
echo "  3) Solo migraciones"
echo "  4) Solo optimización"
echo "  5) Configurar cron jobs"
echo "  6) Verificar despliegue"
echo "  7) Salir"
echo ""
read -p "Opción [1-7]: " choice

case $choice in
    1)
        echo ""
        echo -e "${CYAN}=== DESPLIEGUE COMPLETO ===${NC}"
        echo ""

        # Configuración interactiva
        read -p "Host/dominio [${REMOTE_HOST}]: " input_host
        REMOTE_HOST="${input_host:-$REMOTE_HOST}"

        read -p "Usuario SSH [${REMOTE_USER}]: " input_user
        REMOTE_USER="${input_user:-$REMOTE_USER}"

        read -p "Directorio remoto [${REMOTE_DIR}]: " input_dir
        REMOTE_DIR="${input_dir:-$REMOTE_DIR}"

        read -p "Base de datos [${DB_NAME}]: " input_db
        DB_NAME="${input_db:-$DB_NAME}"

        read -sp "Contraseña DB: " input_pass
        echo ""
        DB_PASS="${input_pass:-$DB_PASS}"

        echo ""
        echo "Configuración:"
        echo "  Host:     ${REMOTE_HOST}"
        echo "  User:     ${REMOTE_USER}"
        echo "  Dir:      ${REMOTE_DIR}"
        echo "  Database: ${DB_NAME}"
        echo ""
        read -p "¿Continuar? (s/n): " -n 1 -r
        echo ""

        if [[ ! $REPLY =~ ^[Ss]$ ]]; then
            echo "Cancelado."
            exit 0
        fi

        check_local_requirements
        test_ssh_connection
        prepare_local_build
        sync_files
        sync_env_file
        setup_remote
        run_migrations
        optimize_remote
        setup_cron
        setup_ssl
        verify_deployment
        print_summary
        ;;

    2)
        echo ""
        echo -e "${CYAN}=== ACTUALIZACIÓN DE CÓDIGO ===${NC}"
        check_local_requirements
        prepare_local_build
        sync_files
        ssh -p ${SSH_PORT} ${REMOTE_USER}@${REMOTE_HOST} "
            cd ${REMOTE_DIR}
            php composer.phar install --no-dev --optimize-autoloader --no-interaction
            php artisan config:cache
            php artisan route:cache
            php artisan view:cache
        "
        log_success "Código actualizado y caches refrescados"
        ;;

    3)
        echo ""
        echo -e "${CYAN}=== MIGRACIONES ===${NC}"
        run_migrations
        ;;

    4)
        echo ""
        echo -e "${CYAN}=== OPTIMIZACIÓN ===${NC}"
        optimize_remote
        ;;

    5)
        echo ""
        echo -e "${CYAN}=== CRON JOBS ===${NC}"
        setup_cron
        ;;

    6)
        echo ""
        echo -e "${CYAN}=== VERIFICACIÓN ===${NC}"
        verify_deployment
        ;;

    7)
        echo "Saliendo..."
        exit 0
        ;;

    *)
        echo "Opción no válida"
        exit 1
        ;;
esac
