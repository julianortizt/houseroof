@extends('layouts.app')
@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    {{-- Breadcrumb --}}
    <nav class="mb-8 text-sm text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-amber-600">{{ __('Inicio') }}</a>
        <span class="mx-2">/</span>
        <a href="{{ route('shop.index') }}" class="hover:text-amber-600">{{ __('Tienda') }}</a>
        <span class="mx-2">/</span>
        @if($product->category)
            <a href="{{ route('shop.index', ['category' => $product->category_id]) }}" class="hover:text-amber-600">{{ $product->category->name }}</a>
            <span class="mx-2">/</span>
        @endif
        <span class="text-gray-900 font-medium">{{ $product->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        {{-- Images --}}
        <div>
            @if($product->getMainImageUrl())
                <div class="aspect-[4/3] bg-gray-100 rounded-2xl overflow-hidden mb-4">
                    <img src="{{ $product->getMainImageUrl() }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-4">
                </div>
            @endif

            @if($product->media->count() > 1)
                <div class="grid grid-cols-4 gap-2">
                    @foreach($product->media as $media)
                        <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden cursor-pointer border-2 hover:border-amber-500">
                            <img src="{{ $media->getUrl('thumb') }}" alt="" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Product Info --}}
        <div>
            <div class="flex items-center space-x-3 mb-2">
                <h1 class="text-3xl font-bold text-gray-900">{{ $product->name }}</h1>
                @if($product->is_coming_soon)
                    <span class="inline-flex items-center bg-gray-900 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg animate-pulse">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('Próximamente') }}
                    </span>
                @endif
            </div>

            <div class="flex items-center space-x-4 mb-4">
                @if($product->category)
                    <span class="text-sm text-amber-600 font-semibold bg-amber-50 px-3 py-1 rounded-full">{{ $product->category->name }}</span>
                @endif
                @if($product->sku)
                    <span class="text-sm text-gray-500">SKU: {{ $product->sku }}</span>
                @endif
            </div>



            @if($product->short_description)
                <p class="text-gray-600 mb-6 leading-relaxed">{{ $product->short_description }}</p>
            @endif



            {{-- Dimensions --}}
            @if($product->weight_kg || $product->width_cm)
                <div class="grid grid-cols-4 gap-4 mb-6 text-sm text-gray-600">
                    @if($product->weight_kg)
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <span class="block font-bold text-gray-900">{{ $product->weight_kg }} kg</span>
                            <span>{{ __('Peso') }}</span>
                        </div>
                    @endif
                    @if($product->width_cm)
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <span class="block font-bold text-gray-900">{{ $product->width_cm }} cm</span>
                            <span>{{ __('Ancho') }}</span>
                        </div>
                    @endif
                    @if($product->height_cm)
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <span class="block font-bold text-gray-900">{{ $product->height_cm }} cm</span>
                            <span>{{ __('Alto') }}</span>
                        </div>
                    @endif
                    @if($product->depth_cm)
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <span class="block font-bold text-gray-900">{{ $product->depth_cm }} cm</span>
                            <span>{{ __('Profundidad') }}</span>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Add to Cart / Coming Soon --}}
            @if($product->is_coming_soon)
                <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-6">
                    <div class="flex items-center space-x-3 mb-3">
                        <svg class="w-6 h-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-lg font-bold text-gray-900">{{ __('Próximamente disponible') }}</h3>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">{{ __('Este producto estará disponible en nuestra tienda muy pronto. Puede dejarnos su email para ser el primero en enterarse.') }}</p>
                    <div class="flex space-x-3">
                        <a href="{{ route('contact') }}" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-6 rounded-lg transition text-center">
                            {{ __('Notificarme cuando esté disponible') }}
                        </a>
                        @if($siteWhatsapp = \App\Models\Setting::get('site_whatsapp'))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $siteWhatsapp) }}?text={{ urlencode('I am interested in ' . $product->name) }}" target="_blank"
                           class="bg-green-500 hover:bg-green-600 text-white font-bold py-3 px-6 rounded-lg transition text-center">
                            {{ __('Preguntar por WhatsApp') }}
                        </a>
                        @endif
                    </div>
                </div>
            @elseif($product->stock > 0 && app(\App\Services\StripeCredentials::class)->productCheckoutReady())
                <livewire:add-to-cart :productId="$product->id" :key="$product->id" />
            @elseif($product->stock > 0)
                <a href="{{ route('quote.create') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-6 py-3 font-semibold text-white transition hover:bg-amber-700">
                    {{ __('Solicitar presupuesto') }}
                </a>
            @else
                <p class="text-gray-500 italic">{{ __('Producto actualmente no disponible. Contacte para más información.') }}</p>
            @endif

            {{-- Free shipping notice --}}
            <div class="mt-6 p-4 bg-gray-50 rounded-lg flex items-center space-x-3">
                <svg class="w-8 h-8 text-amber-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <div class="text-sm">
                    <p class="font-semibold text-gray-900">{{ __('Envío gratuito') }} {{ __('en pedidos superiores a 500€') }}</p>
                    <p class="text-gray-500">{{ __('Entrega estimada en 3-5 días laborables a toda la Península') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Description --}}
    @if($product->description)
        <div class="mt-16 border-t pt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Descripción del producto') }}</h2>
            <div class="prose max-w-none text-gray-700">
                {!! $product->description !!}
            </div>
        </div>
    @endif

    {{-- Related Products --}}
    @if($relatedProducts->count())
        <div class="mt-16 border-t pt-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Productos relacionados') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($relatedProducts as $rp)
                    <a href="{{ route('shop.show', $rp) }}" class="group bg-white border rounded-2xl overflow-hidden hover:shadow-lg transition">
                        <div class="aspect-[4/3] bg-gray-100 overflow-hidden">
                            @if($rp->getMainImageUrl())
                                <img src="{{ $rp->getMainImageUrl() }}" alt="{{ $rp->name }}"
                                     class="w-full h-full object-contain p-2 group-hover:scale-105 transition duration-300">
                            @endif
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-gray-900 group-hover:text-amber-600 transition">{{ $rp->name }}</h3>
                            @if($rp->short_description)
                                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $rp->short_description }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
