<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Carrito de compras') }}</h1>

    @error('quantity')
        <p class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>
    @enderror
    @error('cart')
        <p class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $message }}</p>
    @enderror

    @if(count($items) > 0)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Cart Items --}}
            <div class="lg:col-span-2 space-y-4">
                @foreach($items as $key => $item)
                    <div class="flex items-center bg-white border rounded-xl p-4 shadow-sm">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg flex-shrink-0 overflow-hidden">
                            @if(!empty($item['image']))
                                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-400">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            @endif
                        </div>

                        <div class="ml-4 flex-1">
                            <h3 class="font-semibold text-gray-900">{{ $item['name'] }}</h3>
                            @if(!empty($item['variant_name']))
                                <p class="text-sm text-gray-500">{{ $item['variant_name'] }}</p>
                            @endif
                            <p class="text-amber-600 font-bold mt-1">{{ number_format($item['price'], 2, ',', '.') }} €</p>
                        </div>

                        <div class="flex items-center space-x-3">
                            <div class="flex items-center border rounded-lg">
                                <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-amber-600">−</button>
                                <span class="w-10 text-center font-semibold">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                        class="w-8 h-8 flex items-center justify-center text-gray-600 hover:text-amber-600">+</button>
                            </div>
                            <button wire:click="removeItem('{{ $key }}')" class="text-red-500 hover:text-red-700 p-1">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach

                <div class="flex justify-between items-center">
                    <a href="{{ route('shop.index') }}" class="text-amber-600 hover:text-amber-700 font-semibold">
                        ← {{ __('Seguir comprando') }}
                    </a>
                </div>
            </div>

            {{-- Order Summary --}}
            <div class="bg-gray-50 border rounded-xl p-6 h-fit sticky top-24">
                <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Resumen del pedido') }}</h2>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Subtotal') }}</span>
                        <span class="font-semibold">{{ number_format($this->subtotal, 2, ',', '.') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Envío estimado') }}</span>
                        <span class="font-semibold">{{ number_format($this->shippingCost, 2, ',', '.') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('IVA estimado') }} ({{ number_format($this->taxRate * 100, 2, ',', '.') }}%)</span>
                        <span class="font-semibold">{{ number_format($tax, 2, ',', '.') }} €</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between text-lg font-bold">
                        <span>{{ __('Total') }}</span>
                        <span class="text-amber-600">{{ number_format($this->total, 2, ',', '.') }} €</span>
                    </div>
                </div>

                @if(app(\App\Services\StripeCredentials::class)->productCheckoutReady())
                    <a href="{{ route('checkout.index') }}"
                       class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-6 rounded-lg transition mt-6">
                        {{ __('Proceder al pago') }}
                    </a>
                @else
                    <a href="{{ route('quote.create') }}"
                       class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-6 rounded-lg transition mt-6">
                        {{ __('Solicitar presupuesto') }}
                    </a>
                @endif
            </div>
        </div>
    @else
        <div class="text-center py-16">
            <svg class="w-20 h-20 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            <h2 class="text-2xl font-bold text-gray-600 mb-2">{{ __('Tu carrito está vacío') }}</h2>
            <p class="text-gray-500 mb-6">{{ __('Explora nuestro catálogo y encuentra los productos perfectos para tu proyecto.') }}</p>
            <a href="{{ route('shop.index') }}"
               class="inline-block bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-8 rounded-lg transition">
                {{ __('Ir a la tienda') }}
            </a>
        </div>
    @endif
</div>
