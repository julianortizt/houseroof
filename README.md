# Costa Resin & Construction — Plataforma Web

Sitio corporativo + tienda online multifunctional con **Laravel 11**. Incluye el sitio público, el e-commerce de productos, el portal de clientes, el panel administrativo (Filament) y el módulo interno **MapsCalculator** (mediciones sobre satélite, presupuestos y órdenes de trabajo).

> **Estado del proyecto:** en desarrollo local activo. Producción (MySQL Hostinger) **sin tocar** — nada se ha desplegado ni migrado allí todavía.

---

## Stack

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.2+ (local 8.4) · Laravel 11.57 |
| Panel admin | Filament 3 (`/admin`) |
| Panel desarrollador | Filament 3 (`/super-panel`) |
| Frontend | Livewire 3 + Blade + Tailwind CSS 3 + Alpine.js |
| Base de datos | MySQL 5.7+ / MariaDB 10.3+ (producción) · SQLite (tests / vista previa local) |
| Medios | Spatie Media Library 11 |
| i18n | 7 idiomas frontend (ES/EN/DE/IT/FR/PT/NL) |
| Pagos | Stripe Checkout (**desactivado por defecto** — pendiente asesoría fiscal) |
| Tests | PHPUnit (`RefreshDatabase` sobre SQLite `:memory:`) |

---

## Qué tiene adentro

- **Sitio público**: portada (plantilla `resin_business`), servicios, proyectos, blog, contacto, tienda/e-commerce.
- **E-commerce**: catálogo, carrito, checkout, webhook Stripe firmado, resolución de pedidos con conflicto de stock.
- **Cotizaciones y órdenes de trabajo**: flujo completo → cotización → aprobación → orden → consumos de inventario → bitácora offline.
- **MapsCalculator**: medición por satélite (PNOA/Esri), clientes/CRM, proyectos, mediciones, PDFs, portal de cliente.
- **Portal de clientes**: registro/login con verificación, consulta de cotizaciones/proyectos/pedidos, descarga de PDFs.
- **Super Panel (desarrollador)**: banderas de módulos, apariencia del sitio, credenciales Stripe cifradas.
- **Simulador de acabados** (interno, tras login admin).

---

## Requisitos

- PHP 8.2+ (local: 8.4) con: `bcmath ctype curl dom fileinfo intl mbstring pdo pdo_mysql sqlite3 openssl tokenizer xml gd exif curl zip`
- Composer · Node.js 18+ (solo para compilar assets; la web funciona con los assets ya compilados)
- MySQL/MariaDB (producción) · SQLite (local/tests)

---

## Puesta en marcha local

```bash
cd costa-resin-website/public_html
composer install
cp .env.example .env
php artisan key:generate
# En .env local, DB_DATABASE apunta a storage/framework/local-preview.sqlite (ya migrado)
php artisan serve --host=127.0.0.1 --port=8000
```

http://127.0.0.1:8000 · Panel: `/admin`

**Acceso local** (ver `docs/CREDENCIALES.md`):
- `julianortizt@gmail.com` — contraseña restablecida (ver el doc, rol: `super_admin`).

## Tests

```bash
php artisan test        # 127 passed, 727 assertions (SQLite en memoria)
```

---

## Estructura

```
public_html/
├── index.php                 # entry point (docroot = raíz; Hostinger)
├── .htaccess                 # hardened: bloquea app/, .env, backups…
├── .installed                # bloquea el instalador web (/install)
├── app/                      # lógica: Filament, Livewire, Services, Models
├── routes/                   # web.php · mapscalculator.php · simulator.php · api.php
├── resources/views/          # Blade (pages, layouts, livewire, public, admin, installer)
├── lang/                     # ES/EN/DE/IT/FR/PT/NL (php y json)
├── build/ y public/build/    # assets compilados (Vite/Tailwind)
├── videos/                   # hero.mp4/webm + poster (portada)
├── docs/                     # documentación completa (índice abajo)
├── _devtools/                # herramientas archivadas + backups (bloqueadas vía web)
└── storage/                  # BD local preview, logs, caché
```

---

## Documentación completa

| Documento | Contenido |
|---|---|
| `docs/INSTALACION-HOSTINGER.md` | Despliegue paso a paso (instalador web y SSH) |
| `docs/CREDENCIALES.md` | ⚠️ **Solo local** (en `.gitignore`, no está en el repo). Usuarios y contraseñas |
| `docs/ARQUITECTURA.md` | Arquitectura técnica detallada |
| `docs/super-panel-scope.md` | Alcance del panel Super (desarrollador), 16 brechas |
| `docs/ASSET-AUDIT.md` | Auditoría de recursos visuales (duplicados, licencias) |
| `docs/LEGAL-REVIEW.md` | Auditoría de páginas legales (seeder ficticio) |
| `docs/database.sql` | Esquema MySQL completo (importación manual) |
| `PENDIENTES.md` | Pendientes y decisiones tomadas |

---

## Seguridad (resumen)

- `APP_DEBUG=false` en producción; secretos fuera del repo (`.env` en `.gitignore`); credenciales legacy eliminadas.
- `.htaccess` verificado con Apache: 403 a `.env`, `app/`, `vendor/`, `lang/`, `tests/`, `_devtools/`, `storage/framework`.
- Headers de seguridad (`X-Frame-Options`, `nosniff`, `Referrer-Policy`, `Permissions-Policy`, HSTS).
- Autenticación Laravel + roles Spatsie (`super_admin`, `manager`, `site_admin`, roles del MapsCalculator).
- Throttling en logins; verificación de firma en webhooks Stripe.

## Antes de producción

1. Dominio y credenciales reales (`TU_DOMINIO.com` en `.env.production` y `deploy.sh`).
2. `php artisan migrate --force` **en producción** (¡nunca aplicado todavía!).
3. Páginas legales reales (el seeder actual usa datos ficticios — ver `docs/LEGAL-REVIEW.md`).
4. Rotar toda contraseña que haya viajado por el repo.
5. Stripe: solo tras asesoría fiscal; checkout apagado por defecto.

---

## Despliegue temporal en Vercel (preview estática) — **provisional**

Vercel no ejecuta Laravel/PHP de forma nativa. Como solución **temporal**, el repo incluye una
**instantánea estática** de las páginas públicas en `vercel-static/` y un `vercel.json` que la
sirve tal cual (sin build, Output Directory = `vercel-static`).

- **No es producción**: sin backend (formularios, carrito, `/admin` y `/super-panel` no funcionan),
  catálogo y contenido del seeder local, sin páginas de proyecto individuales.
- Despliegue: importa el repo en Vercel → Framework: *Other*, Build Command vacío,
  Output Directory `vercel-static`.
- Regenerar: `scripts/build-static-preview.sh https://tu-dominio`
- Eliminar (cuando la web se sirva con PHP en Hostinger): ver
  [`docs/VERCEL-PREVIEW.md`](docs/VERCEL-PREVIEW.md) — incluye el registro para borrar todo el andamiaje.
