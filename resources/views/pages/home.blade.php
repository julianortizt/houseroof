@extends('layouts.app')

@section('meta_description', __('meta_home_desc'))
@section('meta_keywords', __('meta_home_kw'))

@section('content')
 {{-- Hero Slider --}}
<section id="home-hero" x-data="heroSlider()" x-init="init()" class="relative bg-gray-900 text-white overflow-hidden h-[85vh] min-h-[600px]">
    {{-- Slides --}}
    <template x-for="(slide, index) in slides" :key="index">
        <div x-show="currentSlide === index"
             x-transition:enter="transition ease-out duration-700"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-500"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0">
            <img :src="slide.image" :alt="slide.title" class="w-full h-full object-cover object-center" loading="eager" decoding="async" fetchpriority="high">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900/80 via-gray-900/50 to-transparent"></div>
            <div class="absolute inset-0 flex items-center">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                    <div class="max-w-2xl">
                        <span class="inline-block bg-amber-600 text-white text-sm font-bold px-4 py-1.5 rounded-full mb-6" x-text="slide.badge"></span>
                        <h1 class="text-4xl lg:text-6xl font-extrabold leading-tight mb-4" x-text="slide.title"></h1>
                        <p class="text-xl text-gray-300 mb-8 leading-relaxed" x-text="slide.subtitle"></p>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <a :href="slide.ctaUrl"
                               class="inline-flex items-center justify-center bg-amber-600 hover:bg-amber-700 text-white font-bold py-4 px-8 rounded-lg text-lg transition">
                                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                                <span x-text="slide.ctaText"></span>
                            </a>
                            <a href="{{ route('quote.create') }}"
                               class="inline-flex items-center justify-center bg-white/10 hover:bg-white/20 text-white border border-white/30 font-bold py-4 px-8 rounded-lg text-lg transition">
                                {{ __('Solicitar cotización') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- Slider Controls --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-center space-x-3 z-20">
        <template x-for="(slide, index) in slides" :key="'dot-'+index">
            <button @click="goTo(index)"
                    :class="currentSlide === index ? 'bg-amber-500 w-8' : 'bg-white/50 hover:bg-white/75 w-3'"
                    class="h-3 rounded-full transition-all duration-300"></button>
        </template>
    </div>

    {{-- Navigation Arrows --}}
    <button @click="prev()" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 backdrop-blur-sm text-white p-3 rounded-full transition">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </button>
    <button @click="next()" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-white/20 hover:bg-white/40 backdrop-blur-sm text-white p-3 rounded-full transition">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </button>
</section>

 {{-- Trust Bar --}}
@php
    $trustBar = fn(int $n, string $default) => \App\Models\ContentBlock::get('home', 'trust_bar', 'item_'.$n, app()->getLocale()) ?: $default;
@endphp
<section id="home-trust" class="bg-amber-600 text-white py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center text-sm font-semibold">
            <div class="flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>{{ $trustBar(1, __('Project tracking')) }}</span>
            </div>
            <div class="flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>{{ $trustBar(2, __('Reusable templates')) }}</span>
            </div>
            <div class="flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $trustBar(3, __('Flexible workflows')) }}</span>
            </div>
            <div class="flex items-center justify-center space-x-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span>{{ $trustBar(4, __('Customer portal')) }}</span>
            </div>
        </div>
    </div>
</section>

 {{-- Value Proposition --}}
@php
    $CB = \App\Models\ContentBlock::class;
    $L = app()->getLocale();
    $vp = fn(string $key, string $default) => $CB::get('home', 'value_proposition', $key, $L) ?: $default;
@endphp
<section id="home-value" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $vp('title', __('Why choose our professional platform?')) }}</h2>
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">{{ $vp('subtitle', __('Manage projects, measurements, customer relationships, and service workflows in one place.')) }}</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center p-8 rounded-2xl bg-gray-50 hover:shadow-lg transition">
                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $vp('value_1_title', __('Configurable workflows')) }}</h3>
                <p class="text-gray-600">{{ $vp('value_1_text', __('Adapt project steps and reusable templates to your organization.')) }}</p>
            </div>
            <div class="text-center p-8 rounded-2xl bg-gray-50 hover:shadow-lg transition">
                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $vp('value_2_title', __('Reusable templates')) }}</h3>
                <p class="text-gray-600">{{ $vp('value_2_text', __('Start from consistent templates for recurring project types.')) }}</p>
            </div>
            <div class="text-center p-8 rounded-2xl bg-gray-50 hover:shadow-lg transition">
                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">{{ $vp('value_3_title', __('Track project delivery')) }}</h3>
                <p class="text-gray-600">{{ $vp('value_3_text', __('Follow projects from measurements and estimates through work orders.')) }}</p>
            </div>
        </div>
    </div>
