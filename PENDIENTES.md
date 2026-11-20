# Trabajo pendiente

Actualizado: 15 de noviembre de 2026

Este documento registra lo que falta para completar la conversión del sitio en una plataforma genérica y configurable. Los cambios realizados hasta ahora son locales: no se modificó `.env`, no se aplicaron migraciones a producción ni se desplegó el sitio.

- **Despliegue documentado — 2026-11-16:** añadidos `docs/CREDENCIALES.md` (usuarios y contraseñas: super_admin de la vista previa local, cuentas dev, super_admin elegido en el instalador, claves sin configurar, avisos de rotación), `docs/INSTALACION-HOSTINGER.md` (instalación completa por instalador web o por SSH, checklist y troubleshooting), `docs/database.sql` (esquema MySQL, 73 tablas, sin datos) y `install-hostinger.sh` ahora siembra `InstallSeeder` (no las cuentas dev). Producción sigue sin tocar.

## i18n — mezcla ES/EN corregida — 2026-11-15



- Frontend ES-inclusive por defecto: vistas sin claves `__()` fijas ya en ambos idiomas.
- 90 claves añadidas a `lang/es.json` + `resources/lang/es.json` (traducciones ES) y sus claves en `en.json`.
- Cookie banner ahora traducible (`CookieConsent::mount()` usa `__()`; claves añadidas a 7 idiomas).
- Navegación/cliente/admin/correo: cadenas duras ES envueltas en `__()` (accesos clientes, login, registro, verificación, portal de clientes, login admin, footer `© ... All rights reserved`).
- `en.json`: claves en español autogeneradas corregidas a inglés correcto.
- Verificación: todos los idiomas sirven 200; **suite 127 passed**; 8 tests ajustados (locale fijado a `es` donde verificaban texto español).
- Por defecto frontend sigue en **inglés** (decisión de diseño codificada en `LocaleDefaultEnglishTest`); si prefieres ES por defecto para España, es un cambio de una línea + ese test.
- Traducción completa a 7 idiomas sigue incompleta por los ~450 claves `lang/*.json` generados automáticamente antes del rebranding (valores en español en teclas inglesas en DE/IT/FR/PT/NL). Queda pendiente si quieres revisión humana multilingüe.



## Rebranding a Costa Resin & Construction — 2026-11-15 (instrucción del titular)

- Logo oficial COSTA RESIN & CONSTRUCTION aplicado: `images/logo.png`, `images/logo-white.png` (texto en blanco para fondo oscuro), `images/favicon.png` (128 px, disco de sierra), `storage/app/public/site/og-default.jpg` (1200×630 generado) + espejos en `public/storage/site/` y `public/images/`. Respaldo de los originales (Infinity Effects) en `_devtools/brand-assets-20261115/`.
- Nombre visible sustituido en ~75 archivos (config, .env*, app, lang, seeders, vistas, correos, JSON-LD, sitemap.xsl, robots): "ResinFlow" y "Fortis Resin Systems" → "Costa Resin & Construction"; dominios `resinflow.app`/`fortisresin.es`/`fortisresinsystems.es` → `TU_DOMINIO.com` (placeholders).
- Limpieza de registros de marca anterior: 87 archivos `*.bak_*`, `__deploy_bak/`, `__upload_tmp__/`, `config.bak_*` archivados en `_devtools/backups/`; `storage/logs/laravel.log` respaldado en `_devtools/logs/` y vaciado; `database/fortis.db` (0 bytes) y `images/van-fortis.jpg` archivados en `_devtools/`; credenciales super_admin de Fortis retiradas de `.env.production`, `GUIA-INSTALACION-HOSTINGER.md`, `deploy.sh` e `install-hostinger.sh` (placeholders `TU_DOMINIO.com`); passwords dev locales regeneradas (`costaresin-*-2026!`); `DatabaseSeeder` y correos de ejemplo pasan a `costaresin.local`; nombre del paquete composer `costaresin/construction-website`. Navegación y pie públicos muestran el logo de Costa Resin & Construction.
- Codename interno `FortisMapsCalculator` **renombrado a `MapsCalculator`** (77 archivos: namespaces, directorios, rutas internas `maps-calculator.*`, Livewire, seeds y tests). Suite: 127 passed.
- Carpeta del proyecto renombrada: `fortisresinbase` → **`costa-resin-website`** (en `Documentos Julian/`); `.env` `DB_DATABASE` actualizado; servidor local reiniciado.
- En árboles vivos solo persisten rastros históricos documentados en `docs/` (auditoría) y en `_devtools/` (archivos conservados a propósito).
- Navegación y pie públicos muestran el logo (`<img>` con alt "Costa Resin & Construction").
- OJO: el catálogo sembrado localmente sigue teniendo las categorías **Spray Granite** y **Spray Stone** (catálogo de la marca anterior) y las páginas legales sembradas dirán "Costa Resin & Construction" con datos ficticios (Madrid/teléfono inventado) — ambas cosas pendientes de confirmación del titular (ver docs/LEGAL-REVIEW.md).
- Faltan para completar la marca: dominio, teléfono, email, dirección, NIF, redes sociales, WhatsApp number, colores/tipografías definitivas.

