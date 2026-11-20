# Despliegue en Vercel (Laravel + Turso + S3)

Este proyecto ya no se despliega como HTML estático. `vercel.json` arranca un
contenedor **FrankenPHP** que ejecuta Laravel 11, incluye Filament/Livewire y
usa estos servicios externos:

| Necesidad | Servicio |
|---|---|
| Base de datos | **Turso** (SQLite remoto, Vercel Marketplace) |
| Sesiones y caché | Tablas `sessions` y `cache` en la misma BD Turso |
| Subidas del panel | Almacenamiento **S3-compatible** (Cloudflare R2, AWS S3, etc.) |
| Logs | `stderr` (Vercel Runtime Logs) |

> El archivo `vercel-static/` queda obsoleto. Ya no lo referencia `vercel.json`.

---

## 1. Requisitos locales

- PHP **8.4.1+** con extensiones `ffi`, `zip`, `exif`, `intl`, `gd`, `pdo_sqlite`
  (en esta máquina ya está instalado en
  `C:\Users\Julian\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe`).
- Usa `dev.bat` para arrancar el servidor local con PHP 8.4. Si lanzas
  `php artisan serve` desde un terminal antiguo, puedes volver a coger PHP 8.2
  y aparecerá el error `platform_check.php`.
- Vercel CLI: `npm i -g vercel`.

---

## 2. Base de datos Turso

1. En tu proyecto de Vercel, abre **Marketplace → Turso → Create database**.
2. Vercel inyectará automáticamente:
   - `TURSO_DATABASE_URL`
   - `TURSO_AUTH_TOKEN`
3. Añade estas variables de entorno en Vercel (Production y Preview):

```dotenv
APP_KEY=base64:...          # php artisan key:generate --show
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU_PROYECTO.vercel.app

DB_CONNECTION=libsql
LOG_CHANNEL=stderr

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=sync

FILESYSTEM_DISK=public
PUBLIC_DISK_DRIVER=s3
MEDIA_DISK=public
FILAMENT_FILESYSTEM_DISK=public
```

> No ejecutes `php artisan config:cache` en el build: Vercel inyecta las
> variables en runtime. El Dockerfile solo cachea eventos y vistas.

---

## 3. Almacenamiento de imágenes (S3)

El contenedor de Vercel no puede escribir en disco de forma persistente.
Configura un bucket S3-compatible (recomendado: **Cloudflare R2**) y añade:

```dotenv
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=nombre-del-bucket
AWS_ENDPOINT=https://<accountid>.r2.cloudflarestorage.com
AWS_URL=https://<tu-dominio-o-public-bucket>
AWS_USE_PATH_STYLE_ENDPOINT=true
```

El bucket debe permitir lectura pública (o servirlo mediante dominio propio /
CDN).

### Subir las imágenes existentes

```bash
# Con aws cli (endpoint ejemplo de R2)
aws s3 sync storage/app/public s3://TU_BUCKET \
  --endpoint-url https://<accountid>.r2.cloudflarestorage.com

# NO subir public/storage: es una copia duplicada de storage/app/public.
```

---

## 4. Migraciones (paso separado)

Las migraciones **no** se ejecutan al arrancar el contenedor. Hazlo una vez
después de crear la BD:

```bat
copy .env .env.backup
vercel env pull .env --environment=production
dev.bat :: en otra terminal, o directamente:
"C:\Users\Julian\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe" artisan migrate --force
copy .env.backup .env
```

Tras esto, las tablas `sessions` y `cache` existen en Turso y el login del
panel funciona entre instancias.

> No ejecutes `WordPressImporterSeeder`: contiene `SET FOREIGN_KEY_CHECKS`,
> específico de MySQL.

---

## 5. Despliegue

```bash
vercel --prod
```

Comprobaciones:

```bash
curl https://TU_PROYECTO.vercel.app/health
curl https://TU_PROYECTO.vercel.app/admin/login
```

El endpoint `/health` devuelve un JSON con `status: ok` y los checks de la
aplicación.

---

## 6. Repositorio Git

Vercel despliega desde GitHub/GitLab/Bitbucket. Esta carpeta aún no tenía
repo Git; ya está inicializada y lista para el primer commit.

---

## 7. Nota de seguridad

`composer audit` reporta 5 avisos en `laravel/framework` 11.x (uno alto:
inyección CRLF en la regla de email). Laravel 11 ya no recibe parches de
seguridad. Se recomienda planear la subida a Laravel 12 como trabajo
independiente, después de verificar este despliegue.
