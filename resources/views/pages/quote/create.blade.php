@extends('layouts.app')
@section('title', __('Solicitar Cotización'))

@section('content')
<section class="platform-page-hero">
    <div class="platform-page-hero__inner max-w-4xl px-4 sm:px-6 lg:px-8 text-center">
        <span class="platform-page-kicker">{{ app()->getLocale() === 'es' ? 'Siguiente paso' : 'Next step' }}</span>
        <h1 class="platform-page-heading">{{ __('Solicitar Cotización') }}</h1>
        <p class="platform-page-subtitle">{{ __('Cuéntenos sobre su proyecto y le enviaremos un presupuesto personalizado') }}</p>
    </div>
</section>

<section class="platform-page-body py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h2 class="mb-5 text-lg font-bold text-gray-900">
                {{ app()->getLocale() === 'es' ? 'Qué ocurre después' : 'What happens next' }}
            </h2>
            <x-platform-process-steps :current-step="1" />
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">{{ session('success') }}</div>
        @endif
        <div class="platform-card platform-form p-6 sm:p-8">
            <livewire:quote-form />
            <p class="mt-6 border-t border-gray-100 pt-4 text-sm leading-6 text-gray-500">
                {{ app()->getLocale() === 'es'
                    ? 'El equipo revisará tu solicitud y confirmará los siguientes pasos. Esta solicitud no genera una factura ni procesa un pago.'
                    : 'The team will review your request and confirm next steps. Submitting this request does not issue an invoice or process a payment.' }}
            </p>
        </div>
    </div>
</section>
@endsection