## Actualizado 2026-11-15 — trabajo ejecutado

> Nota: la marca vigente es **Costa Resin & Construction** (rebranding del mismo día); donde aparece «ResinFlow» en este documento es histórico del trabajo previo.

- **Unificar la marca pública [HECHO]:** `/servicios` y ~25 vistas públicas (login, acceso clientes, correos, WhatsApp, mantenimiento, instalador, etc.) ya usan `platform.public_name`; defaults de `config/app.php` y `config/platform.php` pasan a `ResinFlow`. Sin «Plataforma Profesional» en páginas públicas (verificado por HTTP). Portada Fortis intacta.
- **Métodos de demostración del asistente IA [HECHO]:** eliminados 9 métodos sin llamadas en `app/Services/AiAssistantService.php`; banner de `TestAiAssistant` actualizado a «ResinFlow - IA». Rama demo deliberada (sin API key) conservada por estar testeada.
- **Pruebas de filtros/edición de categorías de proyectos [HECHO]:** 6 tests nuevos en `tests/Feature/ProjectCategoryFilterTest.php`. Suite completa: **127 passed (727 assertions)**.
- **Alcance del Super Panel [DOCUMENTADO]:** inventario y 16 brechas con severidad en `docs/SUPER-PANEL-SCOPE.md`. Sin implementar; acciones P0→P2 pendientes.
- **Revisión de recursos visuales [AUDITADA]:** inventario completo (duplicados MD5, og:image de fábrica, logo/favicon, assets de catálogo Infinity Effects, ~124 MiB de duplicación física) en `docs/ASSET-AUDIT.md`. Reemplazos/retiradas **pendientes de confirmación del titular**.
- **Apariencia y plantillas [VERIFICACIÓN HTTP]:** los 4 estilos y las 5 plantillas de portada responden HTTP 200 con `data-platform-style` correcto; la config local quedó restaurada (portfolio / services). Pendiente la inspección visual humana (contraste, móvil).
- **Auditoría legal [DOCUMENTADA]:** `docs/LEGAL-REVIEW.md` — el seeder legal mezcla datos ficticios (ResinFlow/Madrid) y Fortis (Benissa) sin verificar; nada debe publicarse sin confirmar entidad, NIF, fuero y contacto. 11 preguntas al titular. El EN no equivale al ES.
- **Tests:** la cifra de 107 pruebas que figuraba abajo quedó desactualizada; estado actual **127 passed**.
- **Producción/MySQL:** sin tocar, como siempre. La integración de marca/idioma nuevos **no se ha desplegado**.

## Mejoras completadas en esta etapa

