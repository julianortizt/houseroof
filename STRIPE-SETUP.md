# 💳 Guía de Configuración de Stripe - Costa Resin & Construction

## ¿Qué es Stripe?

Stripe es una pasarela de pago online que permite cobrar con tarjeta de crédito/débito 
de forma segura, cumple con PSD2/SCA (obligatorio en la UE), y cobra comisión por 
transacción (1.4% + 0.25€ para tarjetas europeas).

---

## Paso 1: Crear cuenta de Stripe (5 minutos)

1. Ve a https://dashboard.stripe.com/register
2. Regístrate con tu email y datos de la empresa:
   - **Nombre de empresa**: Costa Resin & Construction
   - **País**: España
   - **Moneda**: EUR (Euro)
3. Verifica tu email
4. Completa los datos de la empresa (NIF/CIF, dirección, banco)

---

## Paso 2: Obtener claves de prueba

1. Ve a https://dashboard.stripe.com/test/apikeys
2. Copia las siguientes claves:

```
pk_test_YOUR_KEY_HERE    (Publishable key)
sk_test_YOUR_KEY_HERE    (Secret key)
```

3. Pegarlas en `.env`:

```env
STRIPE_KEY=pk_test_TU_CLAVE_AQUI
STRIPE_SECRET=sk_test_TU_CLAVE_AQUI
STRIPE_WEBHOOK_SECRET=whsec_TU_SECRETO
```

---

## Paso 3: Configurar Webhook (para confirmar pagos)

1. Ve a https://dashboard.stripe.com/test/webhooks
2. Click "Add endpoint"
3. URL: `https://TU_DOMINIO.com/webhook/stripe`
4. Events to send:
   - `checkout.session.completed`
   - `payment_intent.payment_failed`
5. Copia el "Signing secret" (whsec_xxx) a .env como STRIPE_WEBHOOK_SECRET

---

## Paso 4: Tarjetas de prueba

En modo test, usa estas tarjetas:

| Tarjeta | Número | Resultado |
|---------|--------|-----------|
| ✅ Visa成功 | 4242 4242 4242 4242 | Pago exitoso |
| ✅ Visa (SCA) | 4000 0025 0000 3155 | Requiere autenticación 3D Secure |
| ❌ Rechazada | 4000 0000 0000 0002 | Pago rechazado |
| ❌ Sin fondos | 4000 0000 0000 9995 | Fondos insuficientes |

- **Fecha**: Cualquier fecha futura (ej: 12/28)
- **CVC**: Cualquier 3 dígitos (ej: 123)
- **Nombre**: Cualquier nombre

---

## Paso 5: Activar modo LIVE (producción)

Cuando estés listo para cobrar de verdad:

1. Ve a https://dashboard.stripe.com/account/activate
2. Completa la verificación de identidad
3. Activa el modo "Live" en https://dashboard.stripe.com/test/apikeys
4. Copia las claves LIVE:

```
pk_live_REDACTED
sk_live_REDACTED
```

5. Actualiza `.env` con las claves LIVE
6. Configura webhook con URL de producción

---

## Comisiones de Stripe en España

| Tipo de tarjeta | Comisión |
|----------------|----------|
| Tarjetas europeas (EEA) | 1.4% + 0.25€ |
| Tarjetas internacionales | 2.9% + 0.25€ |
| Tarjetas refresh (SCA) | Sin coste adicional |
| Reembolsos | Sin coste adicional |

---

## Alternativa: Pago por transferencia bancaria

Si el cliente paga directamente al dueño por:
- Transferencia bancaria
- Bizum
- Efectivo en entrega

Se puede configurar en el admin para que el pedido se marque como 
"pendiente de pago" y el admin lo confirme manualmente.

---

## Solución de Problemas

### "Stripe key is invalid"
- Verifica que pk_test_ empiece con "pk_test_"
- No incluir espacios ni saltos de línea

### "No such customer"
- El cliente debe estar autenticado para usar Customer Portal
- Para checkout con guest, no se necesita customer

### Webhook no funciona
- Verifica que la URL sea HTTPS
- El dominio debe estar verificado en Stripe
- Revisa los logs en dashboard.stripe.com/webhooks

### Pago falla en movil
- Asegúrate de que Stripe.js esté cargado
- Verifica que no hay bloqueadores de anuncios activos
