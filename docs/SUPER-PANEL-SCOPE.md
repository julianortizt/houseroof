# Alcance del Super Panel — Fortis Resin Systems

- **Fecha:** 2026-11-15
- **Ámbito:** verificación del inventario, cobertura y separación del Super Panel (`/super-panel`) frente al panel administrativo normal (`/admin`).
- **Método:** inspección estática de código (sin ejecutar cambios). Última verificación de línea citada: `archivo:línea` según el estado del repositorio en la fecha indicada.
- **Estado base de pruebas:** `php artisan test 2>&1 | tail -3` → **127 passed (727 assertions)** (no 121; la cifra documentada en PENDIENTES.md §"Estado validado" quedó desactualizada).
- **Documento relacionado:** ítem abierto en `PENDIENTES.md` ("Comprobar el alcance del Super Panel…").

---

## 1. Cobertura actual

### 1.1 Qué es el Super Panel hoy

Un segundo panel Filament 3 que **reutiliza casi toda la superficie del panel normal** y añade herramientas exclusivas del desarrollador. No es un panel "mínimo y aislado": es un **superconjunto** (24 recursos) del panel normal menos la contabilidad legal (27 recursos).

| Aspecto | `/admin` (normal) | `/super-panel` (Super Panel) |
|---|---|---|
| Provider | `app/Providers/Filament/AdminPanelProvider.php:21` (`->default()` línea 26, `id('admin')` línea 27, `path('admin')` línea 28) | `app/Providers/Filament/SuperPanelProvider.php:21` (`id('super-panel')` línea 26, `path('super-panel')` línea 27) |
| Marca | `config('platform.name')` (config/platform.php:4), color Amber | `'SUPER PANEL · Control de plataforma'` (SuperPanelProvider.php:30), color Cyan (línea 31) |
| Recursos | 27 (incluye `AccountingAccountResource`, `JournalEntryResource`, `InvoiceResource`) | 24 (**idénticos salvo los 3 anteriores**) — SuperPanelProvider.php:32-57 |
| Páginas descubiertas | mismas 13 clases en ambos (`discoverPages`, SuperPanelProvider.php:58 / AdminPanelProvider.php:64) | idem |
| Widgets | `AccountWidget` | `AccountWidget` (igual) |
| Login | sí | sí (SuperPanelProvider.php:28) |
| En mantenimiento | excluido de 503 (bootstrap/app.php:31-41) | excluido de 503 (`'super-panel'`, `'super-panel/*'`, bootstrap/app.php:38-39) |
| Grupo de navegación exclusivo | no | `'Control exclusivo del desarrollador'` (SuperPanelProvider.php:86) |
| Enlace rápido en topbar | no | sí → `super-panel-actions` (SuperPanelProvider.php:88-91) → solo a Apariencia y Módulos |

Recursos exclusivos de `/admin` ausentes en Super Panel: `AccountingAccountResource`, `JournalEntryResource`, `InvoiceResource` (contabilidad/facturación formal). No existe ningún recurso **exclusivo** del Super Panel; toda su "exclusividad" está en páginas.

### 1.2 Páginas (13 clases descubiertas en ambos paneles) y estado de guardia