- La portada principal ahora representa a Fortis Resin Systems como negocio de instalación de pavimentos de resina, construcción y venta de productos en Málaga y Costa Blanca. La portada anterior de plataforma se conserva en `/plataforma-empresarial` y como plantilla alternativa en el Super Panel.
- Se retiraron de los datos estructurados de negocio una dirección, coordenadas, teléfono, correo, horario, logo y fecha de fundación no verificados. El teléfono y correo solo se publican si están configurados; la búsqueda del catálogo solo se anuncia cuando Productos está activo.
- Se extendió el estilo corporativo y adaptable a Servicios, Contacto, Cotizaciones, Proyectos, Blog, Acerca de y páginas de detalle.
- Se unificó el recorrido público: explicar el servicio, enviar una solicitud, revisión manual del equipo y propuesta/siguientes pasos. El sitio aclara que el formulario todavía no emite facturas ni procesa pagos.
- El portafolio ya admite categorías, búsqueda por título/ubicación y administración de categoría desde el recurso de Proyectos.
- Los respaldos visuales de Servicios son ilustraciones de flujo, no fotografías de productos. Los recursos cargados en `ContentBlock` aún requieren auditoría.
- El pie y el aviso de cookies solo enlazan páginas legales existentes. No se inventó ni actualizó contenido legal.
- Se corrigió el enlace de administración del correo de nueva cotización: apuntaba a una ruta inexistente de Filament y fallaba al renderizar el aviso. Una prueba de regresión verifica ahora el enlace real.

Las migraciones `2026_11_04_042500_add_category_to_projects_table` a `2026_11_04_171000_enable_product_catalog_module`, incluida `2026_11_04_183000_add_work_order_costs_and_offline_logs`, están aplicadas únicamente a `storage/framework/local-preview.sqlite`. Se hizo una copia de respaldo antes de aplicar la migración más reciente; la comprobación SQLite informó `integrity_check: ok` y cero violaciones de claves foráneas. La migración de apariencia también cambia el valor guardado `corporate` por `resin_business` para que la portada comercial pase a ser la principal tras desplegar, y activa Productos conservando los registros existentes. No se aplicó nada a producción. La conexión CLI sin variables explícitas apunta a MySQL: verificar el destino y autorizar el despliegue antes de migrar producción. La migración de líneas de cotización crea la tabla solo si no existe; revisar cualquier tabla manual preexistente antes de desplegar.

## Módulos administrativos: base de activación completada

Se amplió `PlatformModules` y la página del Super Panel para centralizar las banderas de catálogo, inventario, cotizaciones, órdenes de trabajo, facturación, contabilidad, simulador, administración de usuarios y portal de clientes. Las banderas se guardan en `settings`; desactivar una función no elimina registros. Se añadieron bloqueos de rutas/API y visibilidad de recursos existentes donde esas funciones ya están conectadas.

