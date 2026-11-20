@extends('layouts.app')

@php
    $t = fn (string $key): string => \App\Support\RoofContent::home($key);
    $image = fn (string $key): ?string => \App\Support\RoofContent::image('home', 'roof', $key);
    $brand = config('platform.public_name');
    $seoTitle = \App\Models\Setting::get('seo_title', '');
    $seoDescription = \App\Models\Setting::get('seo_description', '');
@endphp

@section('title', $seoTitle ?: ($brand . ' — ' . $t('hero_title')))
@section('meta_description', $seoDescription ?: __('meta_home_desc'))
@section('meta_keywords', \App\Models\Setting::get('seo_keywords', '') ?: __('meta_home_kw'))

@section('content')
{{-- ═══════════════ HERO ═══════════════ --}}
<section id="home-hero" class="relative isolate overflow-hidden bg-slate-950 text-white">
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(circle_at_75%_20%,rgba(217,119,6,.22),transparent_55%),linear-gradient(135deg,#0f172a_0%,#020617_100%)]" aria-hidden="true"></div>
    <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 py-20 sm:px-6 sm:py-24 lg:grid-cols-[1.05fr_.95fr] lg:px-8 lg:py-28">
        <div>
            <p class="inline-flex items-center gap-2 rounded-full border border-amber-400/25 bg-amber-400/10 px-4 py-2 text-sm font-semibold text-amber-200">
                <span class="h-2 w-2 rounded-full bg-amber-400"></span>
                {{ $t('hero_badge') }}
            </p>
            <h1 class="mt-7 max-w-3xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">
                {{ $t('hero_title') }}
            </h1>
            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                {{ $t('hero_subtitle') }}
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ e($t('hero_primary_url')) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-amber-400">
                    {{ $t('hero_primary_cta') }}
                    <svg aria-hidden="true" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7"/></svg>
                </a>
                <a href="{{ e($t('hero_secondary_url')) }}" class="inline-flex items-center justify-center rounded-lg border border-white/20 px-6 py-3.5 font-semibold text-white transition hover:bg-white/10">
                    {{ $t('hero_secondary_cta') }}
                </a>
            </div>
            <p class="mt-5 text-sm text-slate-400">{{ $t('hero_note') }}</p>
        </div>

        <div class="relative mx-auto w-full max-w-xl">
            <div class="absolute -inset-6 rounded-[2.5rem] bg-amber-500/10 blur-3xl" aria-hidden="true"></div>
            @if($image('hero_image'))
                <img src="{{ $image('hero_image') }}" alt="{{ $t('hero_title') }}" class="relative rounded-3xl border border-white/10 object-cover shadow-2xl">
            @else
                <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-slate-900/70 p-6 shadow-2xl sm:p-8">
                    <div class="relative flex h-72 flex-col justify-between overflow-hidden rounded-2xl bg-gradient-to-b from-sky-900/70 via-slate-800 to-slate-900 p-6 sm:h-80">
                        <div class="absolute inset-x-0 top-10 flex justify-center" aria-hidden="true">
                            <svg viewBox="0 0 320 150" class="w-full max-w-sm drop-shadow-2xl" fill="none">
                                <path d="M20 82 160 18l140 64" stroke="#fbbf24" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M48 72v66h224V72" stroke="#94a3b8" stroke-width="6" stroke-linejoin="round"/>
                                <path d="M104 138V96h48v42M188 138V96h48v42" fill="#0f172a" stroke="#64748b" stroke-width="4"/>
                                <path d="M20 82 160 18l140 64" stroke="#d97706" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="self-start rounded-full bg-slate-950/80 px-3 py-1.5 text-xs font-semibold tracking-wide text-amber-200">
                            {{ $brand }}
                        </span>
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[.18em] text-amber-300">{{ $t('services_kicker') }}</p>
                            <p class="mt-3 max-w-xs text-2xl font-bold leading-tight text-white">{{ $t('services_title') }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3 pt-5 text-center">
                        @foreach([1, 2, 3] as $n)
                            <div>
                                <p class="text-lg font-bold text-white">{{ $t("stat_{$n}_value") }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $t("stat_{$n}_label") }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ═══════════════ TRUST BAR ═══════════════ --}}
<section id="home-trust" class="border-b border-slate-200 bg-white">
    <div class="mx-auto grid max-w-7xl gap-4 px-4 py-6 text-sm font-semibold text-slate-700 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
        @foreach([1, 2, 3, 4] as $n)
            <p class="flex items-center justify-center gap-2 lg:justify-start">
                <span class="text-amber-600">0{{ $n }}</span>
                {{ $t("trust_{$n}") }}
            </p>
        @endforeach
    </div>
</section>