| Página | Ruta | Guardia `canAccess()` | Resultado |
|---|---|---|---|
| `SuperPanel\PlatformModules` | `/super-panel/modules` | sí — solo panel `super-panel` **y** email === `platform.developer_email` (app/Filament/Pages/SuperPanel/PlatformModules.php:26-33) | **Exclusiva del desarrollador** en ambos paneles (en `/admin` → 403) |
| `SuperPanel\PlatformAppearance` | `/super-panel/appearance` | sí — idem (app/Filament/Pages/SuperPanel/PlatformAppearance.php:33-40) | Exclusiva del desarrollador |
| `StripeSettings` | `/super-panel/stripe-settings` | sí — idem, vía `auth('web')` (app/Filament/Pages/StripeSettings.php:28-35) | Exclusiva del desarrollador. Guardado además re-verificado en `StripeCredentials::save()` — `abort_unless(... developer_email ...)` (app/Services/StripeCredentials.php:106-111) |
| `FiscalSettings` | `fiscal-settings` (ambos paneles) | sí — `super-panel`: developer; `admin`: rol `super_admin`/`admin`; **+ módulo `invoicing` activo** (app/Filament/Pages/FiscalSettings.php:30-44); guardado con `abort_unless` (línea 93) | Compartida con roles altos del admin |
| `Accounting\TrialBalance` | `accounting/trial-balance` (ambos, pero ruta solo si se descubre) | parcial — solo exige bandera `accounting`; **no** comprueba permiso `manage accounting` ni rol (app/Filament/Pages/Accounting/TrialBalance.php:22-24) | Brecha (ver §3) |
| `Settings` | `settings` (ambos) | **ninguna** | Expuesta a cualquier rol con acceso al panel |
| `MaintenanceMode` | `maintenance-mode` (ambos) | **ninguna** | Expuesta a cualquier rol con acceso al panel |
| `AiAssistant` | `ai-assistant` (ambos) | **ninguna** | Expuesta a cualquier rol con acceso al panel |
| `EditHome` / `EditAbout` / `EditContact` / `EditServices` | `edit-home`, `edit-about`, `edit-contact`, `edit-services` | ninguna | Contenido del sitio público editable por cualquier rol con acceso al panel |
| `LanguageSwitcher` | `language` | ninguna (según diseño; solo cambia sesión de idioma) | Riesgo bajo |

Además: `Pages\Dashboard` (dashboard genérico) en ambos paneles.

Mecanismo de guardia efectivo de Filament 3 verificado en vendor: las páginas heredan `mountCanAuthorizeAccess()`/`hydrateCanAuthorizeAccess()` → `abort_unless(static::canAccess(), 403)` (vendor/filament/filament/src/Pages/Concerns/CanAuthorizeAccess.php:7-15), invocados por el hook de rasgos de Livewire en mount/hydrate (vendor/livewire/livewire/src/Features/SupportLifecycleHooks/SupportLifecycleHooks.php:13-38, `callTraitHook('mount'|'hydrate')` línea 152-161). **Nota de orden:** el `mount()` propio de la página se ejecuta **antes** que `mountCanAuthorizeAccess()` (callHook('mount') precede a callTraitHook('mount'), SupportLifecycleHooks.php:18-22). Ninguna página lee secretos hacia la respuesta HTTP dentro de `mount()`, pero el patrón "mount sin abort + dependencia del hook del framework" es frágil (ver B-09).

### 1.3 Herramientas exclusivas del desarrollador (lo que el Super Panel *debería* cubrir)

Cubierto hoy en el Super Panel:

1. **Gestión de módulos** (10 banderas + dependencias + auditoría): `PlatformModules` (página) → `App\Services\PlatformModules` (app/Services/PlatformModules.php). Dependencias impuestas (work_orders/invoicing→quotes, accounting→invoicing, payments→quotes) con transacción y validación. Auditado vía `AuditLogger::recordModuleChanges()` (app/Services/AuditLogger.php:29). ✔
2. **Apariencia** (4 estilos, 5 plantillas de portada): `PlatformAppearance` → settings `platform_style` / `homepage_template`. ✔ **Sin auditoría** (ver B-06).
3. **Credenciales Stripe cifradas** (pk/sk/webhook, `APP_KEY`, "dejar vacío = conservar", confirmación fiscal obligatoria, bloqueo por módulos): `StripeSettings` → `App\Services\StripeCredentials::save()` (cifrado en DB, `Crypt::encryptString`, StripeCredentials.php:170-193; auditoría `recordStripeConfigurationChange`, línea 214-220 aprox.). ✔
4. **Configuración fiscal**: `FiscalSettings` (compartida con `/admin`; identidad legal + `invoicing` module gate; auditoría `recordFiscalProfileChange` en `FiscalProfile`). ✔ Parcial: visible también para `super_admin`/`admin` en `/admin` (deliberado, §1.2).
5. **Bitácora de administración**: compartida vía `AuditLogResource` (solo lectura real: `canCreate/Edit/Delete = false` — app/Filament/Resources/AuditLogResource.php y su módulo `administration` vía `RequiresPlatformModule`). Registros actuales: módulos, Stripe, perfil fiscal, cambios de roles, valoración de inventario, pagos/cobros. **No cubre apariencia ni `Settings`.** ◐ Parcial.
6. **Usuarios/roles con protecciones**: compartida vía `UserResource` — protege cuenta del desarrollador, super_admin y último superadministrador (app/Filament/Resources/UserResource.php:237-279); `UserAdministrationSecurityTest` cubre estas reglas. Asignación de `super_admin` solo por el desarrollador (líneas 196-201, 282-287). ✔ Compartida (correcto), pero ver B-11.

