# Estado del sistema y requerimientos para el módulo ERP

## 1. Resumen ejecutivo

El sistema actual ya está operativo como una base comercial y operativa para la gestión de proyectos, catálogo, cotizaciones, presupuestos, facturación interna y contabilidad base. Sin embargo, aún no constituye un ERP completo ni una contabilidad fiscal/legal de producción.

La capa actual cumple una función de control interno de negocio, con validaciones financieras y asientos contables básicos, pero todavía falta la parte imprescindible para convertirlo en un ERP de empresa real: proveedores, compras, flujo fiscal completo, cierre contable, banca y reportes de negocio.

## 2. Estado del sistema

### 2.1. Estado funcional

Sí está funcionando:
- frontend comercial y público
- catálogo de productos y ecommerce parcial
- gestión de proyectos / obra / cotización
- módulos de inventario y operaciones internas
- facturación en borrador
- contabilidad interna de doble entrada
- perfiles fiscales básicos
- validación de módulos por plataforma
- panel administrativo con roles y permisos

### 2.2. Estado no completo

Todavía no está listo como ERP de producción real porque faltan módulos y validaciones clave:
- proveedores y compras
- libro mayor avanzado
- cierre de ejercicio
- IVA/retenciones realistas y completos
- conciliación bancaria
- flujo fiscal/legal real para emisión de documentos válidos
- reportes de negocio y control financiero avanzado

## 3. Qué hace el módulo contable actual

El módulo contable implementado es un sistema interno de asiento contable de doble entrada. Su finalidad es:
- crear cuentas contables
- registrar apuntes de debe y haber
- validar que los asientos cuadran
- publicar asientos
- revertir asientos
- generar un balance de comprobación
- enlazar los movimientos contables con facturas emitidas

La lógica principal está en:
- app/Services/AccountingJournalService.php
- app/Services/InvoiceDraftService.php
- app/Services/FiscalProfile.php
- app/Services/PlatformModules.php

Este módulo es útil para organizar la contabilidad interna, pero no reemplaza un ERP o una contabilidad fiscal legal completa.

## 4. Lo que falta para el módulo ERP

### 4.1. Módulo de proveedores y compras
Requerido para una empresa real:
- proveedores
- compras
- albaranes
- órdenes de compra
- pagos a proveedores
- imputación contable de compras

### 4.2. Módulo fiscal y legal completo
Necesario para emisión real y cumplimiento:
- series de facturas por ejercicio
- IVA por tipo de operación
- retenciones
- facturas de compra
- libros fiscales
- estructura legal para declaración

### 4.3. Libro mayor y cierre contable
Necesario para la gestión financiera real:
- libro mayor por cuenta
- balances por periodo
- cierre de ejercicio
- asientos de regularización
- amortizaciones
- resultados por ejercicio

### 4.4. Gestión bancaria
Necesario para control del flujo real:
- cuentas bancarias
- extractos
- conciliación bancaria
- movimientos de caja y banco
- pagos y cobros con asiento automático

### 4.5. Reportes de negocio
Necesario para decidir operativa y financiera:
- balance general
- cuenta de resultados
- flujo de caja
- previsión de cobros/pagos
- indicadores por proyecto, producto y cliente

### 4.6. Control operativo ERP
Necesario para producción:
- unidades de negocio
- centros de coste
- departamentos
- permisos por área
- aprobaciones de gasto y cobro

## 5. Estado de prioridad del ERP

### Prioridad 1: imprescindible
- proveedores y compras
- flujo fiscal real
- IVA/retenciones
- libro mayor y cierre
- conciliación bancaria
- reportes de negocio

### Prioridad 2: altamente recomendable
- gestión de caja
- pagos automáticos
- aprobaciones y auditoría
- dashboard financiero

### Prioridad 3: diferencial / avanzado
- BI financiero
- integración con ERP externo
- automatización de facturación
- trazabilidad completa por proyecto y centro de coste

## 6. Requerimientos concretos para implementar el módulo ERP

### 6.1. Módulos mínimos necesarios
1. Proveedores
2. Compras
3. Facturación fiscal real
4. Cuentas bancarias y conciliación
5. Libro mayor / cierre
6. IVA y retenciones
7. Dashboard financiero
8. Reportes contables

### 6.2. Requisitos funcionales
- creación y edición de proveedores
- gestión de compras y gastos
- facturas de compra y venta
- numeración fiscal por serie
- validación de IVA por tipo
- flujo de cobro y pago
- contabilidad por proyecto
- control de presupuesto vs real

### 6.3. Requisitos técnicos
- migración de esquema con nuevas tablas ERP
- modelos: Supplier, PurchaseOrder, PurchaseInvoice, BankAccount, CashMovement, FinancialReport
- servicios: PurchaseService, BankReconciliationService, TaxService, ReportingService
- control de permisos por rol y módulo
- auditoría y trazabilidad de cada movimiento
- exportación CSV/PDF de informes

## 7. Calificación real del sistema actual

### Sistema actual
- Comercial: muy bueno
- Operativo: bueno
- Financiero básico: aceptable
- Fiscal real: insuficiente
- ERP completo: no

### Calificación orientativa
- Completitud comercial: 8/10
- Contabilidad interna: 7/10
- ERP real: 3/10
- Producción empresarial real: 4/10

## 8. Conclusión

El sistema ya no es un prototipo básico, pero tampoco es un ERP productivo completo. Tiene una base sólida de negocio y contabilidad interna, pero requiere una segunda capa de trabajo enfocada en ERP financiero real: proveedores, compras, fiscalidad, impuestos, conciliación bancaria y reportes de negocio.

El siguiente paso lógico no es seguir ampliando UI o contenido, sino construir la capa ERP mínima viable antes de considerar el sistema “comercialmente listo para producción real”.
