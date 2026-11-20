# Anti-descarga y anti-clonado (Hostinger)

> **Para qué sirve:** disuadir y volver inútil la descarga automatizada del sitio con
> herramientas tipo HTTrack / wget --mirror / Teleport / offline explorers, e identificar
> las copias no autorizadas del sitio.
>
> **Para qué NO sirve (honestidad):** nada publicado en un navegador puede ser 100%
> imposible de copiar. Un humano con capturas o un navegador headless (Puppeteer) puede
> obtener el HTML/CSS/JS/imágenes, como con cualquier web. La "protección total" no existe;
> lo que sí existe es **hacer que el robo sea inútil o muy caro**, y un **contrato de
> licencia** que lo prohíba.

## Las 3 capas implementadas

### 1. Trampa tarpit (`/site-archive`)
- Ruta `trap` con un controlador (`TrapController`) que devuelve una página basura
  autoreferenciada: texto aleatorio, productos y referencias inventadas, y **40 enlaces
  nuevos en cada petición** con `?d=N` recursivo. HTTrack/wget se comen infinitos niveles
  y espacio en disco con archivos inútiles.
- `Cache-Control: no-store` · `X-Robots-Tag: noindex`.
- **Jamás enlazada en la UI**: se llega a ella por (a) el enlace honeypot oculto en el
  `<body>` de todas las páginas y (b) las reglas de `.htaccess` que redirigen ahí los
  User-Agent de copiadoras conocidas.

### 2. Bloqueo de User-Agents de descargador en Apache (`.htaccess`)
GET/HEAD cuya cabecera User-Agent contenga `httrack|webcopier|wget|teleport|offline…`
se reenvía a `/index.php/site-archive` (basura 200, sin error para que la herramienta
siga mordiendo el anzuelo).

- **No afecta a Google/Bing**: sus User-Agents no coinciden con la lista.
- **No afecta a usuarios humanos.**
- Si en el futuro necesitas permitir una herramienta legítima (p.ej. tu propio monitor),
  recorta la lista en `.htaccess`.
- **Importante:** tu navegador, `curl` de soporte y PageSpeed pueden marcar como 200/trap
  si usan esos User-Agents. Usa `curl -A "Mozilla/5.0"` para pruebas.

### 3. Guarda anti-clonado por dominio (en `<head>` de todas las páginas)
Script inline que compara `window.location.hostname` con `ANTI_CLONE_ALLOWED_HOSTS`.
- En un dominio no autorizado, el contenido del `<body>` se **reemplaza por un aviso de
  "copia no autorizada"** (con enlace de contacto).
- localhost/127.0.0.1 siempre permitidos.
- Apagar: `ANTI_CLONE_GUARD=false` en `.env` (config `config/antidown.php`).
- **Antes de desplegar a producción:** pon tu dominio real en
  `ANTI_CLONE_ALLOWED_HOSTS` (p.ej. `midominio.com,www.midominio.com`). Si usas un
  subdominio de Hostinger provisional (`*.hostingersite.com`), inclúyelo o el sitio
  mostrará el aviso de copia no autorizada.
- Tras cambiar `.env` en producción: `php artisan config:clear`.

## Verificación local

```bash
# La trampa responde 200 y enlaza a sí misma infinitamente
curl -s http://127.0.0.1:8000/site-archive | head -40

# Simula un descargador con .htaccess (solo Apache; en php -S pasa por Laravel igual)
curl -s -I -A "HTTrack Website Copier/3.x" http://127.0.0.1:8000/

# La web normal carga con 200
curl -s -o /dev/null -w "%{http_code}\n" http://127.0.0.1:8000/
```

## Límites y qué falta (importante)

- **No impide** ver/copiar a mano el contenido público (HTML/CSS/JS/imágenes son públicos).
- **No impide** que un cliente con acceso FTP/SSH/cPanel copie `app/`, `database/`,
  `.env` y el volcado SQL. La única respuesta real ahí es: tú alojas, o contrato +
  confianza + solo cliente con su propio rincón.
- **No impide** Selenium/Puppeteer + capturas. Es un coste de todo sitio público.
- `ANTI_CLONE_ALLOWED_HOSTS` es una **deterrent de bajo esfuerzo** contra clones perezosos;
  no es DRM (y es trivial eludirla quitando 10 líneas del JS — aceptado a cambio de que
  funcione "out of the box").
- El **contenido protegible por derechos** (fotos, textos) solo lo protege la ley y el
  contrato; registra y firma.