No cubierto / ausente (lo que un desarrollador esperaría de un "panel de plataforma"):

7. **Health/readiness de la plataforma desde el panel** — solo existen como CLI (`php artisan production:health-check`, app/Console/Commands/ProductionHealthCheckCommand.php) y endpoint público `/health` (routes/web.php:198-212). No hay vista en Super Panel.
8. **Gestión visual de roles/permisos** — se hace por seeder (`database/seeders/RolesAndPermissionsSeeder.php`), no desde UI; PENDIENTES.md ya lo reconoce ("falta … gestión visual completa de roles").
9. **Visibilidad de configuración efectiva en runtime** (qué valores vienen de DB vs `.env`) — no existe.
10. **Cola/trabajos programados, cachés, storage** — sin pantalla (aceptable en hosting compartido; ver B-08).
11. **Actualización/deploy o respaldos** — fuera de alcance deliberado (hosting Hostinger); solo documentado en DEPLOY-HOSTINGER.md.
12. **2FA / reautenticación para acciones destructivas** — inexistente en ambos paneles.

### 1.4 Seguridad estructural verificada (separación real)

- **Acceso al panel:** `User::canAccessPanel()` — `super-panel`: `filled(developer_email) && email === developer_email`; `admin`: roles `super_admin|admin|editor|manager|site_admin` (app/Models/User.php:40-49).
- **Middleware de autenticación:** ambos paneles solo `Authenticate::class` (SuperPanelProvider.php:77; AdminPanelProvider.php:83-85) → la separación depende **exclusivamente** de `canAccessPanel()` + `canAccess()` por página. No hay guard separada, ni 2FA, ni IP allowlist, ni middleware propio (`EnsureDeveloper` no existe — comprobado por búsqueda global de `super_admin|EnsureDeveloper|developer_email|super-panel` en `app/ routes/ config/ database/`).
- **Páginas exclusivas:** las 3 páginas developer-only implementan `canAccess()` que exige `Filament::getCurrentPanel()?->getId() === 'super-panel'` **y** email exacto (PlatformModules.php:26-33, PlatformAppearance.php:33-40, StripeSettings.php:28-35). En `/admin` devuelven 403 aunque `developer_email` coincida (por el check de panel id). ✔ Bien diseñado.
- **Flags de módulos compartidos:** recursos usan `RequiresPlatformModule` (app/Filament/Resources/Concerns/RequiresPlatformModule.php:60-141) — mismas reglas en ambos paneles. ✔
- **Admin roles no-desarrolladores** (`super_admin` no-dev, p. ej. cuentas "Paul Noble"/"Cristian Quiñonez" según ADMIN-GUIDE.md:59) **no pueden entrar al Super Panel**, aunque sí `/admin`. Deliberado, pero implica **bus factor = 1** (ver B-11).
- **`/super-panel` excluido del modo mantenimiento** junto con `/admin` y `/livewire` (bootstrap/app.php:35-41). ✔ Deliberado; `'livewire'` global es amplio (ver B-10).

---

## 2. Separación del panel normal — verificación de acceso

