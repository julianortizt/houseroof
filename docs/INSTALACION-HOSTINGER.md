# Instalación de Costa Resin & Construction en Hostinger

> Una sola página, paso a paso. Estructura del proyecto: **docroot = raíz del proyecto**
> (`index.php` y `.htaccess` en la raíz; `public/` solo sirve para el desarrollo local).
> No publiques `node_modules`, `vendor`, `.env` ni `_devtools/` — el `.htaccess` ya los bloquea,
> pero sube un archivo limpio.

---

## 0. Lo que necesitas

- Una cuenta Hostinger con el dominio apuntando a tu hosting.
- **Base de datos MySQL**: créala en **hPanel → Databases → MySQL Databases**
  (nombre, usuario, contraseña). Anótalos: los usará el instalador.
- Elegir si usar **Opción A (instalador web, recomendada, sin SSH)** u **Opción B (script/bash por SSH)**.

---

## 1. Opción A — Instalador web (recomendada)

### Paso 1 — Subir los archivos
1. Descarga el proyecto completo (zip de `public_html`, incluyendo `.htaccess`, `index.php`, `app/`, `config/`, etc.).
2. En hPanel → **File Manager** (o FTP/SFTP), sube el **contenido** a `/home/usuario/public_html/`
   (la carpeta raíz del dominio, no dentro de `/public_html/public`).
3. Comprueba que existe `index.php` y `.htaccess` en la raíz del sitio.

### Paso 2 — Abrir el instalador web
Abre en el navegador: **`https://TU_DOMINIO/install`**

> El instalador solo funciona sobre un sitio "virgen" (sin el archivo `.installed`).
> Si ya aparece 404, el sitio ya está instalado — bórralo y vuelve a empezar solo si quieres reinstalar.

### Paso 3 — El asistente (5 pasos, todo por web)
1. **Bienvenida**: verifica requisitos del servidor (PHP, extensiones, permisos). Deben salir ✅.
2. **Base de datos**: escribe los datos del paso 0. Verás "Conexión OK" o el error de Hostinger
   (si falla, suele ser que el host es `localhost` en vez de `127.0.0.1`).
3. **Sitio**: nombre público, URL, **email y contraseña del super_admin** (la primera y única cuenta inicial),
   y datos SMTP (puedes dejarlos vacíos ahora).
4. **Confirmar**: revisa y pulsa **Instalar**.
5. **Finalizado**: el sitio está listo; el instalador **se bloquea solo** (se crea `.installed` en la raíz).

### Paso 4 — Entrar al panel
- **Web:** `https://TU_DOMINIO/`
- **Panel admin:** `https://TU_DOMINIO/admin`
- Entra con el email y contraseña del paso 3. **Cámbiala** y guárdala en un gestor: no existe otra copia.

### Reinstalar / borrar la instalación
- Para forzar una reinstalación, **borra** el archivo `.installed` en la raíz del sitio (vía File Manager).
- **Ojo**: vuelve a leer este manual; los pasos vuelven a emitir `.env`, tablas y admin.

---

## 2. Opción B — Instalación por script (requiere SSH activo en Hostinger)

### Paso 1 — Subir el proyecto
Como en la Opción A (o `git clone` por SSH dentro de `~/public_html`).

### Paso 2 — Ejecutar el instalador
```bash
cd ~/public_html
chmod +x install-hostinger.sh
./install-hostinger.sh
```
El script: pide dominio y credenciales MySQL, escribe `.env`, ejecuta las migraciones,
selea roles y hace `config:cache`, `route:cache`, `view:cache`, `storage:link`.

### Paso 3 — Crear el super_admin inicial
Vía SSH:
```bash
php artisan tinker
$u = App\Models\User::create(['name'=>'Costa Resin & Construction','email'=>'admin@tudominio.com','password'=>bcrypt('tu-contraseña-segura')]);
$u->assignRole('super_admin');
```
o, mejor, usa el instalador web de la Opción A (crea y bloquea en un solo flujo).

### Paso 4 — Entrar
Igual que en la Opción A.

---

## 3. Esquema SQL de importación manual (sin instalador, sin SSH)

Si el hosting solo permite phpMyAdmin (sin SSH ni instalación web):

1. Crea la base MySQL en hPanel.
2. hPanel → **phpMyAdmin** → selecciona la BD → **Importar** → elige `docs/database.sql`
   (esquema completo, 73 tablas, sin datos; genera las tablas tal como están las migraciones).
3. Crea el `.env` (partiendo de `.env.example`): `APP_KEY`, `DB_*`, `APP_URL`, `APP_ENV=production`, `APP_DEBUG=false`.
4. Genera la clave: `php artisan key:generate` (o añade la de `.env.example` actual).
5. **Importante**: `docs/database.sql` NO trae datos ni usuario admin; ejecuta
   `php artisan db:seed --class=InstallSeeder --force` y crea el super_admin como en la Opción B.

