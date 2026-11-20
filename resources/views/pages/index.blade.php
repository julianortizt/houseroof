@extends('layouts.app')

@php
    $locale = app()->getLocale();
    $CB = \App\Models\ContentBlock::class;

    // The Spanish site uses the admin-editable ContentBlock. Every other language
    // resolves via the translation files, and ONLY if that exact locale has a
    // translation does it use it; otherwise it falls back to an explicit English
    // string. This guarantees the page never shows Spanish outside the Spanish site.
    $text = function (string $section, string $key, string $spanish, string $english) use ($CB, $locale) {
        if ($locale === 'es') {
            return $CB::get('services', $section, $key, 'es', null, true) ?? $spanish;
        }
        return \Lang::has($spanish, $locale, false)
            ? \Lang::get($spanish, [], $locale, false)
            : $english;
    };

    $heroTitle    = $text('hero', 'title', 'Servicios', 'Services');
    $heroSubtitle = $text('hero', 'subtitle', 'Gestiona proyectos, mediciones y servicios desde un único espacio de trabajo.', 'Manage projects, measurements, and service delivery in one workspace.');

    $resinBadge = $text('resin', 'badge', 'Servicio 1', 'Service 1');
    $resinTitle = $text('resin', 'title', 'Planificación y medición de proyectos', 'Project planning and measurement');
    $resinP1    = $text('resin', 'p1', 'Registra los datos del proyecto y las necesidades del cliente desde la primera visita.', 'Capture project details and customer requirements from the first site visit.');
    $resinP2    = $text('resin', 'p2', 'Organiza las mediciones y la información de cada espacio en un solo lugar.', 'Keep measurements and space details organized in one place.');
    $resinContent = $locale === 'es' ? $CB::get('services', 'resin', 'content', 'es') : null;
    $resinImage   = $CB::get('services', 'resin', 'image', $locale);
    $resinAlt     = $text('resin', 'alt', 'Planificación de proyectos', 'Project planning');

    $graniteBadge = $text('granite', 'badge', 'Servicio 2', 'Service 2');
    $graniteTitle = $text('granite', 'title', 'Presupuestos y órdenes de trabajo', 'Estimates and work orders');
    $graniteP1    = $text('granite', 'p1', 'Convierte el alcance del proyecto en un presupuesto claro y reutilizable.', 'Turn project scope into a clear, reusable estimate.');
    $graniteP2    = $text('granite', 'p2', 'Coordina las tareas y registra el avance hasta completar el trabajo.', 'Coordinate tasks and track progress through completion.');
    $graniteContent = $locale === 'es' ? $CB::get('services', 'granite', 'content', 'es') : null;
    $graniteImage   = $CB::get('services', 'granite', 'image', $locale);
    $graniteAlt     = $text('granite', 'alt', 'Orden de trabajo', 'Work order');

    $ctaTitle  = $text('cta', 'title', '¿Hablamos de tu proyecto?', 'Let’s discuss your project');
    $ctaText   = $text('cta', 'text', 'Comparte tus necesidades y nuestro equipo te ayudará a definir los siguientes pasos.', 'Share your requirements and our team will help you define the next steps.');
    $ctaButton = $text('cta', 'button_text', 'Contactar', 'Contact us');
@endphp

@section('title', $heroTitle . ' — ' . config('platform.public_name'))

@section('content')
{{-- Hero --}}
<section class="bg-gray-900 text-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl lg:text-5xl font-bold mb-4">{!! $heroTitle !!}</h1>
        <p class="text-xl text-gray-300 max-w-3xl mx-auto">{!! $heroSubtitle !!}</p>
    </div>
</section>

<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Resin Installation --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <span class="text-amber-600 font-bold uppercase tracking-wider text-sm">{!! $resinBadge !!}</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-6">{!! $resinTitle !!}</h2>
                @if($resinContent)
                    <div class="text-gray-600 prose prose-gray max-w-none">{!! $resinContent !!}</div>
                @else
                    <div class="text-gray-600 space-y-4">
                        <p>{{ $resinP1 }}</p>
                        <p>{{ $resinP2 }}</p>
                    </div>
                @endif
            </div>
            <div class="rounded-2xl overflow-hidden">
                @if($resinImage)
                    <img src="{{ upload_url($resinImage) }}" alt="{{ $resinAlt }}" class="w-full h-96 object-cover">
                @else
                    <img src="{{ asset('images/service-resin.jpg') }}" alt="{{ $resinAlt }}" class="w-full h-96 object-cover">
                @endif
            </div>
        </div>

        {{-- Granite Installation --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="order-2 lg:order-1 rounded-2xl overflow-hidden">
                @if($graniteImage)
                    <img src="{{ upload_url($graniteImage) }}" alt="{{ $graniteAlt }}" class="w-full h-96 object-cover">
                @else
                    <img src="{{ asset('images/service-granite.jpg') }}" alt="{{ $graniteAlt }}" class="w-full h-96 object-cover">
                @endif
            </div>
            <div class="order-1 lg:order-2">
                <span class="text-amber-600 font-bold uppercase tracking-wider text-sm">{!! $graniteBadge !!}</span>
                <h2 class="text-3xl font-bold text-gray-900 mt-2 mb-6">{!! $graniteTitle !!}</h2>
                @if($graniteContent)
                    <div class="text-gray-600 prose prose-gray max-w-none">{!! $graniteContent !!}</div>
                @else
                    <div class="text-gray-600 space-y-4">
                        <p>{{ $graniteP1 }}</p>
                        <p>{{ $graniteP2 }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- CTA --}}
        <div class="bg-amber-50 border-2 border-amber-200 rounded-2xl p-8 lg:p-12 text-center">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">{!! $ctaTitle !!}</h3>
            <p class="text-gray-600 mb-6 max-w-2xl mx-auto">{!! $ctaText !!}</p>
            <a href="{{ route('quote.create') }}" class="inline-flex items-center bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-8 rounded-lg transition">
                {{ $ctaButton }}
            </a>
        </div>
    </div>
</section>
@endsection