| Control | Mecanismo | Archivo:línea |
|---|---|---|
| Quién puede autenticarse en `/super-panel` | `canAccessPanel('super-panel')` → email === developer_email | app/Models/User.php:42-46 |
| Middleware que lo hace cumplir | `Authenticate::class` → abort 403 si `!canAccessPanel(panel)` | app/Providers/Filament/SuperPanelProvider.php:77 ; vendor/filament/filament/src/Http/Middleware/Authenticate.php:33-38 |
| Def-fail del email del desarrollador | `env('SUPER_ADMIN_EMAIL') ?: 'julianortizt@gmail.com'` | config/platform.php:6 |
| Guardia por página (exclusive) | `canAccess()` ×3 (ver §1.2) | PlatformModules.php:26-33 · PlatformAppearance.php:33-40 · StripeSettings.php:28-35 |
| Guardia de framework en mount/hydrate | `abort_unless(canAccess(),403)` | CanAuthorizeAccess.php:7-15 |
| Defensa en profundidad en guardado | `StripeCredentials::save()` re-aborta | StripeCredentials.php:106-111 |
| Middleware que protege rutas admin/panel restantes | `admin.user` → `EnsureAdministrativeUser` (registrado en bootstrap/app.php:49) | app/Http/Middleware/EnsureAdministrativeUser.php:10-20 |
| Config fail-closed para páginas | páginas sin `canAccess()` = abierto a **cualquier** usuario del panel | — |

**Conclusión de separación:** la separación es **real y consistente en tres capas** (panel login → canAccess por página → re-check en servicio de Stripe). El punto débil no son las páginas exclusivas, sino (a) el perímetro de identidad (un solo email, factor único, fallback hardcodeado), y (b) páginas compartidas sin guardia que difuminan la frontera entre "panel del personal" y "herramientas del sitio".

---

## 3. Brechas detectadas

> Severidades: **ALTA** = exposición de secreto/integridad/disponibilidad explotando hoy o configurando mal un entorno; **MEDIA** = riesgo real con mitigación parcial o probabilidad moderada; **BAJA** = higiene/arquitectura.

### B-01 · ALTA · `Settings` legible/escribible por cualquier rol con acceso a `/admin`, incluye la clave de IA
`app/Filament/Pages/Settings.php:17` — la página **no define `canAccess()`**. Campos sensibles: `ai_api_key` (rellenado línea 98, TextInput password revealable línea 435), más SEO, GDPR, WhatsApp, colores, sliders, nombre/email del sitio. `save()` (línea 467) no re-autoriza. Cualquier `editor`/`manager`/`site_admin` (canAccessPanel, User.php:48) puede leer con el "reveal" y rotar la API key del proveedor de IA, o romper la config global del sitio.
**Recomendación:** `canAccess()` restringido a `super_admin|admin` (o `manage settings`/`manage site_settings`, permisos que existen pero nadie usa); mover `ai_api_key` a pantalla tipo StripeSettings (solo-desarrollador) o enmascararlo server-side; auditar `Settings::save()`.

### B-02 · ALTA · Fallback hardcodeado del email del desarrollador en config de producción
`config/platform.php:6` — `env('SUPER_ADMIN_EMAIL') ?: 'julianortizt@gmail.com'`. Si `SUPER_ADMIN_EMAIL` falta/está vacío en el entorno de hosting (Hostinger, ver GUIA-INSTALACION-HOSTINGER.md), el control total del Super Panel recae sobre una dirección personal horneada en el repo (que además es pública en el historial/código). No es "fallar cerrado": es fallar **hacia un valor por defecto conocido**.
**Recomendación:** fail-closed (`?: null` + `canAccessPanel` ya aborta con `filled()` → nadie entra), y documentar `SUPER_ADMIN_EMAIL` como variable obligatoria en la guía de instalación. Adicionalmente, log/alerta si el fallback está activo.

