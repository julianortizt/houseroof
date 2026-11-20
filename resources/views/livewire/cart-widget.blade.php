<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" class="relative p-2 text-gray-700 hover:text-amber-600 transition-colors">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
        </svg>
        @if($itemCount > 0)
            <span class="absolute -top-1 -right-1 bg-amber-600 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                {{ $itemCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-transition
         class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border z-50">
        <div class="p-4">
            <h3 class="font-bold text-gray-900 mb-3">{{ __('Carrito de compras') }}</h3>

            @if(count($items) > 0)
                <div class="space-y-3 max-h-60 overflow-y-auto">
                    @foreach($items as $key => $item)
                        <div class="flex items-center space-x-3 text-sm">
                            <div class="w-10 h-10 bg-gray-100 rounded-lg flex-shrink-0">
                                @if(!empty($item['image']))
                                    <img src="{{ $item['image'] }}" alt="" class="w-full h-full object-cover rounded-lg">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 truncate">{{ $item['name'] }}</p>
                                <p class="text-gray-500">x{{ $item['quantity'] }}</p>
                            </div>
                            <span class="font-semibold text-amber-600">{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }} €</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t mt-3 pt-3">
                    <div class="flex justify-between font-bold text-gray-900 mb-3">
                        <span>{{ __('Total') }}</span>
                        <span>{{ $total }}</span>
                    </div>
                    <a href="{{ route('cart.index') }}" class="block w-full text-center bg-gray-900 text-white py-2.5 rounded-lg font-semibold hover:bg-gray-800 transition">
                        {{ __('Ver carrito') }}
                    </a>
                    <a href="{{ route('checkout.index') }}" class="block w-full text-center bg-amber-600 text-white py-2.5 rounded-lg font-semibold hover:bg-amber-700 transition mt-2">
                        {{ __('Ir a pagar') }}
                    </a>
                </div>
            @else
                <p class="text-gray-500 text-sm text-center py-4">{{ __('Tu carrito está vacío') }}</p>
            @endif
        </div>
    </div>
</div>