- Disponible parcialmente: catálogo de productos y comercio, inventario, cotizaciones, órdenes de trabajo, simulador, administración de usuarios y portal de clientes. Estos módulos operativos quedan activos por defecto y se pueden ocultar o reactivar desde el Super Panel. El checkout de productos y los cobros online permanecen apagados hasta configurar Stripe y completar la revisión fiscal.
- El catálogo aparece en la navegación pública y en `/tienda`; el recurso de gestión está en `/admin/products` y también en `/super-panel/products`. La bandera se administra en `/super-panel/modules`. La vista previa SQLite tiene cero productos cargados; no se sembraron precios ni existencias de ejemplo.
- [x] **Carrito y checkout ecommerce (base):** rutas de catálogo, carrito, checkout y confirmación; se revalidan precio y stock vigentes, y el pedido solo se marca pagado tras el webhook firmado. Se retiraron métodos de pago no verificados. El checkout real sigue bloqueado hasta activar módulos, guardar credenciales Stripe live y confirmar revisión fiscal; falta probar el flujo con cuenta Stripe y cargar catálogo/stock reales.
- [x] **Resolución de pedidos con conflicto de stock:** el panel permite revalidar y descontar el stock tras la reposición, o solicitar un reembolso completo idempotente a Stripe. La orden solo pasa a cancelada/reembolsada tras la confirmación; se registra el estado y se aplican los webhooks. Un fallo de Stripe requiere conciliación manual para evitar duplicados.
- Facturación: se puede crear desde una cotización aprobada un borrador interno no fiscal con líneas y totales copiados. La vista está controlada por la bandera `invoicing`, activa por defecto. No hay numeración fiscal, emisión, envío ni abonos; el cobro de la cotización es un flujo separado y no constituye una factura fiscal.
- Cobros: pagos de servicios implementados con anticipo configurable (30% inicial), saldo al finalizar la orden y reembolsos parciales iniciados por administración. Claves live y secreto de webhook se guardan cifrados. No se habilita ningún cobro real hasta activar módulos, configurar Stripe y confirmar revisión fiscal desde el Super Panel.
- La recepción/salida de stock conserva su libro auditable; las reservas activas de materiales descuentan disponibilidad y se consumen al registrar uso en una orden. Se añadieron pedidos a proveedor, recepciones auditables y valoración por coste medio ponderado por ubicación. La valoración queda inactiva hasta cargar saldos iniciales y activarla desde el panel.
- Portal de clientes: registro/login web con cuenta separada, verificación obligatoria del correo, consulta limitada a cotizaciones/proyectos/pedidos coincidentes con el correo verificado y aceptación/rechazo de cotizaciones enviadas. Aceptar una cotización no crea automáticamente una orden. Está activo por defecto y se puede ocultar desde el Super Panel.
- Administración de usuarios: la asignación del rol `super_admin` queda reservada al correo de desarrollador configurado; no se puede borrar el propio usuario, la cuenta del desarrollador ni el último superadministrador.
- Contabilidad: diario interno genérico de partida doble, asientos equilibrados, contabilización inmutable, reversos y balance de comprobación. No hay un plan contable oficial precargado, cierres, conciliación bancaria ni libros fiscales; no usar para libros estatutarios.
- Dependencias impuestas: órdenes de trabajo y facturación requieren cotizaciones; contabilidad requiere facturación; pagos requiere cotizaciones. El Super Panel rechaza estados incompatibles y permite guardar cambios dependientes en una sola operación. Los pagos online continúan desactivados por defecto.
- El inventario ya tiene catálogo, ubicaciones y libro inmutable de entradas, salidas y ajustes; bloquea existencias negativas y conserva saldos históricos. Las órdenes activas pueden registrar consumo con enlace a la orden, artículo, ubicación, usuario y saldo; solo se permite durante preparación o ejecución, con los dos módulos habilitados y permisos del proyecto.
- El inventario se mantiene separado del campo `Product.stock`; definir la integración o migración de existencias antes de reactivar comercio o vincular artículos de venta. Las recepciones capturan coste y alimentan el coste medio ponderado cuando está activado.
- El simulador y sus API ya no son públicos: requieren sesión y rol administrativo. Hay un acceso directo desde las barras superiores del panel.
- La solicitud pública de cotización sigue disponible aunque se desactive el espacio interno de cotizaciones; así se conserva el canal de contacto.

El usuario confirmó España, Málaga (territorio común) y alcance contable completo. El módulo permite capturar datos fiscales de empresa, pero siguen sin definirse forma jurídica, situación frente al SII, volumen de operaciones y si el producto emitirá/intercambiará facturas por cuenta de terceros. No se generarán facturas fiscales ni se habilitarán cobros live hasta verificar los requisitos españoles vigentes y definir la configuración legal de la entidad/proveedor.

## Estado funcional de los módulos

Los módulos operativos listados aquí ya están conectados al panel administrativo. **Se pueden ocultar y activar desde el Super Panel**, reservado al desarrollador. Productos, inventario, cotizaciones, órdenes de trabajo, facturación interna, contabilidad interna, simulador, administración y portal de clientes están activos por defecto; pagos online permanecen apagados por seguridad.

