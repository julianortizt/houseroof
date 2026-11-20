@extends('layouts.app')

@section('meta_description', __('meta_contact_desc'))
@section('meta_keywords', __('meta_contact_kw'))

@php
    $locale = app()->getLocale();
    $CB = \App\Models\ContentBlock::class;

    // Hero
    $heroTitle = $CB::get('contact', 'hero', 'title', $locale) ?? __('Contacto');
    $heroSubtitle = $CB::get('contact', 'hero', 'subtitle', $locale) ?? __('Solicite una cotización o contáctenos para cualquier consulta');

    // Info
    $address = $CB::get('contact', 'info', 'address', $locale);
    $phone = $CB::get('contact', 'info', 'phone', $locale) ?? \App\Models\Setting::get('site_phone');
    $email = $CB::get('contact', 'info', 'email', $locale) ?? \App\Models\Setting::get('site_email');
    $schedule = $CB::get('contact', 'info', 'schedule', $locale);
    $whatsapp = \App\Models\Setting::get('site_whatsapp');
    $formTitle = $CB::get('contact', 'info', 'form_title', $locale) ?? __('Solicitar cotización de instalación');
@endphp

@section('title', $heroTitle . ' — ' . config('platform.public_name'))

@section('content')
<section class="platform-page-hero">
    <div class="platform-page-hero__inner px-4 sm:px-6 lg:px-8 text-center">
        <span class="platform-page-kicker">{{ app()->getLocale() === 'es' ? 'Hablemos' : 'Let’s talk' }}</span>
        <h1 class="platform-page-heading">{!! $heroTitle !!}</h1>
        <p class="platform-page-subtitle">{{ $heroSubtitle }}</p>
    </div>
</section>

<section class="platform-page-body py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- Quote Form --}}
            <div class="lg:col-span-2">
                <div class="platform-card platform-form p-6 sm:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">{!! $formTitle !!}</h2>
                    <livewire:quote-form />
                </div>
            </div>

            {{-- Contact Info --}}
            @if($address || $phone || $email || $schedule || $whatsapp)
            <div class="space-y-8">
                @if($address || $phone || $email)
                <div class="platform-card p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">{{ __('Información de contacto') }}</h3>
                    <div class="space-y-4 text-sm">
                        @if($address)
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 0 1-2.827 0l-4.244-4.243a8 8 0 1 1 11.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/></svg>
                            <div>
                                <p class="font-semibold text-gray-900">{{ __('Dirección') }}</p>
                                <p class="text-gray-600">{!! $address !!}</p>
                            </div>
                        </div>
                        @endif
                        @if($phone)
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <div>
                                <p class="font-semibold text-gray-900">{{ __('Teléfono') }}</p>
                                <p class="text-gray-600">{{ $phone }}</p>
                            </div>
                        </div>
                        @endif
                        @if($email)
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <div>
                                <p class="font-semibold text-gray-900">{{ __('Email') }}</p>
                                <p class="text-gray-600">{{ $email }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                @if($schedule)
                <div class="platform-card bg-amber-50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('Horario') }}</h3>
                    <p class="text-sm text-gray-600">{!! $schedule !!}</p>
                </div>
                @endif

                @if($whatsapp)
                <div class="platform-card bg-green-50 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('WhatsApp') }}</h3>
                    <p class="text-sm text-gray-600 mb-3">{{ __('Respuesta inmediata por WhatsApp') }}</p>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank"
                       class="inline-flex items-center bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition text-sm">
                        {{ __('Abrir WhatsApp') }}
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>
</section>
@endsection
