<x-mail::message>
# Confirmación de pedido

¡Gracias por su compra! Ha recibido un pedido correctamente.

**Número de pedido:** {{ $order->order_number }}

## Detalles del pedido

@foreach($order->items as $item)
- **{{ $item->product_name }}**{{ $item->variant_name ? ' - ' . $item->variant_name : '' }} x{{ $item->quantity }} — {{ number_format($item->total_price, 2, ',', '.') }} €
@endforeach

---

**Subtotal:** {{ number_format($order->subtotal, 2, ',', '.') }} €
**Envío:** {{ number_format($order->shipping_cost, 2, ',', '.') }} €
**IVA (21%):** {{ number_format($order->tax, 2, ',', '.') }} €
**Total:** **{{ number_format($order->total, 2, ',', '.') }} €**

## Dirección de envío

{{ $order->customer_name }}<br>
{{ $order->shipping_address_line_1 }}<br>
@if($order->shipping_address_line_2){{ $order->shipping_address_line_2 }}<br>@endif
{{ $order->shipping_postal_code }} {{ $order->shipping_city }}<br>
{{ $order->shipping_country }}

---

@if($order->payment_status === 'unpaid' && $order->stripe_checkout_session_id === null)
## Instrucciones de pago

@if(str_contains($order->notes ?? '', 'Transferencia bancaria'))
### 🏦 Transferencia bancaria

**Banco:** {{ config('services.bank.name', 'Tu Banco') }}
**IBAN:** {{ config('services.bank.iban', 'ES00 0000 0000 0000 0000 0000') }}
**Titular:** {{ config('services.bank.holder', config('platform.public_name')) }}
**Concepto:** {{ $order->order_number }}
**Importe:** {{ number_format($order->total, 2, ',', '.') }} €

> Por favor, realice la transferencia en un plazo máximo de **5 días laborables**. 
> Incluya el número de pedido como concepto del pago.

@elseif(str_contains($order->notes ?? '', 'Bizum'))
### 📱 Bizum

**Número de Bizum:** {{ config('services.bizum.number', '654195493') }}
**Titular:** {{ config('services.bizum.holder', config('platform.public_name')) }}
**Importe:** {{ number_format($order->total, 2, ',', '.') }} €
**Concepto:** {{ $order->order_number }}

> Envíe el Bizum indicando el número de pedido como concepto.

@endif

Una vez recibido el pago, procederemos al envío de su pedido.
@endif

---

Si tiene alguna pregunta, no dude en contactarnos.

Saludos,<br>
{{ config('platform.public_name') }}
</x-mail::message>
