# 🚀 Guía de Despliegue en Hostinger

## ⚡ Instalación Rápida (Recomendada)

Después de subir los archivos del proyecto a `/home/usuario/public_html`:

```bash
# 1. Conectar por SSH
ssh usuario@tudominio.com

# 2. Ir al directorio del proyecto
cd ~/public_html

# 3. Dar permisos y ejecutar el instalador
chmod +x install-hostinger.sh
./install-hostinger.sh
```

El instalador te pedirá:
- Dominio del sitio
- Credenciales de la base de datos
- Email SMTP
- API Key de Groq (opcional)

Y automáticamente configurará:
- Archivo `.env` con producción (`APP_DEBUG=false`)
- Migraciones de la base de datos
- Permisos de archivos
- Storage link
- Cachés de producción

---

## Prerrequisitos en Hostinger

### Document root del dominio

El proyecto completo debe quedar en `/home/u123456789/public_html` y el dominio debe apuntar únicamente a:

```
/home/u123456789/public_html/public
```

En Hostinger: **Dominios → Administrar → Cambiar directorio raíz**. No apuntes el dominio a la raíz `public_html`, porque allí están `.env`, `app/` y `storage/`.

### 1. Habilitar SSH
```
Panel de Hostinger → Avanzado → SSH Access → Habilitar
```

### 2. Crear base de datos MySQL
```
Panel → Bases de datos → Crear nueva base de datos
- Nombre: u123456789_costaresin
- Usuario: u123456789_admin
- Contraseña: (guardarla)
```

### 3. Habilitar PHP 8.2+
```
Panel → PHP → Seleccionar PHP 8.2
Extensiones requeridas (activar todas):
✅ bcmath, ctype, curl, dom, fileinfo, json
✅ mbstring, openssl, pdo, pdo_mysql
✅ tokenizer, xml, exif, intl, gd, zip
```

### 4. Configurar cron job
```
Panel → Avanzado → Cron Jobs → Añadir:
* * * * * cd /home/u123456789/public_html && php artisan schedule:run >> /dev/null 2>&1
```

---

## Método 1: Despliegue Automático (Recomendado)

### Instalador guiado para Hostinger

Después de subir el proyecto completo a `/home/usuario/public_html`, ejecuta:

```bash
cd /home/usuario/public_html
chmod +x install-hostinger.sh
./install-hostinger.sh
```

El instalador solicita URL, credenciales MySQL, SMTP y la clave opcional de Groq. Después ejecuta Composer, genera `APP_KEY`, crea el enlace de storage, aplica migraciones, instala roles y usuarios, y construye las cachés de producción. No ejecuta `npm` en Hostinger; los assets deben subirse ya compilados desde `public/build`.

Las cuentas administrativas creadas por el instalador son `dev@TU_DOMINIO.com`, `paulnoble@TU_DOMINIO.com` y `cristianquinones@TU_DOMINIO.com`. Cambia sus contraseñas inmediatamente después del primer acceso.

### Preparar el entorno
```bash
# 1. Editar deploy.sh con tus datos
nano deploy.sh

# Cambiar:
REMOTE_HOST="tudominio.com"
REMOTE_USER="u123456789"
REMOTE_DIR="/home/u123456789/public_html"
DB_NAME="u123456789_costaresin"
DB_USER="u123456789_admin"
DB_PASS="tu_contraseña"
```

### Ejecutar despliegue completo
```bash
chmod +x deploy.sh
./deploy.sh
```

Seleccionar opción **1** (Despliegue completo) y seguir las instrucciones.

---

## Método 2: Deploy Manual por SSH

### 1. Subir archivos
```bash
# Desde tu máquina local
rsync -avz --progress \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='.env' \
  ./ tu_usuario@tudominio.com:/home/tu_usuario/public_html/
```

### 2. Conectar al servidor
```bash
ssh tu_usuario@tudominio.com
cd /home/tu_usuario/public_html
```

