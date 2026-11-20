@extends('layouts.app')

@section('meta_description', __('meta_services_desc'))
@section('meta_keywords', __('meta_services_kw'))

@php
    $locale = app()->getLocale();
    $CB = \App\Models\ContentBlock::class;

    // Spanish site uses the admin-editable ContentBlock. Every other language
    // resolves via the translation files, and ONLY if that exact locale has a
    // translation does it use it; otherwise it falls back to an explicit English
    // string. This guarantees the page never shows Spanish outside the Spanish site.
    // Always read from ContentBlock (admin-edited content) for ALL locales.
    // Falls back to hardcoded Spanish/English strings only if DB is empty.
    $text = function (string $section, string $key, string $spanish, string $english) use ($CB, $locale) {
        $value = $CB::get('services', $section, $key, $locale, null, true);
        if (!empty($value)) {
            return $value;
        }
        return $locale === 'es' ? $spanish : $english;
    };

    $heroTitle    = $text('hero', 'title', 'Servicios', 'Services');
    $heroSubtitle = $text('hero', 'subtitle', 'Gestiona proyectos, mediciones y servicios desde un único espacio de trabajo.', 'Manage projects, measurements, and service delivery in one workspace.');

    $resinBadge = $text('resin', 'badge', 'Servicio 1', 'Service 1');
    $resinTitle = $text('resin', 'title', 'Planificación y medición de proyectos', 'Project planning and measurement');
    $resinP1    = $text('resin', 'p1', 'Registra los datos del proyecto y las necesidades del cliente desde la primera visita.', 'Capture project details and customer requirements from the first site visit.');
    $resinP2    = $text('resin', 'p2', 'Organiza las mediciones y la información de cada espacio en un solo lugar.', 'Keep measurements and space details organized in one place.');
    $resinContent = $CB::get('services', 'resin', 'content', $locale);
    $resinImage   = $CB::get('services', 'resin', 'image', $locale);
    $resinAlt     = $text('resin', 'alt', 'Planificación de proyectos', 'Project planning');

    $graniteBadge = $text('granite', 'badge', 'Servicio 2', 'Service 2');
    $graniteTitle = $text('granite', 'title', 'Presupuestos y órdenes de trabajo', 'Estimates and work orders');
    $graniteP1    = $text('granite', 'p1', 'Convierte el alcance del proyecto en un presupuesto claro y reutilizable.', 'Turn project scope into a clear, reusable estimate.');
    $graniteP2    = $text('granite', 'p2', 'Coordina las tareas y registra el avance hasta completar el trabajo.', 'Coordinate tasks and track progress through completion.');
    $graniteContent = $CB::get('services', 'granite', 'content', $locale);
    $graniteImage   = $CB::get('services', 'granite', 'image', $locale);
    $graniteAlt     = $text('granite', 'alt', 'Orden de trabajo', 'Work order');

    $ctaTitle  = $text('cta', 'title', '¿Hablamos de tu proyecto?', 'Let’s discuss your project');
    $ctaText   = $text('cta', 'text', 'Comparte tus necesidades y nuestro equipo te ayudará a definir los siguientes pasos.', 'Share your requirements and our team will help you define the next steps.');
    $ctaButton = $text('cta', 'button_text', 'Contactar', 'Contact us');
@endphp

@section('title', $heroTitle . ' — ' . config('platform.public_name'))

@section('content')
{{-- Hero --}}
<section class="platform-page-hero">
    <div class="platform-page-hero__inner px-4 sm:px-6 lg:px-8 text-center">
        <span class="platform-page-kicker">{{ $locale === 'es' ? 'Flujos de trabajo' : 'Workflows' }}</span>
        <h1 class="platform-page-heading">{!! $heroTitle !!}</h1>
        <p class="platform-page-subtitle">{!! $heroSubtitle !!}</p>
    </div>
</section>

