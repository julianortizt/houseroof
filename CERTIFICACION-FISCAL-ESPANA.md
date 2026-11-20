# Ruta de certificación fiscal en España

## 1) Preparación legal de la empresa
- Confirmar la forma jurídica: SL, SA, autónomo, etc.
- Verificar que la razón social y NIF coinciden con la factura y con la dirección fiscal.
- Confirmar domicilio fiscal, provincia, código postal y país.
- Revisar el correo de contacto y teléfono de la empresa.

## 2) Configuración dentro de la aplicación
- Entrar al panel de configuración fiscal.
- Completar: razón social, NIF, dirección fiscal, ciudad, provincia, país, serie, email fiscal.
- Activar "Habilitar emisión fiscal" solo cuando la empresa esté legalmente preparada.
- Validar que la serie sea única y coherente con la empresa.

## 3) Certificado digital / firma electrónica
- Obtener un certificado digital válido para la empresa (FNMT o entidad autorizada).
- Instalarlo en el equipo o servidor donde corre la app.
- Verificar la cadena de certificados y compatibilidad con el entorno de producción.

## 4) Registro y cumplimiento fiscal
- Comprobar si la actividad requiere alta en AEAT, IVA, IRPF o régimen correspondiente.
- Confirmar que la empresa está dada de alta para emitir facturas.
- Revisar si el negocio necesita Facturae, SII o conectividad con el entorno fiscal del país.

## 5) Producción y seguridad
- Desplegar la app en un servidor con dominio propio.
- Configurar `APP_URL`, certificados HTTPS y variables de entorno seguras.
- Asegurar copias de seguridad, logs y auditoría de facturas emitidas.
- Bloquear acceso a entorno de producción a usuarios no autorizados.

## 6) Verificación operativa
- Emitir una factura de prueba en entorno controlado.
- Validar numeración, serie, datos fiscales y importes.
- Verificar que el seguimiento contable y el asiento sean correctos.
- Guardar evidencia de la prueba y revisión interna.

## 7) Cierre de certificación
- Solo cuando la empresa esté registrada, el certificado sea válido y la app esté en producción, se puede habilitar emisión real.
- Emisión legal real debe realizarse con un responsable fiscal autorizado y bajo la política de la empresa.

## Nota importante
Esto es una ruta técnica y legal de preparación, no una certificación automática ni una garantía de validez tributaria. La legalidad final depende del registro real de la empresa, la firma digital y el marco fiscal aplicable.
