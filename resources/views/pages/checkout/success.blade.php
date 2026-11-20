@extends('layouts.app')
@section('title', __('Pedido completado'))

@section('content')
<section class="py-20">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="bg-white border rounded-2xl p-12 shadow-sm">
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ __('¡Pedido completado!') }}</h1>
            <p class="text-lg text-gray-600 mb-2">{{ __('Gracias por su compra. Ha recibido un email de confirmación con los detalles de su pedido.') }}</p>
            <p class="text-sm text-gray-500 mb-8">{{ __('Si no recibe el email en unos minutos, revise su carpeta de spam o contacte con nosotros.') }}</p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('shop.index') }}" class="inline-flex items-center justify-center bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-8 rounded-lg transition">
                    {{ __('Seguir comprando') }}
                </a>
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center bg-gray-100 hover:bg-gray-200 text-gray-900 font-bold py-3 px-8 rounded-lg transition">
                    {{ __('Volver al inicio') }}
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