<section class="platform-page-body py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Project planning --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center mb-16 lg:mb-20">
            <div class="platform-card p-7 sm:p-9">
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
            <div class="platform-card overflow-hidden p-2">
                @if($resinImage)
                    <img src="{{ upload_url($resinImage) }}" alt="{{ $resinAlt }}" class="w-full h-96 object-cover">
                @else
                    <div class="flex h-96 items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 p-6">
                        <div class="w-full max-w-sm rounded-2xl border border-white/10 bg-white/5 p-5 text-white shadow-2xl backdrop-blur">
                            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                <span class="text-sm font-semibold">{{ $locale === 'es' ? 'Espacio del proyecto' : 'Project workspace' }}</span>
                                <x-heroicon-o-map class="h-5 w-5 text-amber-300" />
                            </div>
                            <div class="mt-5 space-y-3">
                                <div class="rounded-xl border border-white/10 bg-gray-950/50 p-4">
                                    <p class="text-xs text-gray-400">{{ $locale === 'es' ? 'Ubicación y alcance' : 'Location and scope' }}</p>
                                    <p class="mt-2 font-semibold">{{ $locale === 'es' ? 'Datos organizados' : 'Details organized' }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-xl bg-white/5 p-3"><x-heroicon-o-map class="h-5 w-5 text-cyan-300" /><p class="mt-2 text-xs text-gray-300">{{ $locale === 'es' ? 'Mediciones' : 'Measurements' }}</p></div>
                                    <div class="rounded-xl bg-white/5 p-3"><x-heroicon-o-document-text class="h-5 w-5 text-amber-300" /><p class="mt-2 text-xs text-gray-300">{{ $locale === 'es' ? 'Propuesta' : 'Proposal' }}</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Estimates and work orders --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center mb-16 lg:mb-20">
            <div class="platform-card order-2 lg:order-1 overflow-hidden p-2">
                @if($graniteImage)
                    <img src="{{ upload_url($graniteImage) }}" alt="{{ $graniteAlt }}" class="w-full h-96 object-cover">
                @else
                    <div class="flex h-96 items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-cyan-950 p-6">
                        <div class="w-full max-w-sm rounded-2xl border border-white/10 bg-white/5 p-5 text-white shadow-2xl backdrop-blur">
                            <div class="flex items-center justify-between border-b border-white/10 pb-4">
                                <span class="text-sm font-semibold">{{ $locale === 'es' ? 'Plan de trabajo' : 'Work plan' }}</span>
                                <span class="rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-semibold text-emerald-300">{{ $locale === 'es' ? 'En seguimiento' : 'In progress' }}</span>
                            </div>
                            <div class="mt-5 space-y-4">
                                @foreach([
                                    $locale === 'es' ? 'Alcance confirmado' : 'Scope confirmed',
                                    $locale === 'es' ? 'Equipo y tareas' : 'Team and tasks',
                                    $locale === 'es' ? 'Revisión de avance' : 'Progress review',
                                ] as $task)
                                    <div class="flex items-center gap-3 text-sm text-gray-200">
                                        <x-heroicon-o-check-circle class="h-5 w-5 text-emerald-400" />
                                        {{ $task }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="platform-card order-1 lg:order-2 p-7 sm:p-9">
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
        <div class="mb-12">
            <h2 class="mb-6 text-2xl font-bold text-gray-900">
                {{ $locale === 'es' ? 'Un proceso claro, de principio a fin' : 'A clear process, from start to finish' }}
            </h2>
            <x-platform-process-steps :current-step="1" />
        </div>

        <div class="platform-card border-amber-200 bg-amber-50 p-8 text-center lg:p-12">
            <h3 class="text-2xl font-bold text-gray-900 mb-4">{!! $ctaTitle !!}</h3>
            <p class="text-gray-600 mb-6 max-w-2xl mx-auto">{!! $ctaText !!}</p>
            <a href="{{ route('quote.create') }}" class="inline-flex items-center bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-8 rounded-lg transition">
                {{ $locale === 'es' ? 'Iniciar solicitud' : 'Start a request' }}
            </a>
        </div>
    </div>
</section>
@endsection
