# 🔧 Guía Completa: Configurar Stripe para Costa Resin & Construction

## 📋 Índice

1. [Crear cuenta de Stripe](#1-crear-cuenta)
2. [Obtener claves de API](#2-obtener-claves)
3. [Configurar en el proyecto](#3-configurar-en-el-proyecto)
4. [Configurar webhook](#4-configurar-webhook)
5. [Probar el checkout](#5-probar-el-checkout)
6. [Activar modo producción](#6-activar-modo-produccion)

---

## 1. Crear cuenta de Stripe

1. Ve a **https://dashboard.stripe.com/register**
2. Completa el formulario con:
   - Email
   - Contraseña
   - País: **España**
3. Verifica tu email
4. Completa los datos de la empresa:
   - Nombre: **Costa Resin & Construction**
   - Tipo: **Persona física** o **Empresa**
   - CIF/NIF
   - Dirección: Benissa, Alicante, España

---

## 2. Obtener claves de API

1. Ve a **https://dashboard.stripe.com/test/apikeys**
2. Copia las siguientes claves:

```
pk_test_xxxxx...  → Public Key (se usa en frontend)
sk_test_xxxxx...  → Secret Key (se usa en backend)
```

---

## 3. Configurar en el proyecto

Edita el archivo `.env` en la raíz del proyecto:

```env
STRIPE_KEY=pk_test_TU_PUBLIC_KEY_AQUI
STRIPE_SECRET=sk_test_TU_SECRET_KEY_AQUI
STRIPE_WEBHOOK_SECRET=whsec_xxxxx (ver paso 4)
```

---

## 4. Configurar webhook

### En desarrollo local (con Stripe CLI):

1. Instala Stripe CLI: https://stripe.com/docs/stripe-cli
2. Ejecuta:
   ```bash
   stripe listen --forward-to localhost:8000/stripe/webhook
   ```
3. Copia el `whsec_xxxxx` que aparece en la consola

### En producción (Hostinger):

1. Ve a **https://dashboard.stripe.com/webhooks**
2. Click "Add endpoint"
3. URL: `https://tudominio.com/stripe/webhook`
4. Eventos a escuchar:
   - `checkout.session.completed`
   - `payment_intent.payment_failed`
5. Copia el signing secret (`whsec_xxxxx`)

---

## 5. Probar el checkout

### Modo test (desarrollo):

1. Inicia el servidor:
   ```bash
   php artisan serve
   ```

2. Abre http://localhost:8000/shop

3. Añade un producto al carrito

4. Ve al checkout y selecciona **"Tarjeta (Stripe)"**

5. Usa estas tarjetas de prueba:

   | Tarjeta | Número | CVV | Fecha |
   |---------|--------|-----|-------|
   | ✅ Éxito | `4242 4242 4242 4242` | 123 | 12/28 |
   | ❌ Rechazada | `4000 0000 0000 0002` | 123 | 12/28 |
   | 🔐 Requiere 3D Secure | `4000 0025 0000 3155` | 123 | 12/28 |

6. Completa el pago y verifica:
   - Email de confirmación recibido
   - Pedido creado en admin (`/admin/orders`)
   - Estado del pago actualizado

---

## 6. Activar modo producción

Cuando estés listo para vender:

1. Ve a **https://dashboard.stripe.com/go-live**
2. Completa la verificación de identidad
3. Obtén las claves de producción:
   - `pk_live_xxxxx`
   - `sk_live_xxxxx`
4. Actualiza en `.env` (en Hostinger)

---

## ⚠️ Notas importantes

- **PSD2/SCA**: Stripe maneja automáticamente la autenticación fuerte para clientes europeos
- **Comisiones**: ~1.4% + 0.25€ por transacción en Europa
- **Retiros**: Los fondos llegan a tu banco en 2 días hábiles
- **Soporte**: chat en dashboard.stripe.com

---

## 📧 Configurar email transaccional

Para que Stripe envíe recibos automáticos:

1. Ve a **Settings > Customer emails**
2. Activa: "Send receipts for successful payments"
3. Personaliza el logo y colores

---

## 🔗 Recursos útiles

- [Documentación Stripe](https://stripe.com/docs)
- [Panel de pruebas](https://dashboard.stripe.com/test/dashboard)
- [Guía de webhooks](https://stripe.com/docs/webhooks)
- [Tarjetas de prueba](https://stripe.com/docs/testing#cards)