1. [x] **Inventarios (operación parcial):** catálogo, ubicaciones, movimientos firmados, consumo, reservas, proveedores y pedidos de compra con recepciones parciales/íntegras enlazadas al libro. El coste medio ponderado por ubicación calcula entradas y consumos cuando se activa; falta cargar el inventario inicial real y conciliarlo con `Product.stock`/contabilidad.
2. [x] **Contabilidad (base interna):** cuentas, asientos de partida doble equilibrados, contabilización inmutable, reversos y balance interno bajo la bandera `accounting`. Falta definir/validar el plan de cuentas oficial, cierres, conciliación bancaria, libros e informes fiscales con asesoría contable.
3. [x] **Facturación (borradores):** borradores internos no fiscales desde cotizaciones aprobadas, con instantánea de líneas y totales; cada cotización solo genera uno. Falta ciclo fiscal completo, numeración, emisión, impuestos, abonos, documentos y flujo desde órdenes.
4. [x] **Cotizaciones:** el flujo interno conserva versiones y aprobación; la conversión a orden es explícita y previene duplicados. El portal permite al cliente ver y responder a cotizaciones enviadas propias. Falta completar PDFs/descarga, comunicación comercial y validación fiscal.
5. [x] **Órdenes de trabajo:** crear desde cotización aprobada, asignar personal, calendario, transición de estados, fechas reales de inicio/finalización y bitácora están conectados. Se registran costes de mano de obra y otros costes; inventario aporta materiales valorados cuando el coste medio está activo. La bitácora admite captura local offline y sincronización idempotente sin teselas offline. Falta definir retención/limpieza del almacenamiento del navegador y conciliar los costes con contabilidad.
6. [x] **Simulador:** se retiró de la navegación pública y ahora requiere autenticación y rol administrativo; el enlace vive en el panel. Se conservaron el simulador y sus registros.
7. [x] **Módulo administrativo:** la gestión de usuarios y roles existe; se añadieron protecciones para la cuenta del desarrollador, el rol `super_admin` y el último superadministrador. Se agregó una bitácora de solo lectura para cambios de identidad/roles y operaciones de módulos; falta una matriz de permisos operativos más detallada y gestión visual completa de roles.

Requisito de aceptación común: diez controles centrales están disponibles en el Super Panel. Cada módulo debe probar navegación, acceso directo y API según superficies implementadas, sin borrar datos. Las banderas de módulos en desarrollo no indican que estén listos para uso.

Estado actual: catálogo, CRM, cotizaciones, órdenes, MapsCalculator, inventario con reservas y compras, portal de clientes verificados, borradores internos no fiscales, diario contable genérico y bitácora administrativa tienen funcionalidades parciales. No presentar capacidades pendientes como operativas ni el diario genérico como contabilidad legal.

## Funciones de plataforma aún pendientes

