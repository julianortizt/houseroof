# 🚀 Guía Visual de Instalación - Costa Resin & Construction en Hostinger

## 📋 Resumen rápido

```
Paso 1: Crear base de datos        ⏱️ 2 minutos
Paso 2: Subir archivos             ⏱️ 5 minutos
Paso 3: Conectar por SSH           ⏱️ 1 minuto
Paso 4: Ejecutar instalador        ⏱️ 3 minutos
Paso 5: Configurar dominio         ⏱️ 1 minuto
Paso 6: Activar SSL                ⏱️ 1 minuto
────────────────────────────────────────────
TOTAL:                             ⏱️ ~15 minutos
```

---

## 🖥️ MÉTODO 0 — Instalador Visual (Recomendado, sin SSH)

Este proyecto incluye un **instalador web** accesible desde el navegador. No
necesitas usar la terminal: subes los archivos y abres `https://tudominio.com/install`.

```
Paso 1: Subir archivos a /public_html          ⏱️ 5 minutos
Paso 2: Apuntar el dominio a /public_html/public
Paso 3: Abrir https://tudominio.com/install     ⏱️ 5 minutos
Paso 4: Activar SSL                            ⏱️ 1 minuto
TOTAL:                                         ⏱️ ~12 minutos
```

### Qué hace el instalador
1. **Requisitos:** verifica PHP 8.2+, extensiones y permisos de escritura.
2. **Base de datos:** pide host/puerto/nombre/usuario/contraseña y prueba la conexión en el acto.
3. **Sitio:** nombre, URL, email y contraseña del administrador, y SMTP (opcional).
4. **Instalación:** escribe el `.env`, genera `APP_KEY`, ejecuta migraciones y seeders
   (roles, ajustes, páginas legales, categorías y colores), crea el administrador,
   crea el `storage:link` y genera las cachés de producción.
5. **Bloqueo:** crea el archivo `.installed` en la raíz; el instalador deja de ser accesible.
   Para volver a instalar, borra `.installed`.

> El instalador crea un `.env` con una `APP_KEY` válida automáticamente si no existe,
> por lo que funciona incluso en una subida totalmente limpia.

### Pasos
1. Sube **todo** el proyecto a `/home/usuario/public_html` (el dominio debe apuntar a `/public_html/public`).
2. Abre en el navegador: `https://tudominio.com/install`
3. Sigue el asistente (Requisitos → Base de datos → Sitio → Confirmar → Instalar).
4. Al terminar, activa SSL en el panel y entra al panel en `/admin`.

---

## 📦 Archivos que necesitas

| Archivo | Ubicación | Tamaño |
|---------|-----------|--------|
| `costa-resin-upload.zip` | `C:/Users/Julian/Desktop/` | 94 MB |
| `install-hostinger.sh` | Dentro del ZIP | - |
| `database.sql` (backup) | `C:/Users/Julian/Desktop/costa-resin-backup-*/` | 143 KB |

---

## 🔑 Credenciales del sistema

### Usuarios administradores
| Rol | Email | Contraseña |
|-----|-------|------------|
| Super Admin | admin@TU_DOMINIO.com | **la que defina el instalador; no reutilizar** |
| Development | dev@TU_DOMINIO.com | **definir en el despliegue** |
| Manager | manager@TU_DOMINIO.com | **definir en el despliegue** |
| Site Admin | siteadmin@TU_DOMINIO.com | **definir en el despliegue** |

⚠️ Las credenciales del sitio anterior (Fortis) fueron retiradas del repositorio por seguridad.
Genera contraseñas nuevas en el primer despliegue y guárdalas en un gestor seguro.

### Datos de empresa
| Campo | Valor |
|-------|-------|
| Empresa | Costa Resin & Construction |
| Dirección | PENDIENTE CONFIRMAR POR EL TITULAR |
| Teléfono | PENDIENTE CONFIRMAR POR EL TITULAR |
| Email | info@TU_DOMINIO.com (confirmar) |
| WhatsApp | PENDIENTE CONFIRMAR POR EL TITULAR |

---

## 📖 PASO A PASO DETALLADO

---

### PASO 1: Crear Base de Datos MySQL

```
┌─────────────────────────────────────────────────────────┐
│  Panel de Hostinger → Bases de datos → MySQL            │
└─────────────────────────────────────────────────────────┘
```

1. **Ve a:** Panel de Hostinger → **Bases de datos** → **MySQL**

2. **Click en:** "Crear base de datos"

3. **Ingresa:**
   ```
   Nombre de BD:    costaresin_db
   Usuario de BD:   costaresin_user
   Contraseña:      [Genera una fuerte y guárdala]
   ```

4. **Click:** "Crear"

5. **GUARDA ESTAS CREDENCIALES:**
   ```
   Host:     localhost (o 127.0.0.1)
   DB Name:  u123456789_costaresin_db  (tu usuario + _costaresin_db)
   User:     u123456789_costaresin_user
   Password: [la que generaste]
   ```

⚠️ **IMPORTANTE:** Anota estas credenciales, las necesitarás en el Paso 4.

---

### PASO 2: Subir Archivos a Hostinger

```
┌─────────────────────────────────────────────────────────┐
│  Panel de Hostinger → Archivos → Administrador archivos │
└─────────────────────────────────────────────────────────┘
```

1. **Ve a:** Panel de Hostinger → **Archivos** → **Administrador de archivos**

2. **Navega a:** `/public_html/`

3. **Sube el archivo:**
   - Click en **"Subir"** (icono de nube arriba)
   - Selecciona: `C:/Users/Julian/Desktop/costa-resin-upload.zip`
   - Espera a que termine (94 MB ≈ 1-3 minutos)