</section>

 {{-- Product catalogue (module-controlled) --}}
@if($featuredProducts->count())
<section id="home-products" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('Productos destacados') }}</h2>
                <p class="text-gray-600 mt-2">{{ __('Los más vendidos para proyectos de pavimento decorativo') }}</p>
            </div>
            <a href="{{ route('shop.index') }}" class="hidden sm:inline-flex items-center text-amber-600 hover:text-amber-700 font-semibold">
                {{ __('Ver todos') }} →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($featuredProducts as $product)
                <a href="{{ route('shop.show', $product) }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-lg transition overflow-hidden relative {{ $product->is_coming_soon ? 'opacity-80' : '' }}">
                    @if($product->is_coming_soon)
                        <div class="absolute top-3 left-3 z-10">
                            <span class="inline-flex items-center bg-gray-900 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg">
                                <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ __('Próximamente') }}
                            </span>
                        </div>
                    @endif
                    <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                        @if($product->getMainImageUrl())
                            <img src="{{ $product->getMainImageUrl() }}" alt="{{ $product->name }}"
                                 class="w-full h-full object-contain p-2 group-hover:scale-105 transition duration-300 {{ $product->is_coming_soon ? 'grayscale-[30%]' : '' }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-xs text-amber-600 font-semibold uppercase tracking-wider mb-1">{{ $product->category->name ?? '' }}</p>
                        <h3 class="font-bold text-gray-900 group-hover:text-amber-600 transition">{{ $product->name }}</h3>
                        @if($product->short_description)
                            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $product->short_description }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="text-center mt-8 sm:hidden">
            <a href="{{ route('shop.index') }}" class="inline-flex items-center text-amber-600 hover:text-amber-700 font-semibold">
                {{ __('Ver todos los productos') }} →
            </a>
        </div>
    </div>
</section>
@endif

 {{-- Services Section --}}
@php
    $svc = fn(string $key, string $default) => \App\Models\ContentBlock::get('home', 'services_section', $key, app()->getLocale()) ?: $default;
    $svcImg = fn(string $field, string $default) => \App\Models\ContentBlock::get('home', 'services_section', $field, app()->getLocale()) ?: $default;
@endphp
<section id="home-services" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">{{ $svc('section_title', __('Project services')) }}</h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">{{ $svc('section_subtitle', __('Flexible workflows for planning, measuring, estimating, and delivering work.')) }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            {{-- Project planning --}}
            @php $resinImg = $svcImg('resin_image', ''); @endphp
            <div class="relative rounded-2xl overflow-hidden group">
                <img src="{{ $resinImg ? upload_url($resinImg) : asset('images/service-resin.jpg') }}" alt="{{ $svc('resin_alt', __('Project planning and measurement')) }}"
                     class="w-full h-80 object-cover group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="text-2xl font-bold mb-3">{{ $svc('resin_title', __('Project planning and measurement')) }}</h3>
                    <p class="text-gray-300 mb-4">{{ $svc('resin_description', __('Capture measurements and keep project details organized from the first site visit.')) }}</p>
                    <a href="{{ route('services') }}" class="inline-flex items-center text-amber-400 font-semibold hover:text-amber-300 transition">
                        {{ __('Más información') }} →
                    </a>
                </div>
            </div>

 {{-- Granite Installation --}}
            @php $graniteImg = $svcImg('granite_image', ''); @endphp
            <div class="relative rounded-2xl overflow-hidden group">
                <img src="{{ $graniteImg ? upload_url($graniteImg) : asset('images/service-granite.jpg') }}" alt="{{ $svc('granite_alt', __('Chips de granito')) }}"
                     class="w-full h-80 object-cover group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/90 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
                    <h3 class="text-2xl font-bold mb-3">{{ $svc('granite_title', __('Estimates and work orders')) }}</h3>
                    <p class="text-gray-300 mb-4">{{ $svc('granite_description', __('Turn approved project details into clear estimates and actionable work orders.')) }}</p>
                    <a href="{{ route('services') }}" class="inline-flex items-center text-amber-400 font-semibold hover:text-amber-300 transition">
                        {{ __('Más información') }} →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

 {{-- Featured Projects --}}
@if($featuredProjects->count())
<section id="home-projects" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('Proyectos realizados') }}</h2>
                <p class="text-gray-600 mt-2">{{ __('Examples of completed work') }}</p>
            </div>
            <a href="{{ route('projects.index') }}" class="hidden sm:inline-flex items-center text-amber-600 hover:text-amber-700 font-semibold">
                {{ __('Ver galería completa') }} →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($featuredProjects as $project)
                @php
                    $ptitle = $project->title;
                    if (empty($ptitle)) {
                        foreach (($project->getTranslations('title') ?? []) as $t) { if (!empty($t)) { $ptitle = $t; break; } }
                    }
                    $ploc = $project->location;
                    if (empty($ploc)) {
                        foreach (($project->getTranslations('location') ?? []) as $t) { if (!empty($t)) { $ploc = $t; break; } }
                    }
                @endphp
                <a href="{{ route('projects.show', $project) }}" class="group relative rounded-2xl overflow-hidden aspect-[4/3]">
                    @if($project->getFirstMediaUrl())
                        <img src="{{ $project->getFirstMediaUrl() }}" alt="{{ $ptitle }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-transparent to-transparent"></div>
                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                        <h3 class="text-xl font-bold">{{ $ptitle }}</h3>
                        @if($ploc)
                            <p class="text-sm text-gray-300 mt-1 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                                {{ $ploc }}
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="text-center mt-8 sm:hidden">
            <a href="{{ route('projects.index') }}" class="inline-flex items-center text-amber-600 hover:text-amber-700 font-semibold">
                {{ __('Ver todos los proyectos') }} →
            </a>
        </div>
    </div>
