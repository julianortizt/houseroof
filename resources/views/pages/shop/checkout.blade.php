@extends('layouts.app')
@section('title', __('Finalizar compra'))

@section('content')
    @if(app(\App\Services\StripeCredentials::class)->productCheckoutReady())
        <livewire:checkout />
    @else
        <main class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center">
                <h1 class="text-2xl font-bold text-slate-950">{{ __('La compra online todavía no está disponible') }}</h1>
                <p class="mt-4 leading-7 text-slate-700">
                    {{ __('Puedes consultar el catálogo y solicitar una cotización. El pago online se habilitará cuando se complete la configuración y revisión fiscal.') }}
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('shop.index') }}" class="rounded-lg border border-slate-300 px-5 py-3 font-semibold text-slate-800 hover:bg-white">
                        {{ __('Volver a productos') }}
                    </a>
                    <a href="{{ route('quote.create') }}" class="rounded-lg bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-700">
                        {{ __('Solicitar presupuesto') }}
                    </a>
                </div>
            </section>
        </main>
    @endif
@endsection
