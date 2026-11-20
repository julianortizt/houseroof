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
    $heroSubtitle = $text('hero', 'subtitle', 'Limpieza, mantenimiento y reparación de cubiertas para edificios residenciales, comerciales e industriales en toda Europa.', 'Roof cleaning, maintenance and repair for residential, commercial and industrial buildings across Europe.');

    $resinBadge = $text('resin', 'badge', 'Limpieza de techos', 'Roof cleaning');
    $resinTitle = $text('resin', 'title', 'Limpieza y mantenimiento de cubiertas', 'Roof cleaning and maintenance');
    $resinP1    = $text('resin', 'p1', 'Eliminamos musgo, líquenes y suciedad de tejas, pizarra y cubiertas planas con métodos seguros para el material.', 'We remove moss, lichen and dirt from tiles, slate and flat roofs using material-safe methods.');
    $resinP2    = $text('resin', 'p2', 'Revisamos canalones y bajantes y entregamos un informe fotográfico con el estado de la cubierta.', 'We inspect gutters and downpipes and provide a photographic report on the condition of the roof.');
    $resinContent = $locale === 'es' ? $CB::get('services', 'resin', 'content', 'es') : null;
    $resinImage   = $CB::get('services', 'resin', 'image', $locale);
    $resinAlt     = $text('resin', 'alt', 'Limpieza de techos', 'Roof cleaning');

    $graniteBadge = $text('granite', 'badge', 'Reparación de techos', 'Roof repair');
    $graniteTitle = $text('granite', 'title', 'Reparación de goteras e impermeabilización', 'Leak repair and waterproofing');
    $graniteP1    = $text('granite', 'p1', 'Localizamos el origen de la gotera y reparamos juntas, tejas, chimeneas y limahoyas.', 'We trace the source of the leak and repair joints, tiles, chimneys and valleys.');
    $graniteP2    = $text('granite', 'p2', 'Aplicamos tratamientos impermeabilizantes compatibles con cada tipo de cubierta y verificamos la estanqueidad.', 'We apply waterproofing treatments compatible with every roof type and verify watertightness.');
    $graniteContent = $locale === 'es' ? $CB::get('services', 'granite', 'content', 'es') : null;
    $graniteImage   = $CB::get('services', 'granite', 'image', $locale);
    $graniteAlt     = $text('granite', 'alt', 'Reparación de techos', 'Roof repair');

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

        {{-- Roof cleaning --}}
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
                    <div class="flex h-96 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 p-8">
                        <p class="text-center text-lg font-semibold text-white">{{ $resinAlt }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Roof repair --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div class="order-2 lg:order-1 rounded-2xl overflow-hidden">
                @if($graniteImage)
                    <img src="{{ upload_url($graniteImage) }}" alt="{{ $graniteAlt }}" class="w-full h-96 object-cover">
                @else
                    <div class="flex h-96 items-center justify-center rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950 p-8">
                        <p class="text-center text-lg font-semibold text-white">{{ $graniteAlt }}</p>
                    </div>
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
