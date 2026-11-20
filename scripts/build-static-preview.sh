#!/bin/bash
# ============================================================
#  Costa Resin & Construction — Regenerar la vista previa estática de Vercel
# ============================================================
#  Genera `vercel-static/` rastreando la app Laravel local y deja
#  `vercel.json` apuntando a ese directorio.
#
#  Requisitos: php 8.2+, python3, curl. No usa MySQL ni la base de
#  datos de vista previa del usuario: crea una SQLite DESECHABLE en /tmp.
#
#  Uso:
#    scripts/build-static-preview.sh [BASE_URL]
#
#  BASE_URL (opcional): dominio absoluto final del sitio estático.
#  Por defecto: https://costaresin.vercel.app
#
#  IMPORTANTE: intercambia temporalmente DB_DATABASE en `.env` y lo
#  restaura SIEMPRE al terminar (trap en EXIT). Ver docs/VERCEL-PREVIEW.md
#  para las limitaciones de la vista previa y cómo eliminarla.
# ============================================================

set -euo pipefail
cd "$(dirname "$0")/.."

BASE_URL="${1:-https://costaresin.vercel.app}"
SNAPSHOT_DB="$(mktemp /tmp/costaresin-snapshot-XXXX.sqlite)"
ENV_BACKUP="$(mktemp /tmp/costaresin-env-XXXX.bak)"
PORT="${PORT:-8899}"
SERVER_PID=""

cleanup() {
  if [ -n "$SERVER_PID" ] && kill -0 "$SERVER_PID" 2>/dev/null; then
    kill "$SERVER_PID" 2>/dev/null || true
  fi
  cp "$ENV_BACKUP" .env
  rm -f "$SNAPSHOT_DB" "$ENV_BACKUP"
  echo "✔ .env restaurado ($(grep '^DB_DATABASE' .env | head -1))"
}
trap cleanup EXIT

echo "==> Respaldando .env y apuntando temporalmente a la SQLite desechable: $SNAPSHOT_DB"
cp .env "$ENV_BACKUP"
python3 - "$SNAPSHOT_DB" << 'EOF'
import sys, pathlib
p = pathlib.Path('.env')
target = sys.argv[1]
lines = p.read_text().splitlines()
with p.open('w') as f:
    for line in lines:
        if line.startswith('DB_DATABASE='):
            f.write(f'DB_DATABASE="{target}"\n')
        else:
            f.write(line + '\n')
EOF

echo "==> Migrando esquema en la desechable…"
php artisan migrate --force > /dev/null

echo "==> Preparando categorías del catálogo (las que los seeders no crean)…"
php artisan tinker --execute='
\App\Models\Category::where("slug", "resinas-poliuretano")->update(["slug" => "resinas"]);
foreach ([["Imprimaciones","imprimaciones",10],["Protección","proteccion",11],["Limpieza","limpieza",12]] as [$n,$s,$o]) {
  if (!\App\Models\Category::where("slug", $s)->exists()) {
    \App\Models\Category::create(["name" => $n, "slug" => $s, "sort_order" => $o]);
  }
}' > /dev/null

echo "==> Sembrando catálogo, páginas legales y contenido…"
php artisan db:seed --class=ProductSeeder > /dev/null
php artisan db:seed --class=ColorSeeder > /dev/null
php artisan db:seed --class=PageSeeder > /dev/null
php artisan db:seed --class=SettingsSeeder > /dev/null
php artisan db:seed --class=ContentBlockSeeder > /dev/null
php artisan db:seed --class=PopulateContentBlocksSeeder > /dev/null

echo "==> Limpiando cachés compartidas…"
php artisan cache:clear > /dev/null || true
php artisan view:clear > /dev/null || true

echo "==> Arrancando servidor de vista previa en 127.0.0.1:$PORT…"
php artisan serve --port="$PORT" --host=127.0.0.1 > /tmp/costaresin-preview-serve.log 2>&1 &
SERVER_PID=$!
sleep 4
curl -fsS -o /dev/null "http://127.0.0.1:$PORT/" || { echo "✖ El servidor no responde"; exit 1; }

echo "==> Rastreando páginas públicas del sitemap…"
rm -rf vercel-static
python3 - "$PORT" "$BASE_URL" << 'EOF'
import re, os, pathlib, shutil, sys, urllib.request

PORT, BASE_URL = sys.argv[1], sys.argv[2].rstrip('/')
ORIGIN = f'http://127.0.0.1:{PORT}'
ROOT = pathlib.Path('.')
OUT = ROOT / 'vercel-static'
PUBLIC = ROOT / 'public'
STORAGE = ROOT / 'storage' / 'app' / 'public'