- [ ] **Facturación fiscal:** el sistema solo crea borradores internos no fiscales desde cotizaciones aprobadas y permite capturar el perfil de empresa. Confirmar forma jurídica, sistema de numeración, fiscalidad, SII/VERI*FACTU aplicable, abonos, conservación, factura electrónica y si se facturará por cuenta de terceros antes de implementar emisión.
- [ ] **Inventario valorado y contabilidad oficial:** se eligió e implementó coste medio ponderado por ubicación. Falta importar cantidades y costes unitarios iniciales por artículo/ubicación, activar la valoración tras conciliación y validar el plan contable, cierres, libros e informes fiscales.
- [ ] **Habilitación de cobros live:** flujo de cotizaciones listo, pero desactivado por defecto. Configurar claves/webhook en el Super Panel, verificar eventos de Stripe y confirmar la asesoría fiscal antes de habilitar cargos. No se han ejecutado transacciones live ni pruebas con credenciales reales.
- [x] **PDF de cotizaciones:** DomPDF instalado y bloqueado en Composer; la descarga A4 está disponible para el equipo autorizado y en el portal solo para el cliente con correo verificado que sea propietario de la cotización. Las pruebas verifican PDF válido y privacidad.
- [x] **Acceso web de clientes (base):** alta/login con sesión independiente, verificación obligatoria, consultas de datos propios, respuesta a cotizaciones y descarga privada de sus PDF. Falta completar descarga/seguimiento de otros documentos y revisar privacidad/retención.
- [x] **Captura offline de MapsCalculator (base):** apuntes de bitácora en cola local del navegador, sincronización al volver la conexión, clave idempotente y rechazo de reutilización entre usuarios/órdenes; no se guardan fotos ni teselas. La página debe haberse cargado antes de perder conexión. Falta definir caducidad/limpieza automática de la cola local y retención formal de datos.
- [x] **Activación central de módulos:** registro de diez banderas, interfaz del Super Panel, dependencias, visibilidad/bloqueo de superficies ya existentes y retención de datos.
- [x] **Controles de módulos nuevos:** las pantallas de borradores están protegidas por `invoicing`; cuentas, asientos y balance interno por `accounting`; el checkout de productos requiere Productos y Pagos y los cobros de servicios requieren Cotizaciones, Facturación y Pagos. El módulo de contabilidad no implementa libros oficiales.
- [x] **Credenciales Stripe del Super Panel:** las claves live y el secreto de webhook se guardan cifrados con `APP_KEY`, se muestran vacíos al volver a la página y solo el desarrollador puede administrarlos. La configuración no habilita cobros por sí sola; el checkout sigue condicionado a las banderas de los módulos.
- [x] **Perfil fiscal de empresa:** captura de razón social, identificador y domicilio, protegida por el módulo de facturación y roles administrativos. Es solo configuración preparatoria; no permite emitir ni numerar facturas fiscales.
- [x] **Cobro de cotizaciones de servicios:** cliente autenticado y verificado paga anticipo (30% por defecto, ajustable antes del envío) por Stripe; saldo habilitado solo tras completar la orden. Se registran intentos, pagos y reembolsos parciales idempotentes, y se auditan sin guardar secretos.
- [x] **Aislamiento de módulos cotizaciones/órdenes:** ocultar relaciones y datos del portal de cliente/CRM al desactivar el módulo, bloquear las acciones Livewire y evitar crear órdenes automáticamente al aprobar una cotización.
- [x] **Estados, cierre y costes de órdenes:** transiciones permitidas también en Filament/Livewire/HTTP; inicio y final reales se registran al comenzar y terminar. Administración/comercial captura costes de mano de obra y otros costes; la tabla expone el coste de materiales valorado y el panel muestra el total de costes conocidos.
- [x] **Creación de órdenes desde Filament:** exige cotización aprobada del mismo proyecto y evita órdenes duplicadas; las ediciones mantienen sincronizada la asignación del proyecto.
- [x] **Resolución de pedidos ecommerce en `stock_review`:** reabastecer y surtir con descuento transaccional, o reembolsar completo mediante Stripe con idempotencia, validación de webhook y auditoría; el estado de pago no se cambia manualmente desde el recurso.
- [x] **Calendario operativo y rutas HTTP:** lista filtrable por fecha, estado, técnico y proyecto/cliente; creación HTTP exige una cotización aprobada del mismo proyecto y evita órdenes duplicadas; las rutas HTTP aplican las mismas transiciones válidas y validación de asignación.
- [x] **Consumo de inventario por orden:** movimientos de salida se enlazan a la orden en una migración aditiva; consumos requieren ambos módulos activos, personal autorizado, orden en preparación/en ejecución, artículo/ubicación activos y existencias suficientes.
- [x] **Reservas de inventario:** bloquean stock para órdenes en preparación/ejecución, protegen las cantidades reservadas de salidas ajenas, se descuentan al consumir y pueden liberarse con auditoría de usuario y fecha.
- [x] **Compras y recepciones de inventario:** pedidos a proveedores, recepción parcial/completa transaccional, vínculo a movimientos, captura de costes y rechazo de sobre-recepción.
- [x] **Valoración por coste medio ponderado:** saldo inicial por artículo/ubicación debe coincidir con el libro; se impide activar el método con existencias sin valorar. Las entradas promedian coste y las salidas/consumos de órdenes toman el coste medio; la activación y movimientos quedan auditados. Aún no genera asientos contables.
- [x] **Borrador interno de factura:** instantánea única de una cotización aprobada, con líneas, datos del cliente y totales; solo el panel interno puede consultarlo, sin numeración ni emisión fiscal.
- [x] **Bitácora administrativa:** eventos inmutables de creación/cambio/borrado para usuarios, cotizaciones, operaciones, inventario, facturación interna, diario contable y configuración Stripe/fiscal; los eventos de configuración registran solo estados o nombres de campos, nunca claves ni datos fiscales. Guarda actor, fecha, IP y navegador. Disponible en el panel mientras administración esté habilitado.
- [x] **Idempotencia de webhooks y reembolsos:** pagos de pedidos no descuentan stock dos veces al repetirse eventos; reembolsos confirmados se consultan por su ID y no se vuelven a crear. Sin ID remoto tras 23 horas, se exige reconciliación manual para evitar cargos duplicados.

