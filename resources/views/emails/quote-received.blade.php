<x-mail::message>
# Nueva solicitud de cotización

Se ha recibido una nueva solicitud de cotización desde el sitio web.

**Nombre:** {{ $quote->name }}
**Email:** {{ $quote->email }}
**Teléfono:** {{ $quote->phone ?? 'No proporcionado' }}
**Empresa:** {{ $quote->company ?? 'N/A' }}

**Servicio solicitado:** {{ $quote->service_type_label }}
**Área estimada:** {{ $quote->area_m2 ? $quote->area_m2 . ' m²' : 'No especificada' }}

**Descripción del proyecto:**
{{ $quote->description ?? 'Sin descripción' }}

**Dirección:**
{{ $quote->address ?? '' }} {{ $quote->city ?? '' }} {{ $quote->postal_code ?? '' }}

<x-mail::button :url="route('filament.admin.resources.maps-calculator.quotes.edit', $quote)">
Ver en el panel de administración
</x-mail::button>

Saludos,<br>
{{ config('platform.public_name') }}
</x-mail::message>
