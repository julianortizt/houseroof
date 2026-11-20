# Plan de preparación para producción real

## Estado actual verificado

- El proyecto Laravel está arrancado en `http://127.0.0.1:8000` con respuesta HTTP 200.
- La suite de pruebas del proyecto está pasando: `107 passed (651 assertions)`.
- La app ya tiene entorno de producción configurado en `.env` con `APP_ENV=production`, `APP_DEBUG=false` y `APP_URL=https://TU_DOMINIO.com`.
- Hay módulos avanzados operativos, pero el documento interno de pendientes indica que aún no es un sistema legalmente listo para facturación fiscal ni cobros reales.

## Objetivo

Convertir la plataforma de “preview avanzada” a “sistema operativo para negocio real” dentro del marco legal y comercial de España.

## Fase 1: Bloqueos legales y fiscales (prioridad máxima)

### 1.1 Facturación fiscal
- Confirmar forma jurídica de la empresa.
- Definir número de identificación fiscal y régimen aplicable.
- Decidir uso de SII / VERI*FACTU / factura electrónica.
- Establecer numeración, series, serie de terceros y flujo de emisión.
- Definir abonos, anulaciones y conservación documental.
- Revisar política de facturación de terceros si aplica.

### 1.2 Perfil fiscal y datos de empresa
- Validar razón social, CIF/NIF, dirección fiscal, domicilio y representación.
- Documentar la entidad responsable y organización contable.
- Confirmar si la empresa emitirá facturas por cuenta ajena o por su propia cuenta.

### 1.3 Revisión legal de páginas y avisos
- Revisar textos legales de privacidad, cookies, condiciones y aviso legal.
- Verificar que no queden referencias genéricas o a marca antigua.
- Confirmar jurisdicción y responsable legal.

## Fase 2: Cobros reales y pagos

### 2.1 Stripe live / webhook
- Preparar claves live de Stripe.
- Configurar webhook firmando con el secreto correcto.
- Validar eventos: `checkout.session.completed`, pagos, reembolsos y idempotencia.
- Probar flujo completo en entorno real con datos no productivos.

### 2.2 Bloquear/activar módulos de pago
- Activar solo tras la revisión fiscal.
- Establecer reglas de módulos: `payments`, `quotes`, `invoicing` y `accounting`.
- Validar que la activación no permita cobros sin la fiscalidad correcta.

## Fase 3: Inventario, valoración y contabilidad oficial

### 3.1 Persistir stock real
- Importar inventario inicial con cantidades y costes reales.
- Conciliar `Product.stock` con movimientos de inventario.
- Verificar que las reservas y consumos no generen inconsistencias.

### 3.2 Valuación por coste medio ponderado
- Activar solo con inventario inicial validado.
- Comprobar si los movimientos se reflejan correctamente por ubicación.
- Revisar integración con la contabilidad interna.

### 3.3 Contabilidad legal
- Definir plan contable real acorde a España.
- Implementar cierres contables, balances y conciliación bancaria.
- Revisar necesidad de libros fiscales y asesoría contable.

## Fase 4: Seguridad y hardening de producción

- Revisión de cachés y optimización final con `config:cache`, `route:cache`, `view:cache`.
- Verificar sesión, cookies y dominio seguro.
- Revisar permisos `super_admin` y separación de roles.
- Revisar logs, auditoría y trazabilidad de acciones críticas.
- Confirmar que ningún dato sensible se expone en la UI o en los logs.

## Fase 5: Datos de negocio y UX final

- Poblar catálogo y precios con datos reales.
- Revisar la marca pública para que no quede mezcla de identidad genérica y comercial.
- Validar diseño responsive en móvil y escritorio.
- Confirmar que los textos de servicios y cotización reflejan el negocio real.

## Orden recomendado de implementación

1. Revisión legal/fiscal.
2. Stripe live y pagos reales.
3. Inventario + valoración real.
4. Contabilidad legal y conciliación.
5. Datos reales del negocio.
6. Release final de producción.

## Criterio de “listo para producción real”

La plataforma estará lista cuando:

- la entidad y fiscalidad estén definidas y validadas,
- Stripe live esté validado,
- la facturación fiscal sea legalmente operativa,
- el inventario inicial esté reconciliado,
- la contabilidad esté auditorizable,
- y la plataforma esté probado con flujo real y sin bloqueos de negocio.

Hasta ese punto, el sistema debe seguir tratándose como una plataforma avanzada en preview con base técnica sólida, pero no como negocio en producción real.

## Estado de ejecución actual (05/11/2026)

### Bloque 1: legal / rutas de producción
- Corregido el alias inconsistente `cookie-policy` vs `cookies`.
- Añadido enrutado canónico y redirecciones 301 para evitar 404s en producción.
- Verificado con prueba funcional: `php artisan test --filter=ProductionReadinessTest`.

### Bloque 2: preparación para despliegue
- Creado el checker de producción con `app:production-check`.
- Creado el bootstrap comercial con `app:production-bootstrap --force`.
- La plataforma ya diagnostica correctamente si falta base de datos, páginas legales, módulos o validación fiscal.

### Bloque 3: bloque real pendiente
- El entorno local aún no tiene acceso a la base de datos MySQL de producción.
- Esto significa que el sistema puede validarse a nivel de código y salud, pero no puede cerrarse como producción real sin el servidor DB operativo.

### Bloque 4: Stripe live
- La plataforma ya cuenta con la pantalla de configuración y validación fiscal obligatoria.
- Falta conectar el entorno real de Stripe y probar: checkout, webhook, reembolsos y idempotencia.

### Bloque 5: inventario y contabilidad
- Ya existen módulos de inventario y contabilidad base.
- Falta cargar stock real, costes y conciliación con las cuentas del negocio.

## Orden de trabajo real para cerrar la entrega

1. Conectar la base de datos de producción y verificar conexión con `php artisan migrate` y `app:production-check`.
2. Activar y probar Stripe live con webhook real.
3. Completar perfil fiscal y datos legales de empresa.
4. Cargar inventario inicial con costes y ubicaciones reales.
5. Validar flujo de compra, pago, factura, stock y auditoría de acciones críticas.
6. Hacer release final y activar monitorización de producción.

## Criterio de salida del proyecto

La salida final debe entenderse como un sistema de negocio real solo cuando:

- el entorno de BD es operativo,
- la colección legal y fiscal está cerrada,
- los pagos reales funcionan en production,
- el inventario contabiliza fielmente las operaciones,
- y la plataforma ha superado al menos un flujo de negocio end-to-end con validación real.
