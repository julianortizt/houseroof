@extends('layouts.app')

@section('meta_description', __('meta_about_desc'))
@section('meta_keywords', __('meta_about_kw'))

@php
    $locale = app()->getLocale();

    // Hero
    $heroTitle = \App\Models\ContentBlock::get('about', 'hero', 'title', $locale) ?? __('About Us');
    $heroSubtitle = \App\Models\ContentBlock::get('about', 'hero', 'subtitle', $locale) ?? __('A configurable platform for managing projects, measurements, customers, and service delivery.');

    // Story
    $storyTitle = \App\Models\ContentBlock::get('about', 'story', 'title', $locale) ?? __('Our Story');
    $storyContent = \App\Models\ContentBlock::get('about', 'story', 'content', $locale);
    $storyImage = \App\Models\ContentBlock::get('about', 'story', 'image', $locale);

    // Values
    $valuesTitle = \App\Models\ContentBlock::get('about', 'values', 'title', $locale) ?? __('Why Choose Us');
    $value1Title = \App\Models\ContentBlock::get('about', 'values', 'value_1_title', $locale) ?? __('Connected project information');
    $value1Text = \App\Models\ContentBlock::get('about', 'values', 'value_1_text', $locale) ?? __('Organize customer and project information in one place.');
    $value1Icon = \App\Models\ContentBlock::get('about', 'values', 'value_1_icon', $locale) ?? 'building-office-2';
    $value2Title = \App\Models\ContentBlock::get('about', 'values', 'value_2_title', $locale) ?? __('Reusable workflows');
    $value2Text = \App\Models\ContentBlock::get('about', 'values', 'value_2_text', $locale) ?? __('Use reusable workflows and templates for consistent project delivery.');
    $value2Icon = \App\Models\ContentBlock::get('about', 'values', 'value_2_icon', $locale) ?? 'truck';
    $value3Title = \App\Models\ContentBlock::get('about', 'values', 'value_3_title', $locale) ?? __('End-to-end delivery');
    $value3Text = \App\Models\ContentBlock::get('about', 'values', 'value_3_text', $locale) ?? __('Manage projects from measurement through estimates and work orders.');
    $value3Icon = \App\Models\ContentBlock::get('about', 'values', 'value_3_icon', $locale) ?? 'wrench';

    // CTA
    $ctaTitle = \App\Models\ContentBlock::get('about', 'cta', 'title', $locale) ?? __('Need a quote for your project?');
    $ctaSubtitle = \App\Models\ContentBlock::get('about', 'cta', 'subtitle', $locale) ?? __('Request a quote or contact us for any enquiry');
    $ctaButton = \App\Models\ContentBlock::get('about', 'cta', 'button_text', $locale) ?? __('Request quote');

    // Detect if content contains HTML tags
    $heroIsRich = str_contains($heroSubtitle ?? '', '<');
    $storyIsRich = str_contains($storyContent ?? '', '<');
@endphp

@section('title', strip_tags($heroTitle) . ' — ' . config('platform.public_name'))
@section('meta_description', strip_tags($heroSubtitle))

@section('content')
{{-- Hero --}}
<section class="platform-page-hero">
    <div class="platform-page-hero__inner px-4 sm:px-6 lg:px-8 text-center">
        <span class="platform-page-kicker">{{ $locale === 'es' ? 'La plataforma' : 'The platform' }}</span>
        <h1 class="platform-page-heading">{!! $heroTitle !!}</h1>
        @if($heroSubtitle)
            <p class="platform-page-subtitle">{{ strip_tags($heroSubtitle) }}</p>
        @endif
    </div>
</section>

{{-- Company Story --}}
<section class="platform-page-body py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-16 items-start">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-8">{!! $storyTitle !!}</h2>
                @if($storyContent)
                    @if($storyIsRich)
                        <div class="prose prose-lg text-gray-600 max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-p:leading-relaxed prose-strong:text-gray-800 prose-a:text-amber-600">{!! $storyContent !!}</div>
                    @else
                        <p class="text-gray-600 leading-relaxed">{!! $storyContent !!}</p>
                    @endif
                @else
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        {{ __('The platform brings customer, project, measurement, estimate, and service workflows together in one configurable workspace.') }}
                    </p>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        {{ __('Use a consistent process to track customer needs, project details, measurements, and estimates.') }}
                    </p>
                    <p class="text-gray-600 leading-relaxed">
                        {{ __('Adapt templates and workflows to fit the services your organization delivers.') }}
                    </p>
                @endif
            </div>
            <div class="relative">
                @if($storyImage)
                    @php
                        $storyImageUrl = str_starts_with($storyImage, 'http') ? $storyImage : upload_url($storyImage);
                    @endphp
                    <img src="{{ $storyImageUrl }}" alt="{{ strip_tags($storyTitle) }}" class="rounded-2xl shadow-xl w-full object-cover" loading="lazy">
                @else
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl shadow-xl p-12 flex items-center justify-center min-h-[400px]">
                        <div class="text-center">
                            <x-heroicon-o-building-office-2 class="w-20 h-20 text-amber-400 mx-auto mb-6" />
                            <p class="text-amber-700 font-semibold text-lg">{{ __('Project workspace') }}</p>
                        </div>
                    </div>
                @endif
                <div class="absolute -bottom-4 -left-4 bg-amber-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg flex items-center space-x-2">
                    <span class="text-lg">✓</span>
                    <span>{{ __('Configurable platform') }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Values --}}
<section class="platform-page-body py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">{!! $valuesTitle !!}</h2>
            <div class="w-20 h-1 bg-amber-500 mx-auto rounded-full"></div>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            {{-- Value 1 --}}
            <div class="platform-card platform-card--interactive p-8 text-center group">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-5 group-hover:bg-amber-200 transition-colors">
                    <x-dynamic-component :component="'heroicon-o-' . $value1Icon" class="w-8 h-8 text-amber-600" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{!! $value1Title !!}</h3>
                <p class="text-gray-600 leading-relaxed">{!! $value1Text !!}</p>
            </div>
            {{-- Value 2 --}}
            <div class="platform-card platform-card--interactive p-8 text-center group">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-5 group-hover:bg-amber-200 transition-colors">
                    <x-dynamic-component :component="'heroicon-o-' . $value2Icon" class="w-8 h-8 text-amber-600" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{!! $value2Title !!}</h3>
                <p class="text-gray-600 leading-relaxed">{!! $value2Text !!}</p>
            </div>
            {{-- Value 3 --}}
            <div class="platform-card platform-card--interactive p-8 text-center group">
                <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-5 group-hover:bg-amber-200 transition-colors">
                    <x-dynamic-component :component="'heroicon-o-' . $value3Icon" class="w-8 h-8 text-amber-600" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">{!! $value3Title !!}</h3>
                <p class="text-gray-600 leading-relaxed">{!! $value3Text !!}</p>
            </div>
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="bg-amber-600 text-white py-16">
    <div class="max-w-4xl mx-auto px-4 text-center">
        <h2 class="text-3xl font-bold mb-4">{!! $ctaTitle !!}</h2>
        <p class="text-xl mb-8 opacity-90">{!! $ctaSubtitle !!}</p>
        <a href="{{ route('quote.create') }}" class="inline-block bg-white text-amber-600 px-8 py-4 rounded-xl font-bold text-lg hover:bg-gray-100 transition shadow-lg hover:shadow-xl">
            {{ $ctaButton }} →
        </a>
    </div>
</section>
@endsection
