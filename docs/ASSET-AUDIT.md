# Auditoría de recursos visuales — 2026-11-15

> SOLO LECTURA. No se modificó, movió ni borró ningún archivo. Ningún binario fue inspeccionado: toda clasificación usa nombres, rutas, tamaños y hashes MD5. Todo lo marcado (b) y (c) requiere inspección humana antes de aprobar.

## Inventario

| Carpeta | Archivos imagen | Tamaño | Notas |
|---|---|---|---|
| `images/` | 15 | 3,76 MiB | logo.png == favicon.png |
| `simulator-rooms/` | 18 (15 únicos) | 1,41 MiB | duplicados por mayúsculas/tildes (`BAÑO.jpg` = `bano.jpg`) |
| `simulator-textures/` | 9 | 22,17 MiB | PBR granite/concrete/metal; licencia desconocida |
| `storage/app/public/` | 266 (122 MD5 únicos) | 75,6 MiB | **~48,6 MiB son duplicados exactos** |
| `public/storage/` | espejo físico de storage/app/public | 75,6 MiB | NO es symlink: posible doble de peso servido |
| `public/simulator-rooms/`, `public/simulator-textures/` | copias físicas | ~24 MiB | mismo caso |

## Hallazgos principales

1. **`site/og-default.jpg` es la foto de la fábrica de Benissa** (== `images/factory-benissa.jpg` == `site/factory.jpg`). Es la og:image por defecto del sitio (`layouts/app.blade.php`). → reemplazar por og genérica.
2. **Logo/favicon**: `images/logo.png` == `favicon.png`; `logo-white.png` == `site/logo-alt.png`. ¿Marca legacy o actual?
3. **~120 assets del catálogo importado (Infinity Effects / ROX)**: spray-granite-*, spray-stone*, rox-binder-*, diamond-coat*, fresch-coat*, fusion-promoter*, exteri-pro-paint, fijafix, amourguard, kits, imprimaciones, colores v512 (×2 copias previews↔textures idénticas), cuarzo-*/rojo-alicante/rosa-valencia/…, «Leopard», resin-bound/bonded. Producto real de la empresa origen.
4. **Fotos de obra real**: industrial-floor, service-granite, service-resin (= pool-terrace), countertop, terrace-example, hero-bg (= project-design), leopard.
5. **Referencia rota**: `JsonLdService.php` apunta a `/storage/site/placeholder.jpg` (no existe).
6. **Backups** blades (`.bak_*`) referencian los mismos assets (limpieza pendiente).

## Acciones requeridas del titular (antes de borrar/reemplazar)

- [ ] ¿Plataforma 100 % genérica o mantiene catálogo físico? (decide destino de los ~120 assets de producto)
- [ ] Confirmar/regenerar logo, logo-white y favicon según marca vigente.
- [ ] Aprobar nueva og:image genérica; actualizar `layouts/app.blade.php` tras aprobación.
- [ ] Decidir retirada de foto de fábrica (slide 1 del hero por defecto en `home.blade.php` y `ContentBlockSeeder.php`).
- [ ] Decidir si las fotos de obra real pueden publicarse o se reemplazan.
- [ ] Verificar licencia de `simulator-textures/` y de los finishes `.v512.png`.
- [ ] Decidir eliminación de ~75 MiB (`public/storage` físico) y ~48,6 MiB de duplicados internos; revisar si deploy usa symlink `storage:link` o copia física.
- [ ] Eliminar duplicados por mayúsculas en `simulator-rooms/`.
- [ ] Resolver `/storage/site/placeholder.jpg` (añadir asset o eliminar fallback).
- [ ] Confirmar en BD de PRODUCCIÓN qué se sirve de verdad (local está vacía) — requiere acceso a producción; no auditable aquí.

## Vídeo de portada (hero) — 2026-11-16

- `videos/hero.mp4` (4,4 MB · 1600×900 · H.264 · sin audio) y `videos/hero.webm` (5,7 MB · VP9). Poster `videos/hero-poster.jpg` embebido como respaldo y para `prefers-reduced-motion`.
- Fuente: **Pexels License** — "Skilled Workers Applying Concrete Floor Indoors" (https://www.pexels.com/video/skilled-workers-applying-concrete-floor-indoors-35769623/). Uso comercial permitido, sin atribución obligatoria.
- Implementado en `resources/views/pages/home-resin-business.blade.php` (fondo a pantalla completa + overlay degradado). Falta validación visual en dispositivo real; reemplazar por metraje propio de Costa Resin & Construction cuando esté disponible.

## URLs externas (solo OSM/Leaflet): tiles openstreetmap/opentopomap y marker icons de unpkg — sin fotos stock hotlinked.