{{-- ═══════════════ SERVICIOS ═══════════════ --}}
<section id="home-services" class="bg-slate-50 py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="font-semibold uppercase tracking-wider text-amber-700">{{ $t('services_kicker') }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $t('services_title') }}</h2>
            <p class="mt-4 text-lg leading-8 text-slate-600">{{ $t('services_subtitle') }}</p>
        </div>

        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([1, 2, 3, 4] as $n)
                @php($serviceImage = $image("service_{$n}_image"))
                <article class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg">
                    <div class="relative h-40 overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-amber-950">
                        @if($serviceImage)
                            <img src="{{ $serviceImage }}" alt="{{ $t("service_{$n}_title") }}" class="h-full w-full object-cover">
                        @else
                            <svg viewBox="0 0 240 120" class="h-full w-full opacity-90" fill="none" aria-hidden="true">
                                <path d="M20 70 120 24l100 46" stroke="#fbbf24" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M40 62v46h160V62" stroke="#475569" stroke-width="5"/>
                                <path d="M20 70 120 24l100 46" stroke="#b45309" stroke-width="2"/>
                            </svg>
                        @endif
                        <span class="absolute left-4 top-4 rounded-full bg-slate-950/80 px-3 py-1 text-xs font-semibold text-amber-200">{{ $t("service_{$n}_badge") }}</span>
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-slate-950">{{ $t("service_{$n}_title") }}</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">{{ $t("service_{$n}_text") }}</p>
                        <a href="{{ e($t("service_{$n}_url")) }}" class="mt-5 inline-flex items-center font-semibold text-amber-800 hover:text-amber-900">
                            {{ $t("service_{$n}_cta") }} <span class="ml-2" aria-hidden="true">→</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════ PRODUCTOS (módulo opcional) ═══════════════ --}}
@if(app(\App\Services\PlatformModules::class)->enabled('products'))
<section id="home-products" class="border-y border-slate-200 bg-white py-14">
    <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-6 px-4 sm:px-6 lg:flex-row lg:items-center lg:px-8">
        <div class="max-w-2xl">
            <h2 class="text-2xl font-bold tracking-tight text-slate-950">{{ __('Materiales y equipos para techos') }}</h2>
            <p class="mt-2 leading-7 text-slate-600">{{ __('Todo lo necesario para la limpieza, el mantenimiento y la reparación de cubiertas, disponible en nuestro catálogo.') }}</p>
        </div>
        <a href="{{ route('shop.index') }}" class="inline-flex shrink-0 items-center rounded-lg border border-amber-600 px-5 py-3 font-semibold text-amber-800 transition hover:bg-amber-50">
            {{ __('Ver productos') }} <span class="ml-2" aria-hidden="true">→</span>
        </a>
    </div>
</section>
@endif

{{-- ═══════════════ PROCESO ═══════════════ --}}
<section id="home-process" class="bg-white py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="font-semibold uppercase tracking-wider text-amber-700">{{ $t('process_kicker') }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $t('process_title') }}</h2>
            <p class="mt-4 text-lg leading-8 text-slate-600">{{ $t('process_subtitle') }}</p>
        </div>
        <ol class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @foreach([1, 2, 3, 4] as $n)
                <li class="relative rounded-2xl border border-slate-200 p-6">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-sm font-bold text-amber-800">0{{ $n }}</span>
                    <h3 class="mt-5 font-bold text-slate-950">{{ $t("step_{$n}_title") }}</h3>
                    <p class="mt-2 text-sm leading-7 text-slate-600">{{ $t("step_{$n}_text") }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ═══════════════ ZONAS ═══════════════ --}}
<section id="home-areas" class="bg-slate-950 py-20 text-white sm:py-24">
    <div class="mx-auto grid max-w-7xl gap-12 px-4 sm:px-6 lg:grid-cols-2 lg:items-center lg:px-8">
        <div>
            <p class="font-semibold uppercase tracking-wider text-amber-300">{{ $t('areas_kicker') }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">{{ $t('areas_title') }}</h2>
            <p class="mt-5 leading-8 text-slate-300">{{ $t('areas_subtitle') }}</p>
            <a href="{{ route('contact') }}" class="mt-8 inline-flex items-center justify-center rounded-lg bg-amber-500 px-5 py-3 font-semibold text-slate-950 transition hover:bg-amber-400">
                {{ $t('cta_button') }}
            </a>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @foreach([1, 2, 3, 4, 5, 6] as $n)
                <div class="flex min-h-24 items-center rounded-xl border border-white/10 bg-white/5 px-4 text-sm font-semibold text-slate-100">
                    {{ $t("area_{$n}") }}
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════ POR QUÉ ELEGIRNOS ═══════════════ --}}
<section id="home-why" class="bg-white py-20 sm:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <p class="font-semibold uppercase tracking-wider text-amber-700">{{ $t('why_kicker') }}</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $t('why_title') }}</h2>
            <p class="mt-4 text-lg leading-8 text-slate-600">{{ $t('why_subtitle') }}</p>
        </div>
        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach([1, 2, 3] as $n)
                <article class="rounded-2xl border border-slate-200 bg-slate-50 p-7">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-600 text-white" aria-hidden="true">✓</span>
                    <h3 class="mt-5 text-lg font-bold text-slate-950">{{ $t("why_{$n}_title") }}</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">{{ $t("why_{$n}_text") }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ═══════════════ CTA FINAL ═══════════════ --}}
<section id="home-cta" class="bg-amber-50 py-16 sm:py-20">
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">{{ $t('cta_title') }}</h2>
        <p class="mx-auto mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ $t('cta_subtitle') }}</p>
        <a href="{{ e($t('cta_url')) }}" class="mt-8 inline-flex items-center justify-center rounded-lg bg-slate-950 px-7 py-3.5 font-semibold text-white transition hover:bg-slate-800">
            {{ $t('cta_button') }}
        </a>
        <p class="mt-6 text-sm text-slate-500">{{ $t('coverage_note') }}</p>
    </div>
</section>
@endsection
