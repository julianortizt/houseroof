@extends('layouts.app')

@php
    $isSpanish = app()->getLocale() === 'es';
    $copy = fn (string $spanish, string $english): string => $isSpanish ? $spanish : $english;
    $businessName = config('platform.public_name');
    $productsEnabled = app(\App\Services\PlatformModules::class)->enabled('products');
@endphp

@section('title', $copy(
    'Suelos de resina, construcción y productos en Málaga y Costa Blanca | ' . $businessName,
    'Resin flooring, construction and products in Malaga and Costa Blanca | ' . $businessName
))
@section('meta_description', __('meta_home_desc'))
@section('meta_keywords', __('meta_home_kw'))

@section('content')
<section id="home-hero" class="relative isolate overflow-hidden bg-slate-950 text-white">
    <video class="hero-video absolute inset-0 -z-20 h-full w-full object-cover" autoplay muted loop playsinline preload="metadata" poster="{{ asset('videos/hero-poster.jpg') }}" aria-hidden="true">
        <source src="{{ asset('videos/hero.webm') }}" type="video/webm">
        <source src="{{ asset('videos/hero.mp4') }}" type="video/mp4">
    </video>
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-slate-950/90 via-slate-950/70 to-slate-950/50" aria-hidden="true"></div>
    <style>
        @media (prefers-reduced-motion: reduce) {
            .hero-video { display: none; }
            #home-hero { background-image: url('{{ asset('videos/hero-poster.jpg') }}'); background-size: cover; background-position: center; }
        }
    </style>
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-28">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm font-semibold text-amber-200">
                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                {{ $copy('MÁLAGA · COSTA BLANCA', 'MALAGA · COSTA BLANCA') }}
            </p>
            <h1 class="mt-7 max-w-3xl text-4xl font-bold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                {{ $copy('Pavimentos de resina, construcción y productos para transformar tus espacios.', 'Resin flooring, construction and products to transform your space.') }}
            </h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                {{ $copy('En ', 'At ') }}{{ $businessName }}{{ $copy(' atendemos proyectos en Málaga y Costa Blanca. Cuéntanos qué necesitas para tu vivienda, negocio u obra y solicita una propuesta a medida.', ' we serve projects in Malaga and Costa Blanca. Tell us what you need for your home, business or building project and request a tailored proposal.') }}
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('quote.create') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-400">
                    {{ $copy('Solicitar presupuesto', 'Request a quote') }}
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7"/>
                    </svg>
                </a>
                <a href="{{ route('services') }}" class="inline-flex items-center justify-center rounded-lg border border-white/20 px-6 py-3.5 font-semibold text-white transition hover:bg-white/10">
                    {{ $copy('Ver servicios', 'Explore services') }}
                </a>
            </div>
            <p class="mt-5 text-sm text-slate-400">
                {{ $copy('Instalación · construcción · venta de productos', 'Installation · construction · product sales') }}
            </p>
        </div>

        <div class="relative mx-auto w-full max-w-xl">
            <div class="absolute -inset-5 rounded-[2rem] bg-amber-500/10 blur-2xl" aria-hidden="true"></div>
            <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/80 p-4 shadow-2xl sm:p-6">
                <div class="rounded-2xl p-6 sm:p-8" style="background: linear-gradient(140deg, #d6c6a9 0%, #f0e9dc 34%, #b8aa94 35%, #e8dfd1 62%, #a8987e 63%, #d2c3ad 100%);">
                    <div class="flex min-h-64 flex-col justify-between rounded-xl border border-white/40 bg-slate-950/10 p-5 backdrop-blur-[1px] sm:min-h-80 sm:p-7">
                        <span class="self-start rounded-full bg-slate-950/80 px-3 py-1.5 text-xs font-semibold tracking-wide text-white">
                            {{ $copy('SUPERFICIES · INTERIOR · EXTERIOR', 'SURFACES · INTERIOR · EXTERIOR') }}
                        </span>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[.18em] text-slate-800">
                                {{ $copy('Diseñado para tu proyecto', 'Designed around your project') }}
                            </p>
                            <div class="mt-4 flex gap-2" aria-label="{{ $copy('Muestras de color decorativas', 'Decorative color samples') }}">
                                <span class="h-8 w-8 rounded-full border-2 border-white/80 bg-stone-100 shadow"></span>
                                <span class="h-8 w-8 rounded-full border-2 border-white/80 bg-stone-400 shadow"></span>
                                <span class="h-8 w-8 rounded-full border-2 border-white/80 bg-amber-800 shadow"></span>
                                <span class="h-8 w-8 rounded-full border-2 border-white/80 bg-slate-700 shadow"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 px-2 pb-2 pt-5 text-center sm:px-3">
                    <div>
                        <p class="text-sm font-bold text-white">{{ $copy('Resina', 'Resin') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $copy('Pavimentos', 'Flooring') }}</p>
                    </div>
                    <div class="border-x border-white/10">
                        <p class="text-sm font-bold text-white">{{ $copy('Construcción', 'Construction') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $copy('Proyectos', 'Projects') }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">{{ $copy('Productos', 'Products') }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $copy('Consulta disponibilidad', 'Ask about availability') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="home-trust" class="border-b border-slate-200 bg-white">
    <div class="mx-auto grid max-w-7xl gap-4 px-4 py-6 text-sm font-semibold text-slate-700 sm:grid-cols-3 sm:px-6 lg:px-8">
        <p class="flex items-center justify-center gap-2 sm:justify-start">
            <span class="text-amber-600" aria-hidden="true">01</span>
            {{ $copy('Instalación de pavimentos de resina', 'Resin flooring installation') }}
        </p>
        <p class="flex items-center justify-center gap-2 sm:justify-center">
            <span class="text-amber-600" aria-hidden="true">02</span>
            {{ $copy('Construcción y reforma', 'Construction and renovation') }}
        </p>
        <p class="flex items-center justify-center gap-2 sm:justify-end">
            <span class="text-amber-600" aria-hidden="true">03</span>
            {{ $copy('Venta y consulta de productos', 'Product sales and enquiries') }}
        </p>
    </div>
</section>

<section id="home-services" class="bg-slate-50 py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="font-semibold uppercase tracking-wider text-amber-700">{{ $copy('Soluciones para cada proyecto', 'Solutions for every project') }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">
                {{ $copy('Un equipo para pavimentos, construcción y materiales.', 'One team for flooring, construction and materials.') }}
            </h2>
            <p class="mt-4 text-lg leading-8 text-slate-600">
                {{ $copy('Revisamos contigo el alcance y las necesidades del espacio antes de preparar una propuesta.', 'We review the scope and needs of your space with you before preparing a proposal.') }}
            </p>
        </div>

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-lg font-bold text-amber-800">01</span>
                <h3 class="mt-5 text-xl font-bold text-slate-950">{{ $copy('Pavimentos de resina', 'Resin flooring') }}</h3>
                <p class="mt-3 leading-7 text-slate-600">
                    {{ $copy('Instalación de suelos de resina para espacios residenciales, comerciales y otros proyectos, según las condiciones del soporte y el uso previsto.', 'Resin floor installation for residential, commercial and other projects, subject to substrate conditions and intended use.') }}
                </p>
                <a href="{{ route('services') }}" class="mt-5 inline-flex font-semibold text-amber-800 hover:text-amber-900">
                    {{ $copy('Conocer el servicio', 'Learn about the service') }} <span class="ml-2" aria-hidden="true">→</span>
                </a>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-lg font-bold text-slate-800">02</span>
                <h3 class="mt-5 text-xl font-bold text-slate-950">{{ $copy('Construcción y reforma', 'Construction and renovation') }}</h3>
                <p class="mt-3 leading-7 text-slate-600">
                    {{ $copy('Cuéntanos el tipo de obra, el espacio y el resultado que buscas. Valoramos contigo el alcance antes de cotizar.', 'Tell us about the building work, space and result you have in mind. We will review the scope with you before quoting.') }}
                </p>
                <a href="{{ route('quote.create') }}" class="mt-5 inline-flex font-semibold text-amber-800 hover:text-amber-900">
                    {{ $copy('Consultar mi proyecto', 'Discuss my project') }} <span class="ml-2" aria-hidden="true">→</span>
                </a>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-50 text-lg font-bold text-cyan-900">03</span>
                <h3 class="mt-5 text-xl font-bold text-slate-950">{{ $copy('Venta de productos', 'Product sales') }}</h3>
                <p class="mt-3 leading-7 text-slate-600">
                    {{ $copy('Consulta productos para pavimentos y construcción, disponibilidad y opciones adecuadas para tu trabajo.', 'Ask about flooring and construction products, availability and options suited to your work.') }}
                </p>
                @if($productsEnabled)
                    <a href="{{ route('shop.index') }}" class="mt-5 inline-flex font-semibold text-amber-800 hover:text-amber-900">
                        {{ $copy('Ver productos', 'Browse products') }} <span class="ml-2" aria-hidden="true">→</span>
                    </a>
                @else
                    <a href="{{ route('quote.create') }}" class="mt-5 inline-flex font-semibold text-amber-800 hover:text-amber-900">
                        {{ $copy('Consultar productos', 'Ask about products') }} <span class="ml-2" aria-hidden="true">→</span>
                    </a>
                @endif
            </article>
        </div>
    </div>
</section>

<section id="home-projects" class="bg-white py-20 sm:py-24">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
        <div>
            <p class="font-semibold uppercase tracking-wider text-amber-700">{{ $copy('Atención local', 'Local service') }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">
                {{ $copy('Málaga y Costa Blanca', 'Malaga and Costa Blanca') }}
            </h2>
            <p class="mt-5 leading-8 text-slate-600">
                {{ $copy('Atendemos consultas para instalaciones, trabajos de construcción y productos en estas zonas. La cobertura y viabilidad se confirman según la ubicación y el alcance de cada proyecto.', 'We welcome enquiries about installation, construction work and products in these areas. Coverage and feasibility are confirmed based on each project location and scope.') }}
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ route('quote.create') }}" class="inline-flex items-center justify-center rounded-lg bg-slate-950 px-5 py-3 font-semibold text-white transition hover:bg-slate-800">
                    {{ $copy('Cuéntanos tu proyecto', 'Tell us about your project') }}
                </a>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-5 py-3 font-semibold text-slate-800 transition hover:bg-slate-50">
                    {{ $copy('Contactar', 'Contact us') }}
                </a>
            </div>
        </div>
        <div class="rounded-3xl bg-slate-950 p-8 text-white sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-[.18em] text-amber-300">{{ $businessName }}</p>
            <h3 class="mt-4 text-2xl font-bold">{{ $copy('¿Qué necesitas resolver?', 'What do you need help with?') }}</h3>
            <ul class="mt-6 space-y-4 text-slate-300">
                <li class="flex gap-3"><span class="text-amber-300" aria-hidden="true">✓</span>{{ $copy('Un nuevo pavimento o renovación de una superficie', 'A new floor or surface renovation') }}</li>
                <li class="flex gap-3"><span class="text-amber-300" aria-hidden="true">✓</span>{{ $copy('Una consulta de construcción o reforma', 'A construction or renovation enquiry') }}</li>
                <li class="flex gap-3"><span class="text-amber-300" aria-hidden="true">✓</span>{{ $copy('Información sobre productos para tu proyecto', 'Information about products for your project') }}</li>
            </ul>
            <p class="mt-7 border-t border-white/10 pt-5 text-sm leading-6 text-slate-400">
                {{ $copy('Envíanos los detalles y el equipo revisará tu solicitud. Los trabajos, disponibilidad y condiciones se confirman antes de contratar.', 'Send us the details and the team will review your enquiry. Work, availability and terms are confirmed before engagement.') }}
            </p>
        </div>
    </div>
</section>
@endsection