> Usalo solo si las Opciones A y B no son viables — la Opción A es la vía soportada.

---

## 4. Post-instalación (checklist)

- [ ] `https://TU_DOMINIO/admin` entra con el admin del paso 3
- [ ] Cambiar contraseña del super_admin tras el primer acceso
- [ ] SSL activo (hPanel → Dominios → SSL)
- [ ] SMTP real configurado (Google con contraseña de aplicación, u otros)
- [ ] `MAPTILER_KEY` en `.env` (opcional): clave del plan gratuito de MapTiler — da la opción "Satellite (MapTiler)" en la medición exterior con zoom hasta 22; sin ella, el sistema sigue con PNOA/Esri.
- [ ] Carga imágenes del negocio (logo, servicios, productos) y datos de empresa (dirección, teléfono, email, WhatsApp) en `/admin` → Ajustes
- [ ] Reservar/contratar el dominio definitivo y actualizar `APP_URL` + `.env` + `routes/web.php` si usaste `TU_DOMINIO.com`
- [ ] Crear las páginas legales desde `/admin` → Páginas con datos reales (**no publicar las del seeder**, ver `docs/LEGAL-REVIEW.md`)
- [ ] Stripe: solo tras asesoría fiscal (por defecto los cobros están apagados)
- [ ] Confirmar en hPanel que el directorio `storage/` es escribible (755/775)
- [ ] `php artisan storage:link` (lo intenta el instalador; si falla, créalo a mano)

---

## 5. Estructura tras instalar (recordatorio)

```
public_html/               ← raíz del sitio (docroot)
├── index.php              ← entry point (NO mover)
├── .htaccess              ← reglas (bloquean app/, .env, _devtools/, *.md…)
├── .installed             ← marca de "ya instalado" (404 en /install)
├── app/                   ← lógica Laravel (bloqueado vía web)
├── composer.*             ← bloqueado vía web
├── config/                ← bloqueado vía web
├── docs/                  ← documentación (bloqueada vía web)
├── storage/               ← escribible en hosting (755/775)
├── _devtools/             ← herramientas locales archivadas (bloqueado vía web)
└── vendor/                ← bloqueado vía web
```

---

## 6. Resolución de problemas

| Síntoma | Causa probable | Solución |
|---|---|---|
| 500 inmediato | Permisos `storage/`/`bootstrap/cache` | chmod 775 en `storage bootstrap/cache` |
| 500 tras subir | `.env` ausente o sin `APP_KEY` | Usa el instalador web (crea `.env` + clave) o `php artisan key:generate` |
| `/install` devuelve 404 | `.installed` existe | Bórralo si quieres reinstalar |
| `/install` devuelve error de requisitos | falta extensión PHP (pdo_mysql, mbstring, curl, gd, xml) | hPanel → PHP → Selección de módulos |
| Tablas no aparecen | MySQL import parcial | Reimportar `docs/database.sql` (opción 3) o usar el instalador |
| "Acceso denegado" en admin | credenciales no creadas | Instalar de nuevo (borra `.installed`) o crear usuario por SSH/tinker |
| Imágenes no cargan | `storage:link` no aplicado | hPanel → symlink o `php artisan storage:link` |
| Error 403 al hacer clic | `.htaccess` bloqueando ruta legítima | Avisar; no editar sin revisar las reglas |
| Blancos / errores PHP | OPcache con código antiguo | hPanel → OPcache → limpiar (o `php -d opcache.opt_debug=1 -r "opcache_reset();"`) |

---

## 7. Seguridad mínima antes de publicar

1. **Rota todo lo que haya viajado por el repo**: super_admin, APP_KEY, claves Stripe/IA si se usaron.
2. **Elimina** `_devtools/` si el zip final lo incluye (contiene backups; está bloqueado pero no sobra).
3. Oculta `.` archivos ocultos se sirven con `? ` .htaccess — verificar `HEAD /.env` → **403**.
4. Activa SSL y fuerza HTTPS (redirección en hPanel o en `.htaccess`).
5. Mantén `.installed` si no quieres que el instalador sea accesible nunca más.

---

## 8. Documentos relacionados (mismo proyecto, `docs/`)

- `CREDENCIALES.md` — usuarios y contraseñas locales y de producción
- `LEGAL-REVIEW.md` — auditoría de páginas legales
- `ASSET-AUDIT.md` — auditoría de imágenes y recursos
- `SUPER-PANEL-SCOPE.md` — alcance del panel Super (desarrollador)
- `database.sql` — esquema MySQL completo (importación manual)