</section>
@else
 {{-- Fallback Projects Section (static images from WordPress) --}}
<section id="home-projects" class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('Proyectos realizados') }}</h2>
                <p class="text-gray-600 mt-2">{{ __('Examples of completed work') }}</p>
            </div>
            <a href="{{ route('projects.index') }}" class="hidden sm:inline-flex items-center text-amber-600 hover:text-amber-700 font-semibold">
                {{ __('Ver galería completa') }} →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="{{ route('projects.index') }}" class="group relative rounded-2xl overflow-hidden aspect-[4/3]">
                <img src="{{ asset('images/project-countertop.jpg') }}" alt="{{ __('Workspace project example') }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <h3 class="text-xl font-bold">{{ __('Workspace project') }}</h3>
                    <p class="text-sm text-gray-300 mt-1">{{ __('Interior project') }}</p>
                </div>
            </a>
            <a href="{{ route('projects.index') }}" class="group relative rounded-2xl overflow-hidden aspect-[4/3]">
                <img src="{{ asset('images/industrial-floor.jpg') }}" alt="{{ __('Facility project example') }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <h3 class="text-xl font-bold">{{ __('Facility project') }}</h3>
                    <p class="text-sm text-gray-300 mt-1">{{ __('Commercial project') }}</p>
                </div>
            </a>
            <a href="{{ route('projects.index') }}" class="group relative rounded-2xl overflow-hidden aspect-[4/3]">
                <img src="{{ asset('images/project-install.jpg') }}" alt="{{ __('Outdoor project example') }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 via-transparent to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <h3 class="text-xl font-bold">{{ __('Outdoor project') }}</h3>
                    <p class="text-sm text-gray-300 mt-1">{{ __('External project') }}</p>
                </div>
            </a>
        </div>
    </div>
</section>
@endif

 {{-- Blog Section --}}