La nueva plantilla corporativa identifica estas capacidades como pendientes en vez de presentarlas como operativas.

## Prioridad alta

- [x] **Modo de demostración del asistente de IA:** sin clave configurada informa que no generó respuesta ni datos comerciales, y escapa la solicitud. Las plantillas y el comando de prueba ya no inyectan la marca anterior ni detalles de negocio no confirmados. Completado el 2026-11-15: se eliminaron los 9 métodos de demostración antiguos sin llamadas internas.
- [x] **Auditoría de contenido de la vista previa local:** la base SQLite local no contiene páginas ni bloques guardados; tampoco tiene ajustes SEO o datos de contacto que prevalezcan sobre los respaldos. La base autorizada de producción aún debe revisarse por separado, con respaldo y sin modificar datos.
- [ ] **Verificar las páginas legales antes de publicarlas.** El seeder aún incluye contenido y datos de contacto de la marca anterior; confirmar entidad responsable, jurisdicción, datos de contacto y texto aplicable. La vista previa sin páginas sembradas oculta sus enlaces y no es una revisión legal.
- [x] **Alcance de acceso de MapsCalculator:** las listas y detalles de clientes, proyectos y órdenes de trabajo respetan las asignaciones del usuario; se añadieron pruebas para impedir acceso cruzado a clientes y órdenes no asignadas. Administración conserva el acceso completo.

## Apariencia y experiencia

- [x] **Unificar la marca pública:** completado el 2026-11-15 (ver sección "Actualizado 2026-11-15"). Los valores de marca configurables son `APP_NAME` / `PLATFORM_PUBLIC_NAME`.
- [ ] **Probar visualmente los cuatro estilos** (profesional, oscuro moderno, portfolio y minimalista) y las plantillas estándar, portfolio, servicios y plataforma corporativa en escritorio y móvil. Verificación HTTP del 2026-11-15: las 4 paletas y las 5 plantillas responden 200 y `data-platform-style` cambia correctamente (sin validación visual humana ni contraste ni móvil). Confirmar contraste, legibilidad y consistencia.
- [x] **Validar la selección de apariencia en el Super Panel.** Pruebas confirman guardado con Livewire, persistencia al recargar y rechazo de usuarios no autorizados y del panel administrativo normal.
- [x] **Revisar los recursos visuales actuales.** Auditoría de solo lectura completada el 2026-11-15 (`docs/ASSET-AUDIT.md`): confirmar que las fotografías reutilizadas siguen siendo apropiadas para una plataforma genérica; reemplazar o retirar las que todavía sugieran una empresa o catálogo específico. Pendiente la aprobación del titular y la verificación en producción; no se inventaron fotografías ni modelos 3D.
- [x] **Traducciones SEO genéricas:** descripciones y palabras clave de portada, servicios, proyectos, contacto, blog y catálogo traducidas en los siete idiomas activos. La plantilla corporativa usa ahora esos metadatos localizados.
- [x] **Comprobar el alcance del Super Panel.** Auditoría completada el 2026-11-15 (`docs/SUPER-PANEL-SCOPE.md`): cobertura documentada, separación del panel normal verificada (protecciones con archivo:línea) y 16 brechas con severidad y plan P0→P2. Pendiente la implementación de las correcciones.

