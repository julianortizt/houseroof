# 🖥️ Guía de Desarrollo Local - Costa Resin & Construction

## ⚠️ Problema conocido en Windows

PHP built-in server (`php -S`) en Windows **no carga la extensión openssl** cuando genera peticiones HTTP. Esto impide que Laravel arranque porque necesita openssl para cifrar sesiones/cookies.

**Esto NO afecta a Hostinger/Linux** — en producción todo funciona perfecto.

---

## ✅ Solución 1: Laragon (RECOMENDADO — más fácil)

### Paso 1: Descargar Laragon
1. Ve a **https://laragon.org/download/**
2. Descarga **Lite** (gratis, ~5MB)
3. Ejecuta el instalador → instálalo en `C:\laragon`

### Paso 2: Configurar PHP
1. Abre Laragon
2. Click derecho en el ícono → **PHP** → **Version** → selecciona **8.2**
3. Click derecho → **PHP** → **Extensions** → marca: `openssl`, `curl`, `pdo_mysql`, `mbstring`, `bcmath`, `gd`, `intl`, `exif`, `fileinfo`, `dom`, `xml`

### Paso 3: Ejecutar Laravel
1. Click derecho → **Terminal** (abre una terminal en `C:\laragon\www`)
2. Navega al proyecto:
   ```bash
   cd C:\Users\Julian\Desktop\costa-resin-website
   ```
3. Inicia el servidor:
   ```bash
   C:\laragon\bin\php\php-8.2.x\php.exe artisan serve --host=127.0.0.1 --port=8000
   ```

### Paso 4: Abrir en navegador
- **Sitio:** http://localhost:8000
- **Admin:** http://localhost:8000/admin
- **Login:** admin@costaresin.local / costaresin-admin-2026!

---

## ✅ Solución 2: Laragon con Apache (mejor rendimiento)

Si prefieres Apache en vez del built-in server:

1. En Laragon, click derecho → **WWW Directory**
2. Copia la carpeta `costa-resin-website` dentro de `C:\laragon\www\`
3. Click derecho → **Laravel** → **laravel new**
4. En la terminal:
   ```bash
   cd C:\laragon\www\costa-resin-website
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan storage:link
   php artisan serve
   ```

---

## ✅ Solución 3: Docker (si tienes Docker Desktop)

```bash
cd C:\Users\Julian\Desktop\costa-resin-website

# Crear docker-compose.yml
cat > docker-compose.yml << 'EOF'
version: '3.8'
services:
  app:
    image: php:8.2-cli
    working_dir: /var/www
    volumes:
      - .:/var/www
    ports:
      - "8000:8000"
    command: bash -c "composer install && php artisan migrate:fresh --seed && php artisan serve --host=0.0.0.0 --port=8000"
    depends_on:
      - db
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: costaresin_db
      MYSQL_USER: costaresin_user
      MYSQL_PASSWORD: costaresin2026!MySQL
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql
volumes:
  db_data:
EOF

docker-compose up
```

---

## ✅ Solución 4: XAMPP (clásico)

1. Descarga XAMPP desde https://www.apachefriends.org
2. Selecciona PHP 8.2
3. Instálalo en `C:\xampp`
4. En el panel de XAMPP, inicia **Apache** y **MySQL**
5. Copia `costa-resin-website` dentro de `C:\xampp\htdocs\`
6. Abre http://localhost/costa-resin-website/public

---

## 🔧 Configuración después de instalar

Independientemente de la solución elegida:

```bash
cd al directorio del proyecto

# 1. Dependencias
composer install

# 2. Configurar .env
cp .env.example .env
php artisan key:generate

# 3. Base de datos (asegúrate de que MySQL esté corriendo)
php artisan migrate:fresh --seed

# 4. Storage link
php artisan storage:link

# 5. Limpiar caches
php artisan view:clear
php artisan config:clear
php artisan route:clear

# 6. Iniciar servidor
php artisan serve
```

---

## 🔑 Credenciales

| Servicio | Usuario | Contraseña |
|----------|---------|------------|
| **Admin Panel** | admin@costaresin.local | password |
| **MySQL** | costaresin_user | costaresin2026!MySQL |
| **MySQL Root** | root | (vacía o costaresin2026!MySQL) |

---

## 📋 URLs del sitio

| URL | Descripción |
|-----|-------------|
| http://localhost:8000 | Página de inicio |
| http://localhost:8000/tienda | Catálogo de productos |
| http://localhost:8000/blog | Blog |
| http://localhost:8000/proyectos | Galería de proyectos |
| http://localhost:8000/servicios | Servicios |
| http://localhost:8000/contacto | Contacto |
| http://localhost:8000/admin | Panel de administración |
| http://localhost:8000/sitemap.xml | Sitemap XML |

---

## 🐛 Solución de problemas

### "openssl_cipher_iv_length" no funciona
- **Causa:** PHP built-in server en Windows
- **Solución:** Usar Laragon o XAMPP (Apache + mod_php)

### "No such file or directory: public/css/app.css"
- **Solución:** `npm install && npm run build`

### MySQL connection refused
- **Solución:** Iniciar MySQL desde XAMPP/Laragon o verificar `.env`

### "Route not found" para rutas de redirección
- **Solución:** `php artisan route:clear`