### B-03 · ALTA · Credenciales en texto plano en el repositorio
`GUIA-INSTALACION-HOSTINGER.md:67` y `GUIA-INSTALACION-HOSTINGER.md:310` contienen una contraseña de Super Admin en claro (posiblemente la productiva del panel); `ADMIN-GUIDE.md:59` enumera las tres cuentas `super_admin` ("Developer, Paul Noble y Cristian Quiñonez") y remite a cambiar contraseñas temporales. Un repo con credenciales vivas convierte la "puerta del desarrollador" (un solo factor, sin 2FA) en una puerta con la llave bajo el felpudo. Adicionalmente `ADMIN-CREDENTIALS-LOCAL.md` existe en la raíz (no auditado aquí en detalle; nombre autoexplicativo).
**Recomendación (no ejecutada — solo documental):** rotar inmediatamente la contraseña real; sacar secretos de la guía (usar placeholders); purgar/reescribir historial si procede; añadir `.env` y dumps a `.gitignore` de la doc; verificar que ninguna credencial productiva siga válida.

### B-04 · ALTA · Identidad del Super Panel con un solo factor y bus factor = 1
El único control de entrada es email exacto (`User::canAccessPanel`, User.php:42-46) + password del `/admin` compartido como guard `web`. Sin 2FA, sin re-auth para acciones sensibles, sin allowlist, sin rate-limit específico más allá del global de Filament. Y si el desarrollador (única cuenta con email === developer_email) queda inoperativo, nadie puede Stripe/fiscal/módulos; los `super_admin` alternos quedan deliberadamente bloqueados fuera.
**Recomendación:** 2FA (Filament v3 soporta MFA vía plugins/Fortify) o, mínimo, `auth('web')->password_confirmed_at` fresco + enrolamiento del `super_admin` reserva como segunda vía documentada (o exención por variable `SUPER_PANEL_ALLOW_SUPER_ADMINS=true` con bitácora), además de rotación periódica.

### B-05 · MEDIA · `TrialBalance` sin guardia de permiso `manage accounting`
`app/Filament/Pages/Accounting/TrialBalance.php:22-24` — solo exige la bandera `accounting`. Cualquier rol del panel (incl. `site_admin`/`editor`, que **no** tienen `manage accounting` en el seeder) ve el libro mayor de prueba interno (saldos contables). El recurso `JournalEntryResource` sí exige permiso vía `RequiresPlatformModule` (app/Filament/Resources/Concerns/RequiresPlatformModule.php:60-141); la página equivalente quedó fuera de ese patrón.
**Recomendación:** replicar el chequeo `manage accounting` (o rol `super_admin|admin|manager`) en `canAccess()` de la página.

### B-06 · MEDIA · Acciones sensibles del Super Panel sin auditoría
`PlatformAppearance::save()` (app/Filament/Pages/SuperPanel/PlatformAppearance.php:50-66) guarda estilo/plantilla **sin** `AuditLogger`; `Settings::save()` (Settings.php:467) tampoco audita; `MaintenanceMode::activate/deactivate` (MaintenanceMode.php:148 / 188) tampoco. La bitácora (AuditLogResource) es el mecanismo prometido como control del desarrollador y hoy tiene puntos ciegos: cambios de apariencia (alteran la marca en producción), de ajustes globales/IA, y modo mantenimiento (disponibilidad).
**Recomendación:** añadir `recordAppearanceChange`, `recordSettingsChange` (diff de claves permitidas, sin secretos), `recordMaintenanceToggle` al `AuditLogger` y llamarlos desde los tres `save()`/acciones.

### B-07 · MEDIA · Permisos `manage settings`, `manage site_settings`, `manage maintenance`, `manage users` sembrados pero inutilizados
`database/seeders/RolesAndPermissionsSeeder.php` define `'manage settings'`, `'manage site_settings'`, `'manage maintenance'`, `'manage users'` pero ningún `canAccess()`/policy los consulta; el acceso se decide por rol grueso o por nada (B-01). Resultado: matriz de permisos opaca; un admin que cree "cree roles" no puede limitar nada por permiso.
**Recomendación:** aplicar esos permisos en las páginas/acciones correspondientes (Settings, MaintenanceMode, UserResource) o borrarlos del seeder para evitar falsa sensación de control.

