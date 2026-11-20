<div>
    <div class="flex items-center space-x-4">
        {{-- Quantity --}}
        <div class="flex items-center border rounded-lg">
            <button wire:click="addToCart()" class="px-3 py-2 text-gray-600 hover:text-amber-600" style="display:none">-</button>
            <input type="number" wire:model.live="quantity" min="1" max="99"
                   class="w-16 text-center border-0 focus:ring-0 text-gray-900 font-semibold"
                   x-data="{ qty: @entangle('quantity') }">
        </div>

        @error('quantity')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('cart')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        {{-- Add to cart button --}}
        <button wire:click="addToCart()" type="button"
                class="flex-1 bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-8 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
            </svg>
            <span>{{ __('Añadir al carrito') }}</span>
        </button>
    </div>

    @if(session('success'))
        <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
            {{ session('success') }}
        </div>
    @endif
</div>
