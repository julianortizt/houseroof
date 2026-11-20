@extends('layouts.app')

@section('title', __('Estado del pedido'))

@section('content')
    <main class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
            <h1 class="text-2xl font-bold text-slate-950">
                {{ $paymentReceived ? __('Pago recibido') : __('Pago en proceso de confirmación') }}
            </h1>
            <p class="mt-4 leading-7 text-slate-600">
                {{ $paymentReceived
                    ? __('Hemos recibido la confirmación de pago. Te enviaremos los detalles del pedido por correo.')
                    : __('El proveedor de pagos todavía no confirma el pago. No vuelvas a pagar; contacta con nosotros si el estado no se actualiza.') }}
            </p>
            <a href="{{ route('home') }}" class="mt-6 inline-flex rounded-lg bg-amber-600 px-5 py-3 font-semibold text-white hover:bg-amber-700">
                {{ __('Volver al inicio') }}
            </a>
        </section>
    </main>
@endsection