4. **Extrae el ZIP:**
   - Click derecho sobre `costa-resin-upload.zip`
   - Selecciona **"Extraer"**
   - Confirma la ruta: `/public_html/`
   - Click **"Extraer"**

5. **Verifica:** Debes ver las carpetas `app/`, `config/`, `public/`, etc. dentro de `/public_html/`

⚠️ **IMPORTANTE:** La estructura debe ser:
```
/public_html/
├── app/
├── bootstrap/
├── config/
├── database/
├── install-hostinger.sh  ← ¡Este script!
├── public/
├── resources/
├── routes/
├── storage/
└── ...
```

---

### PASO 3: Conectar por SSH

```
┌─────────────────────────────────────────────────────────┐
│  Panel de Hostinger → Avanzado → SSH Access             │
└─────────────────────────────────────────────────────────┘
```

1. **Ve a:** Panel de Hostinger → **Avanzado** → **SSH Access**

2. **Habilita SSH** si no está activo

3. **Abre tu terminal** (PowerShell, CMD, o Git Bash)

4. **Conéctate:**
   ```bash
   ssh u123456789@tudominio.com
   ```
   (Reemplaza `u123456789` con tu usuario y `tudominio.com` con tu dominio)

5. **Ingresa tu contraseña** de SSH cuando te la pida

6. **Navega al directorio:**
   ```bash
   cd ~/public_html
   ```

---

### PASO 4: Ejecutar el Instalador

```
┌─────────────────────────────────────────────────────────┐
│  Terminal SSH → Instalador automático                   │
└─────────────────────────────────────────────────────────┘
```

1. **Da permisos al instalador:**
   ```bash
   chmod +x install-hostinger.sh
   ```

2. **Ejecuta el instalador:**
   ```bash
   ./install-hostinger.sh
   ```

3. **El instalador te pedirá:**

   ```
   ━━━ Paso 2/8 Configuración del sitio ━━━

   Dominio (ej: TU_DOMINIO.com): TU_DOMINIO.com
   Usuario SSH (ej: u123456789): u123456789
   Directorio público [~/public_html]: ~/public_html

   Base de datos:
   Nombre de la base de datos: u123456789_costaresin_db
   Usuario de la base de datos: u123456789_costaresin_user
   Contraseña de la base de datos: [tu contraseña]

   Configuración de correo:
   Email SMTP (ej: info@TU_DOMINIO.com): info@TU_DOMINIO.com
   Contraseña SMTP: [contraseña del email]

   Asistente IA (opcional):
   Groq API Key (Enter para omitir): [tu API key o Enter]
   ```

4. **Confirma:** Escribe `s` y presiona Enter

5. **Espera** a que termine (~2-3 minutos)

6. **Verás el resumen:**
   ```
   ╔══════════════════════════════════════════════════════════╗
   ║   ✅ INSTALACIÓN COMPLETADA                              ║
   ╚══════════════════════════════════════════════════════════╝

   🌐 Tu sitio web:    https://TU_DOMINIO.com
   🔧 Panel admin:      https://TU_DOMINIO.com/admin
   ```

---

### PASO 5: Configurar Directorio del Dominio

```
┌─────────────────────────────────────────────────────────┐
│  Panel de Hostinger → Dominios → Administrar            │
└─────────────────────────────────────────────────────────┘
```

1. **Ve a:** Panel de Hostinger → **Dominios** → **Administrar**

2. **Selecciona tu dominio**

3. **Busca:** "Directorio raíz" o "Document Root"

4. **Cambia a:**
   ```
   /home/u123456789/public_html/public
   ```

5. **Guarda** los cambios

⚠️ **IMPORTANTE:** Sin este paso, el dominio mostrará los archivos del proyecto en lugar del sitio web.

---

### PASO 6: Activar SSL (HTTPS)

```
┌─────────────────────────────────────────────────────────┐
│  Panel de Hostinger → Dominios → SSL                    │
└─────────────────────────────────────────────────────────┘
```

1. **Ve a:** Panel de Hostinger → **Dominios** → **SSL**

2. **Selecciona tu dominio**

3. **Click:** "Instalar SSL" o "Habilitar"

4. **Tipo:** Let's Encrypt (gratis)

5. **Espera** ~1-2 minutos a que se active

6. **Verifica:** Abre `https://tudominio.com` en tu navegador

---

## ✅ VERIFICACIÓN POST-INSTALACIÓN

### Prueba estas URLs:
```
✅ https://tudominio.com/                 → Página principal
✅ https://tudominio.com/about-us         → Sobre nosotros
✅ https://tudominio.com/tienda           → Tienda
✅ https://tudominio.com/proyectos        → Proyectos
✅ https://tudominio.com/admin            → Panel de administración
✅ https://tudominio.com/legal/privacy-policy → Política de privacidad
```

### Login del admin:
```
URL:      https://tudominio.com/admin
Email:    admin@TU_DOMINIO.com
Password: la definida en el despliegue
```

⚠️ **CAMBIA LA CONTRASEÑA** después del primer login.

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Error 500
```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Imágenes no se ven
```bash
php artisan storage:link --force
chmod -R 775 storage/
```

### Página blanca
```bash
# Verificar .env
cat .env | head -20

# Verificar permisos
ls -la storage/
ls -la bootstrap/cache/
```

### Login no funciona
```bash
# Regenerar APP_KEY
php artisan key:generate --force

# Limpiar sesiones
php artisan session:clear
```

---

## 📞 SOPORTE

Si tienes problemas, verifica:
1. ✅ PHP 8.2+ está activo
2. ✅ Extensiones: curl, mbstring, openssl, pdo_mysql
3. ✅ El dominio apunta a `/public_html/public`
4. ✅ SSL está activo
5. ✅ El archivo `.env` tiene las credenciales correctas

---

**Costa Resin & Construction** © 2026