@if($latestPosts->count())
<section id="home-blog" class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">{{ __('Últimas publicaciones') }}</h2>
                <p class="text-gray-600 mt-2">{{ __('Noticias, guías y consejos sobre pavimentos decorativos') }}</p>
            </div>
            <a href="{{ route('blog.index') }}" class="hidden sm:inline-flex items-center text-amber-600 hover:text-amber-700 font-semibold">
                {{ __('Ver blog') }} →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach($latestPosts as $post)
                <a href="{{ route('blog.show', $post) }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-lg transition overflow-hidden">
                    <div class="aspect-[16/9] bg-gray-100 overflow-hidden">
                        @if($post->getFirstMediaUrl())
                            <img src="{{ $post->getFirstMediaUrl() }}" alt="{{ $post->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        @endif
                    </div>
                    <div class="p-6">
                        <div class="flex items-center space-x-2 text-xs text-gray-500 mb-2">
                            @if($post->category)
                                <span class="text-amber-600 font-semibold">{{ $post->category->name }}</span>
                                <span>•</span>
                            @endif
                            <span>{{ $post->published_at?->format('d/m/Y') }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-amber-600 transition mb-2">{{ $post->title }}</h3>
                        <p class="text-gray-600 text-sm line-clamp-2">{{ $post->excerpt }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

 {{-- CTA Section --}}
@php
    $ctaTitle = \App\Models\ContentBlock::get('home', 'cta', 'cta_title', app()->getLocale())
        ?? \App\Models\ContentBlock::get('home', 'cta', 'title', app()->getLocale())
        ?? __('¿Necesita un presupuesto para su proyecto?');
    $ctaSubtitle = \App\Models\ContentBlock::get('home', 'cta', 'cta_subtitle', app()->getLocale())
        ?? \App\Models\ContentBlock::get('home', 'cta', 'subtitle', app()->getLocale())
        ?? __('Contacte con nosotros para obtener una cotización personalizada. Asesoramiento técnico gratuito para profesionales y particulares.');
    $ctaButton = \App\Models\ContentBlock::get('home', 'cta', 'cta_button', app()->getLocale())
        ?? \App\Models\ContentBlock::get('home', 'cta', 'button_text', app()->getLocale())
        ?? __('Solicitar cotización');
    $siteWhatsapp = \App\Models\Setting::get('site_whatsapp');
@endphp
<section id="home-cta" class="py-20 bg-amber-600 text-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl lg:text-4xl font-bold mb-6">{{ $ctaTitle }}</h2>
        <p class="text-xl text-amber-100 mb-8">{{ $ctaSubtitle }}</p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('quote.create') }}"
               class="inline-flex items-center justify-center bg-white text-amber-600 hover:bg-gray-100 font-bold py-4 px-8 rounded-lg text-lg transition">
                {{ $ctaButton }}
            </a>
            @if($siteWhatsapp)
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteWhatsapp) }}?text={{ urlencode(__('I would like to discuss a project.')) }}"
               target="_blank"
               class="inline-flex items-center justify-center bg-green-500 hover:bg-green-600 font-bold py-4 px-8 rounded-lg text-lg transition">
                <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                {{ __('Contactar por WhatsApp') }}
            </a>
            @endif
        </div>
    </div>
</section>
@section('scripts')
@php
    $heroValue = function (int $slide, string $field, mixed $default = null) {
        return \App\Models\ContentBlock::get('home', 'hero', "slide_{$slide}_{$field}", app()->getLocale())
            ?? \App\Models\Setting::translated("slide_{$slide}_{$field}", $default);
    };
    $heroImage = function (int $slide, string $default): string {
        // Read from ContentBlock first (admin-edited), then Setting, then fallback
        $path = \App\Models\ContentBlock::get('home', 'hero', "slide_{$slide}_image", app()->getLocale())
            ?? \App\Models\Setting::get("slide_{$slide}_image", null);

        if (empty($path)) {
            return asset($default);
        }
        if (is_string($path) && str_starts_with($path, 'http')) {
            return $path;
        }
        return upload_url($path);
    };
    $heroSlides = [];
    foreach ([1, 2, 3] as $slide) {
        $heroSlides[] = [
            'image' => $heroImage($slide, "images/" . ($slide === 1 ? 'factory-benissa.jpg' : ($slide === 2 ? 'industrial-floor.jpg' : 'service-granite.jpg'))),
            'badge' => $heroValue($slide, 'badge', ''),
            'title' => $heroValue($slide, 'title', [
                1 => __('A workspace that adapts to your team'),
                2 => __('From measurements to delivery'),
                3 => __('Tools for every stage of a project'),
            ][$slide]),
            'subtitle' => $heroValue($slide, 'subtitle', [
                1 => __('Bring projects, customers, and workflows together.'),
                2 => __('Keep estimates, tasks, and progress organized in one place.'),
                3 => __('Choose the features and appearance that fit your business.'),
            ][$slide]),
            'ctaText' => $heroValue($slide, 'cta_text', __('Contact us')),
            'ctaUrl' => $heroValue($slide, 'cta_url', route('contact')),
        ];
    }
@endphp
<script>
function heroSlider() {
    return {
        currentSlide: 0,
        autoplayInterval: null,
        slides: {{ Illuminate\Support\Js::from($heroSlides) }},
        init() {
            this.startAutoplay();
        },
        startAutoplay() {
            this.autoplayInterval = setInterval(() => this.next(), 6000);
        },
        stopAutoplay() {
            clearInterval(this.autoplayInterval);
        },
        next() {
            this.currentSlide = (this.currentSlide + 1) % this.slides.length;
        },
        prev() {
            this.currentSlide = this.currentSlide === 0 ? this.slides.length - 1 : this.currentSlide - 1;
        },
        goTo(index) {
            this.stopAutoplay();
            this.currentSlide = index;
            this.startAutoplay();
        }
    }
}
</script>
@endsection
@endsection