## Pruebas pendientes

- [x] **Pruebas de catálogo y administración:** el catálogo queda activo por defecto, puede desactivarse desde la bandera del módulo y el recurso de Productos es accesible en el panel administrativo.
- [x] Añadir pruebas de autorización y guardado de apariencia, además del renderizado de las cuatro paletas y las cuatro plantillas en la portada.
- [x] Añadir regresiones de privacidad para clientes y órdenes de trabajo de MapsCalculator y ejecutar la suite completa en SQLite en memoria; la última ejecución aprobó 107 pruebas (651 aserciones).
- [ ] Confirmar los productos, precios e inventario reales antes de cargarlos en la vista previa; no se consultó ni modificó la base de producción.
- [x] Probar filtros y edición de categorías de proyectos en Filament con datos representativos. Completado el 2026-11-15: `tests/Feature/ProjectCategoryFilterTest.php` (6 pruebas: filtro `SelectFilter::category`, edición vía página, visibilidad en tabla/detalle/público); la cobertura funcional de búsqueda en tabla Filament y pública ya existía. Suite: 127 passed (727 assertions).

## Estado validado más recientemente

- `php artisan view:cache`: correcto.
- `npx tailwindcss -i .\resources\css\app.css -o .\css\app.css --minify`: correcto.
- `npm run build`: correcto.
- `php artisan test --compact`: 107 pruebas aprobadas (651 aserciones) en SQLite en memoria, incluidas el enlace del correo de cotización, PDF, resolución de pedidos, órdenes de trabajo y captura offline.
- Pruebas enfocadas de Productos: catálogo activo por defecto, API y frontend al habilitarse y acceso al recurso Filament; 34 pruebas combinadas de módulos/panel y administración de productos aprobadas (289 aserciones).
- `php artisan view:cache`: correcto después de los cambios al Super Panel.
- Migración `2026_11_04_183000_add_work_order_costs_and_offline_logs`: aplicada a la base SQLite local; `integrity_check: ok` y cero violaciones de claves foráneas. Se conservó una copia previa a la migración en `storage/framework/local-preview-before-module-completion-20261104-1828.sqlite`; producción/MySQL no fue modificada.
- `composer validate --no-check-publish`: correcto.
- Vista previa iniciada en `http://127.0.0.1:8000`; portada española y hoja CSS respondieron HTTP 200.
- Simulador: `/simulador` y sus tres API requieren autenticación, rol administrativo y bandera activa; el enlace se quitó de la navegación pública.
- Páginas revisadas en escritorio/móvil: Servicios, Contacto, Cotizaciones, Proyectos y Blog; HTTP 200 y sin desbordamiento horizontal en las comprobaciones realizadas.
- Vista previa local iniciada en `http://127.0.0.1:8000`; portada corporativa en español revisada visualmente y los recursos de portada, Servicios, Contacto, Cotizaciones, Proyectos, Blog, Acerca de y CSS respondieron HTTP 200.
- Catálogo, `/admin/products`, `/super-panel/modules` y portada alternativa `/plataforma-empresarial?lang=es` respondieron HTTP 200. La vista previa local contiene cero productos; se requieren datos reales para poblarla.
- Portada comprobada en móvil a 390 px: sin desbordamiento horizontal; el metadato SEO se muestra en español.
- Footer: se corrigió el símbolo de copyright duplicado cuando la configuración ya incluye `©`; prueba enfocada aprobada.
- Categorías del portafolio: la búsqueda y el filtro se cubren con pruebas. La vista previa aún no contiene proyectos, por lo que no se cargaron datos ficticios.
- `MapsCalculatorSyncTest`: 1 prueba aprobada (6 aserciones).
- Ruta `super-panel/appearance`: registrada.
- Pint no se pudo ejecutar en el PHP 8.2 local porque la versión instalada de Pint requiere PHP 8.3.
