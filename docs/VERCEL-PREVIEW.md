# Vista previa estática en Vercel — **temporal y provisional**

> **Estado:** solución provisional para poder "ver la web" en Vercel sin desplegar el backend.
> **No es un despliegue de producción.** El sitio real (Laravel 11 + Filament + Livewire + MySQL)
> está pensado para Hostinger (ver `DEPLOY-HOSTINGER.md` / `GUIA-INSTALACION-HOSTINGER.md`).
>
> **Quién debe eliminarlo:** en cuanto la web se sirva con backend real (Hostinger) o se decida
> no usar Vercel para nada, este andamiaje debe borrarse por completo.
> Ver [Cómo eliminar esta vista previa](#cómo-eliminar-esta-vista-previa).

## Qué es

Vercel no ejecuta PHP/Laravel de forma nativa (sin un runtime no oficial y frágil, y sin
MySQL/colas/storage persistente). Para poder publicar algo en Vercel **hoy**, se generó una
**instantánea estática** de las páginas públicas del sitio con el código que hay en este repo:

- Contenido: páginas públicas capturadas del sitemap (`/`, `/tienda`, `/servicios`, `/proyectos`,
  `/blog`, `/contacto`, `/cotizacion`, páginas legales, 25 productos semilla y categorías semilla).
- `vercel-static/` contiene el HTML prerenderizado + assets (`build/`, `css/`, `js/`, `images/`,
  `videos/`, `storage/site/`) y un `404.html` autocontenido.
- `vercel.json` raíz sirve `vercel-static/` como sitio estático (`outputDirectory`,
  `cleanUrls: true`). Sin comando de build ni framework.
- Dominio canónico del snapshot: `https://costaresin.vercel.app` (ya fijado en el HTML/sitemap;
  para otro dominio, regenera con `scripts/build-static-preview.sh https://TU-DOMINIO`).

## Cómo desplegar en Vercel

1. Importa `julianortizt/costaresin` en Vercel (New Project).
2. **Framework Preset:** Other · **Build Command:** *(vacío)* · **Output Directory:** `vercel-static`
   (Vercel lee `vercel.json`; el ajuste en el dashboard es redundante pero recomendable).
3. Deploy. Cada push a `main` republica la instantánea **tal cual está commiteada**.

## Limitaciones (lo que NO funciona)

- **Sin backend:** formularios (contacto, cotización), carrito, checkout, login de clientes/admin,
  panel `/admin` y `/super-panel`, buscador, selector de idioma, Livewire y chat IA **no funcionan**.
- El catálogo muestra los **productos/categorías/contenido del seeder local**, no el catálogo real
  de producción (que vive en la base de datos de Hostinger y no se toca).
- Las páginas de proyecto (`/proyectos/<slug>`, 5 URLs del sitemap antiguo) **no existen** en la
  instantánea y devuelven 404 (no hay seeder de proyectos). Se eliminaron del sitemap incluido.
- Las imágenes de producto salen con icono de placeholder: los archivos reales viven en el storage
  de producción.
- Los enlaces absolutos canónicos/OG apuntan a `https://costaresin.vercel.app`.
- Al ser HTML estático, los cambios de contenido en la BD **no** se reflejan: hay que regenerar
  la instantánea y volver a commitearla.

## Cómo regenerar la instantánea

```bash
scripts/build-static-preview.sh                     # dominio por defecto
scripts/build-static-preview.sh https://midominio   # otro dominio
```

El script: respalda `.env`, apunta temporalmente a una **SQLite desechable** en `/tmp` (la base de
datos de vista previa del equipo NO se toca), migra, siembra catálogo/páginas legales/contenido,
levanta `artisan serve`, rastrea `sitemap.xml`, reescribe los orígenes locales al dominio final,
recorta el sitemap y **restaura `.env` siempre** (incluso si falla). Revisa el resultado en
`vercel-static/` y haz commit.

## Cómo eliminar esta vista previa

Cuando la web se despliegue de verdad (Hostinger u otro host con PHP) o ya no se quiera Vercel:

```bash
git rm -r vercel-static
git rm vercel.json scripts/build-static-preview.sh docs/VERCEL-PREVIEW.md
# quitar la sección "Despliegue temporal en Vercel" del README si se añadió
git commit -m "chore: remove temporary Vercel static preview"
```

Y en Vercel: **Project Settings → General → Delete Project** (o deja el proyecto y desactiva los
deploys). En Hostinger esto no deja ningún rastro: es un andamiaje paralelo que no toca producción.

## Historial

- 2026-11-18 — Creada la vista previa estática (44 páginas), `vercel.json`, el script de
  regeneración y este documento. `docs/CREDENCIALES.md` se sacó del índice de git para no
  publicarlo (ver detalle en el mensaje del commit inicial).