### B-08 · MEDIA · Duplicación de superficie entre paneles con riesgo de deriva
El Super Panel re-registra manualmente 24 clases (SuperPanelProvider.php:32-57) frente a 27 del admin (AdminPanelProvider.php:35-63); las páginas se **descubren** desde el mismo directorio en ambos (`discoverPages` en ambos, SuperPanelProvider.php:58 / AdminPanelProvider.php:64) — la "exclusividad" de páginas depende 100% de que cada página nueva traiga `canAccess()` correcto. Una futura página "solo developer" sin `canAccess()` quedaría automáticamente pública para todo `/admin`; y cualquier página nueva cualquiera aparecería en ambos paneles. Además, mantener dos provider con listas casi idénticas invita a olvidar el espejo (hoy: `SimulatorFinishVariantResource`/`SimulatorQuoteResource` existen en resources pero no están en ninguno — posible deriva ya presente, BAJA).
**Recomendación:** (1) clase base `SuperPanelResource`/filtro por etiqueta en ambos providers, o descubrimiento con un `CanAccessPanelContent` central; (2) test que falle si una clase nueva en `Filament/Pages` carece de `canAccess()` explícito; (3) documentar en PENDIENTES que se acepta el clon; o reducir el Super Panel a solo sus 3 herramientas exclusivas + dashboard.

### B-09 · MEDIA · Patrón frágil: `mount()` sin `abort_unless`, confiando en el hook del framework
`FiscalSettings::mount()` (FiscalSettings.php:46-49) y `StripeSettings::mount()` (StripeSettings.php:37-49) rellenan formularios (perfil fiscal completo: legal_name, NIF, serie…) **antes** de que se aborte por autorización; el `403` lo produce después `mountCanAuthorizeAccess()` (hook de Livewire verificado, CanAuthorizeAccess.php:7-15). Hoy ningún `mount()` vuelca datos a la respuesta HTTP, y `StripeSettings` no persiste nada sin re-check (StripeCredentials.php:106-111). Es un accidente afortunado, no una garantía local.
**Recomendación:** `abort_unless(static::canAccess(), 403)` al inicio de cada `mount()`/acción (como ya hacen `PlatformModules` y `FiscalSettings::save()`); no depender del orden de hooks de Livewire.

### B-10 · MEDIA · Exclusión global de `/livewire` del modo mantenimiento
bootstrap/app.php:35-41 excluye `admin`, `admin/*`, `super-panel`, `super-panel/*` y **`livewire`, `livewire/*`** de `preventRequestsDuringMaintenance`. Esto es necesario para que los botones Livewire funcionen en el panel, pero también deja abierto cualquier endpoint Livewire no-panel registrado en rutas públicas durante el mantenimiento (si el sitio público llega a usar Livewire).
**Recomendación:** restringir la exclusión (p. ej. excluir solo los paths Livewire usados por panels o verificar componentes), o aceptar el riesgo documentándolo explícitamente en la guía de mantenimiento.

### B-11 · MEDIA · Usuarios/roles desde el Super Panel: protección por email, no por rol, y sin camino de delegación
`UserResource` bloquea correctamente al desarrollador/super_admin/último superadmin (UserResource.php:237-279; tests en UserAdministrationSecurityTest). Pero (a) el Super Panel no añade nada sobre esto — es el mismo recurso — y no hay **matriz de permisos operativos** (PENDIENTES.md lo reconoce), (b) no hay impersonación auditada para soporte del desarrollador, y (c) el seeder `editor` no existe pero el código referencia el rol `editor` en `canAccessPanel` (User.php:48) — si en producción existe un rol `editor`, esos usuarios también entran a `/admin` aunque nadie los haya sembrado (verificar en DB de producción).
**Recomendación:** sembrar o eliminar referencia `editor`; añadir a la bitácora la gestión de roles ya existente; documentar el runbook de "desarrollador inoperativo" (ej. reset por consola con doble control).

### B-12 · MEDIA · Endpoint público `/health` informativo
routes/web.php:198-212 expone entorno, `APP_URL`, y el detalle de checks de `ProductionReadiness` (APP_KEY configurada, DB alcanzable, errores/warnings) sin auth. Útil para monitors, pero regala reconocimiento gratuito a atacantes.
**Recomendación:** exigir token (`?token=`/bearer) o mover el detalle a CLI/panel; devolver solo `{status: ok|warning}` públicamente.