sitemap = (ROOT / 'sitemap.xml').read_text(errors='ignore')
urls = re.findall(r'<loc>([^<]+)</loc>', sitemap)
paths = []
for u in urls:
    for o in ('http://localhost:8000', 'http://127.0.0.1:8000', 'https://localhost:8000'):
        if u.startswith(o):
            u = u[len(o):] or '/'
            break
    paths.append(u or '/')

def fetch(path):
    req = urllib.request.Request(ORIGIN + path, headers={'User-Agent': 'vercel-static-crawl/1.0'})
    with urllib.request.urlopen(req, timeout=60) as r:
        return r.read()

saved = []
failed = []
for p in paths:
    try:
        body = fetch(p)
    except Exception as e:
        failed.append(p)
        print('  FAIL', p, e)
        continue
    dest = OUT / 'index.html' if p == '/' else OUT / (p.strip('/') + '.html')
    dest.parent.mkdir(parents=True, exist_ok=True)
    dest.write_bytes(body)
    saved.append(p)

# Livewire runtime (dynamic route, static content)
(OUT / 'livewire').mkdir(exist_ok=True)
(OUT / 'livewire' / 'livewire.js').write_bytes(fetch('/livewire/livewire.js'))

# Núcleo de assets siempre presente
for d in ('build', 'css', 'js', 'images', 'videos'):
    src = PUBLIC / d
    if src.is_dir():
        shutil.copytree(src, OUT / d, dirs_exist_ok=True)

# Assets de storage referenciados (og-image y placeholder JSON-LD)
(OUT / 'storage' / 'site').mkdir(parents=True, exist_ok=True)
for name in ('og-default.jpg',):
    src = STORAGE / 'site' / name
    if src.is_file():
        shutil.copy2(src, OUT / 'storage' / 'site' / name)
        shutil.copy2(src, OUT / 'storage' / 'site' / 'placeholder.jpg')

# Reescribir orígenes locales -> dominio final
origin_re = re.compile(r'https?://(127\.0\.0\.1|localhost):(8000|8899|' + PORT + r')')
for f in OUT.rglob('*'):
    if f.is_file() and f.suffix.lower() in ('.html', '.xml', '.txt', '.js', '.css'):
        s = f.read_text(errors='ignore')
        s2 = origin_re.sub(BASE_URL, s)
        # Enlaces internos históricos /legal/cookie-policy -> /legal/cookies
        s2 = s2.replace('\\/legal\\/cookie-policy', '\\/legal\\/cookies').replace('legal\\/cookie-policy', 'legal/cookies').replace('/legal/cookie-policy', '/legal/cookies')
        if s2 != s:
            f.write_text(s2)

# Sitemap recortado a las páginas que realmente existen
src = (ROOT / 'sitemap.xml').read_text(errors='ignore')
block = re.findall(r'\s*<url>.*?</url>', src, re.S)
keep = [u for u in block if not any(f.strip('/') in u for f in failed)]
head = src[:src.find('<url>')]
(OUT / 'sitemap.xml').write_text(origin_re.sub(BASE_URL, head) + ''.join(origin_re.sub(BASE_URL, u) for u in keep) + '</urlset>\n')
(OUT / 'robots.txt').write_bytes((ROOT / 'robots.txt').read_bytes())

print(f'  OK: {len(saved)} páginas · fallidas: {len(failed)}')
EOF

# 404 simple y autocontenido
cat > vercel-static/404.html << 'HTMLEOF'
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>404 — Página no encontrada | Costa Resin &amp; Construction</title>
<link rel="icon" href="/images/favicon.png">
<style>body{margin:0;font-family:Inter,system-ui,sans-serif;background:#0f172a;color:#fff;display:flex;min-height:100vh;align-items:center;justify-content:center;text-align:center}a{color:#f59e0b;text-decoration:none;font-weight:700}img{max-width:180px;margin-bottom:1.5rem}</style>
</head>
<body>
<main>
<img src="/images/logo-white.png" alt="Costa Resin &amp; Construction" onerror="this.style.display='none'">
<h1>404 — Página no encontrada</h1>
<p>La página que buscas no existe o ha sido movida.</p>
<p><a href="/">← Volver al inicio</a> · <a href="/tienda">Ver la tienda</a> · <a href="/contacto">Contacto</a></p>
</main>
</body>
</html>
HTMLEOF

echo "✔ vercel-static/ regenerado ($(du -sh vercel-static | cut -f1)). .env se restaurará ahora."