### 3. Configurar en el servidor
```bash
# Instalar Composer (si no existe)
curl -sS https://getcomposer.org/installer | php
mv composer.phar composer

# Instalar dependencias
php composer install --no-dev --optimize-autoloader

# Configurar .env
cp .env.production .env
nano .env  # Editar credenciales

# Generar APP_KEY
php artisan key:generate

# Crear storage link
php artisan storage:link

# Permisos
chmod -R 775 storage bootstrap/cache

# Migraciones
php artisan migrate --force

# Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

El `.env` de producción debe incluir al menos:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com
FILESYSTEM_DISK=public
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
SESSION_SECURE_COOKIE=true
```

`file` y `sync` evitan depender de workers o tablas adicionales, algo importante en hosting compartido. Las credenciales MySQL y SMTP deben ser las proporcionadas por Hostinger.

---

## Método 3: Git Deploy (Avanzado)

### Configurar Git en Hostinger
```bash
# En el servidor
cd /home/tu_usuario/public_html
git init
git remote add origin git@github.com:tu-usuario/costa-resin-website.git
git fetch origin
git checkout main
```

### Script de deploy vía Git
```bash
#!/bin/bash
# git-deploy.sh
ssh tu_usuario@tudominio.com << 'ENDSSH'
    cd /home/tu_usuario/public_html
    git pull origin main
    php composer install --no-dev --optimize-autoloader
    php artisan migrate --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo "✅ Deploy completado"
ENDSSH
```

---

## Despliegue Rápido (Actualizaciones)

Para actualizaciones menores (cambios de código):
```bash
chmod +x quick-deploy.sh
./quick-deploy.sh
```

---

## After Deploy Checklist

### ✅ Verificar después de cada despliegue
1. [ ] El sitio carga: `https://tudominio.com`
2. [ ] El admin funciona: `https://tudominio.com/admin`
3. [ ] Las imágenes se muestran
4. [ ] El checkout de Stripe funciona
5. [ ] Los emails se envían
6. [ ] El cron job está activo
7. [ ] SSL está habilitado

### 🔒 Seguridad post-despliegue
1. Cambiar contraseña de admin
2. Verificar que `.env` no sea accesible públicamente
3. Verificar que `DEBUG=false` en `.env`
4. Activar SSL si no está activo
5. Configurar headers de seguridad

### ⚙️ Configurar en el panel de Hostinger
1. **SSL**: Panel → Dominios → SSL → Habilitar
2. **PHP**: Panel → PHP → Versión 8.2
3. **Extensiones**: Verificar todas las requeridas
4. **Cron**: Panel → Avanzado → Cron Jobs

---

## Solución de Problemas

### Error 500
```bash
# Verificar logs
tail -50 storage/logs/laravel.log

# Verificar permisos
chmod -R 775 storage bootstrap/cache

# Verificar .env
php artisan config:cache
```

### Error de base de datos
```bash
# Verificar credenciales en .env
php artisan migrate:status

# Si es necesario, recrear
php artisan migrate:fresh --seed --force
```

### Imágenes no se muestran
```bash
# Recrear storage link
php artisan storage:link --force

# Verificar permisos
chmod -R 775 storage
```

### Permisos denegados
```bash
chmod -R 775 storage bootstrap/cache
chown -R $(whoami):$(whoami) storage bootstrap/cache
```

---

## Estructura de archivos en Hostinger

```
/home/u123456789/public_html/
├── app/                    # Código Laravel
├── bootstrap/
├── config/
├── database/
├── public/                 # Document root (web)
│   ├── index.php
│   ├── build/              # Assets compilados
│   └── storage -> ../storage/app/public
├── resources/
├── routes/
├── storage/                # Datos persistentes
│   └── app/public/         # Imágenes subidas
├── vendor/                 # Dependencias PHP
├── .env                    # Configuración (NO subir a Git)
└── artisan
```

---

## Comandos Útiles en el Servidor

```bash
# Ver estado del sitio
php artisan about

# Limpiar todos los caches
php artisan optimize:clear

# Reconstruir caches
php artisan optimize

# Ver logs
tail -f storage/logs/laravel.log

# Verificar cola de trabajos
php artisan queue:work --once

# Verificar tareas pendientes
php artisan queue:failed

# Modo mantenimiento
php artisan down --message="Mantenimiento programado"
php artisan up
```