### B-13 · BAJA · Vista huérfana `super-panel-link`
resources/views/filament/components/super-panel-link.blade.php existe pero **no se renderiza en ningún provider** (AdminPanelProvider renderiza toggles admin; SuperPanelProvider renderiza `super-panel-actions`). Además su contenido enlazaría al dashboard del Super Panel desde `/admin` — la decisión actual (no enlazar) parece deliberada para no exponer la existencia del panel a roles no-desarrolladores.
**Recomendación:** decidir: o bien una vista explícitamente invocada solo para developer autenticado (con el guard de email que ya lleva el blade), o borrarla; documentar la decisión.

### B-14 · BAJA · `FiscalSettings` vive en ambos paneles bajo grupo distinto
En Super Panel aparece en grupo `'Finanzas (borradores internos)'` (FiscalSettings.php:22) con sort 2 — es mixing metaphors: una herramienta compartida no está bajo `'Control exclusivo del desarrollador'`, y en `/admin` es accesible por `super_admin|admin` (decisión deliberada, razonable) pero ahí no existe el `super-panel` pin de exclusividad. No es vulnerabilidad; es ambigüedad de diseño.
**Recomendación:** documentar como decisión (compartida a propósito) o restringir a super-panel y dar al admin solo lectura.

### B-15 · BAJA · Naming/prefijo de rutas del Super Panel previsible
La ruta `/super-panel` es pública y adivinable; la seguridad recae 100% en auth (correcto en principio), pero el nombre revela la existencia y propósito del panel. Ofuscación ≠ control (y ya no depende de ella), aun así se paga un precio de "información para el atacante".
**Recomendación:** opcional — permitir `SUPER_PANEL_PATH` por `.env` sin cambiar `id`; no obligatorio.

### B-16 · BAJA · Roles del desarrollador: `@`. AccountWidget sin datos extra
Ambos paneles montan `AccountWidget` (misma información de cuenta). Sin impacto; se deja constancia de que la separación es de acceso, no de UX/privilegio visual (ambos muestran branding propio, coloreado para distinguir — bien).

### Brechas contra lo prometido en documentación

| Promesa docs | Estado | Evidencia |
|---|---|---|
| PENDIENTES.md:100 (este ítem) | abierto | — |
| Módulos gestionables desde Super Panel (10 banderas) | ✔ implementado | SuperPanel/PlatformModules.php; Services/PlatformModules.php |
| Stripe live cifrado solo-desarrollador | ✔ implementado | StripeSettings.php:28-35; StripeCredentials.php |
| Apariencia en Super Panel validada | ✔ implementado y con tests (PlatformModulesTest.php:174-231) | app/Filament/Pages/SuperPanel/PlatformAppearance.php |
| Bitácora administrativa | ◐ parcial — sin apariencia/settings/maintenance/IA | ver B-06 |
| "Dos super_admin adicionales existen en ADMIN-GUIDE" | ✔ consistente con seeder, ⚠ contradice "Super Panel = solo desarrollador" en la cabeza del lector | ADMIN-GUIDE.md:59; User.php:42-49 |
| ADMIN-GUIDE / GUIA-HOSTINGER no documentan el Super Panel en absoluto | Brecha documental: ningún runbook de operación del Super Panel (rotación Stripe, alta/baja de módulos, mantenimiento, rollback de apariencia) | ADMIN-GUIDE.md, GUIA-INSTALACION-HOSTINGER.md, README.md (solo "/admin" y roles) |
| PENDIENTES.md:22/44/54 — flags "activas por defecto" y matriz de 10 controles | ✔ implementado, ⚠ sin matriz de permisos por módulo en UI | RolesAndPermissionsSeeder.php |

---

## 4. Acciones sugeridas (ordenadas)

