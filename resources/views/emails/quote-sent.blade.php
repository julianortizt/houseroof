@component('mail::message')
# Presupuesto {{ $quote->formatted_number }}

Estimado/a **{{ $quote->name }}**,

Le enviamos el presupuesto **{{ $quote->formatted_number }}** correspondiente a su solicitud de **{{ $quote->service_type_label }}**.

{{-- Detalles del presupuesto --}}
@if($quote->company)
**Empresa:** {{ $quote->company }}
@endif
@if($quote->email)
**Email:** {{ $quote->email }}
@endif
@if($quote->phone)
**Teléfono:** {{ $quote->phone }}
@endif
@if($quote->address)
**Dirección:** {{ $quote->address }}{{ $quote->city ? ', ' . $quote->city : '' }}{{ $quote->postal_code ? ' ' . $quote->postal_code : '' }}
@endif

**Servicio:** {{ $quote->service_type_label }}
@if($quote->area_m2)
**Área estimada:** {{ number_format($quote->area_m2, 2, ',', '.') }} m²
@endif
@if($quote->description)
**Descripción:** {{ $quote->description }}
@endif

---

### Detalle del presupuesto

| Concepto | Cant. | P. Unit. | Dto. | Total |
|---|---:|---:|---:|---:|
@foreach($quote->items()->get() as $item)
| {{ $item->name }} | {{ number_format($item->quantity, 2, ',', '.') }} | {{ number_format($item->unit_price, 2, ',', '.') }} € | {{ number_format($item->discount_percent ?? 0, 2, ',', '.') }}% | {{ number_format($item->total_price, 2, ',', '.') }} € |
@endforeach

---

**Subtotal:** {{ $quote->formatted_subtotal }}
@if($quote->tax_percent > 0)
**IVA ({{ $quote->tax_percent }}%):** {{ $quote->formatted_tax_amount }}
@endif
**Total:** {{ $quote->formatted_total }}
@if($quote->deposit_target_cents > 0)
**Anticipo inicial ({{ number_format($quote->deposit_percent, 2, ',', '.') }}%):** {{ number_format($quote->deposit_target_cents / 100, 2, ',', '.') }} €
**Saldo restante:** se solicitará cuando finalice el trabajo.
@endif

---

**Válido hasta:** {{ $quote->valid_until->format('d/m/Y') }}

---

@php
    $portalUrl = $quote->project?->client?->portal_url;
@endphp

@if($portalUrl)
Puede seguir el estado de su proyecto en su portal privado:

@component('mail::button', ['url' => $portalUrl])
Ver mi portal
@endcomponent

---

@endif

Adjuntamos el presupuesto en formato PDF para su revisión.

Quedamos a su disposición para cualquier consulta.

Saludos cordiales,

**{{ config('platform.public_name') }}**<br>
@if($phone = \App\Models\Setting::get('site_phone'))
{{ $phone }}<br>
@endif
@if($email = \App\Models\Setting::get('site_email'))
{{ $email }}<br>
@endif
{{ config('app.url') }}

@endcomponent