**P0 — esta semana (solo configuración/operación, sin tocar código):**
1. **Rotar** la contraseña del Super Admin expuesta en `GUIA-INSTALACION-HOSTINGER.md:67,310` y limpiar la doc (placeholders). Verificar que la cuenta "Developer" usa contraseña única y que las otras dos cuentas `super_admin` conocen el procedimiento.
2. Fijar `SUPER_ADMIN_EMAIL` explícito en Hostinger (`.env` de producción); nunca depender del fallback hardcodeado (config/platform.php:6).
3. Comprobar en producción qué roles existen (`editor`?) y revocar los no necesarios en `/admin`.
4. Comunicar al desarrollador que `/super-panel` es su superficie exclusiva y que cualquier página nueva en `Filament/Pages` **debe** declarar `canAccess()`.

**P1 — siguiente sprint (pequeños cambios de código, previa autorización):**
5. `Settings` y `MaintenanceMode`: añadir `canAccess()` (`super_admin|admin` + permiso `manage settings`/`manage maintenance`); enmascarar `ai_api_key` o moverla a pantalla developer-only (B-01, B-07).
6. `TrialBalance`: exigir permiso `manage accounting` (B-05).
7. Auditar `PlatformAppearance::save`, `Settings::save`, mantenimiento on/off vía `AuditLogger` (B-06).
8. `abort_unless(canAccess(), 403)` al inicio de `mount()` de páginas compartidas (B-09).
9. Test de regresión: toda clase en `Filament/Pages` debe implementar `canAccess()` o ser `Dashboard` (B-08).

**P2 — hardening / diseño:**
10. 2FA para la cuenta(s) developer y/o `password_confirmed_at` fresco en acciones sensibles; plan de continuidad si el desarrollador no está disponible (B-04).
11. Fail-closed del fallback `developer_email` (B-02); endpoint `/health` mínimo público (B-12); exclusión `livewire` del maintenance más estrecha (B-10).
12. Matriz de permisos por módulo en UI (admin ↔ permission) y gestión visual de roles; cerrar B-11.
13. Reducir o re-expresar el clon de recursos del Super Panel (¿solo extras + dashboard?) o automatizar la paridad con un trait/test (B-08); resolver recursos huérfanos (`SimulatorFinishVariantResource`, `SimulatorQuoteResource` no registrados en ningún panel).
14. Documentar el runbook del Super Panel (rotación Stripe, cambio de módulos, apariencia, mantenimiento, rollback) y enlazarlo desde PENDIENTES.md; decidir `super-panel-link` (B-13) y si `FiscalSettings` se queda compartida (B-14).
15. Opcional: `SUPER_PANEL_PATH` por `.env` (B-15); quitar/crear rol `editor` según producción (B-11); purgar `ADMIN-CREDENTIALS-LOCAL.md` o moverlo fuera del repo.

**Validación previa a cerrar el ítem en PENDIENTES.md:** re-ejecutar `php artisan test` tras los cambios P1 (actualizar cifra en §"Estado validado"), y añadir pruebas de guardia para `Settings`/`MaintenanceMode`/`TrialBalance` análogas a `PlatformModulesTest`.

---

## 5. Resumen ejecutivo

El Super Panel **cumple lo básico prometido**: existe, es alcanzable solo por el email del desarrollador, sus tres herramientas exclusivas (módulos, apariencia, Stripe) están bien acotadas y —salvo el fallback de config— con verificación en varias capas; la bitácora cubre módulos/Stripe/fiscal/roles; y el aislamiento frente a `/admin` es real en la verificación de login y en cada página exclusiva.

Sus debilidades estructurales son: **una identidad de un solo factor con fallback público**, **páginas compartidas sin guardia** (`Settings` con la API key de IA, `MaintenanceMode`), **acciones developer sin auditoría**, y una **copia manual del panel completo** que depende de la disciplina de cada página nueva. Ninguna página exclusiva es hoy accesible para personal no desarrollador; el riesgo se concentra en el perímetro, no en la lógica de las páginas exclusivas.

**Resultado de pruebas base:** `127 passed (727 assertions)` — distinto del 121 documentado en PENDIENTES.md; no hay fallos.

*Documento generado solo con lectura; no se modificó código, vistas, config ni datos. Única escritura: este archivo.*
