<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">{{ __('Finalizar compra') }}</h1>

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">{{ session('error') }}</div>
    @endif

    <form wire:submit="placeOrder">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Form Fields --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Customer Info --}}
                <div class="bg-white border rounded-xl p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('Datos del cliente') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nombre completo') }} *</label>
                            <input type="text" wire:model="customer_name"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('customer_name') border-red-500 @enderror">
                            @error('customer_name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }} *</label>
                            <input type="email" wire:model="customer_email"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('customer_email') border-red-500 @enderror">
                            @error('customer_email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Teléfono') }}</label>
                            <input type="tel" wire:model="customer_phone"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Empresa') }}</label>
                            <input type="text" wire:model="company_name"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('NIF/CIF') }}</label>
                            <input type="text" wire:model="tax_id"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                    </div>
                </div>

                {{-- Shipping Address --}}
                <div class="bg-white border rounded-xl p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('Dirección de envío') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Dirección') }} *</label>
                            <input type="text" wire:model="shipping_address_line_1"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('shipping_address_line_1') border-red-500 @enderror">
                            @error('shipping_address_line_1') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Dirección (línea 2)') }}</label>
                            <input type="text" wire:model="shipping_address_line_2"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Ciudad') }} *</label>
                            <input type="text" wire:model="shipping_city"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('shipping_city') border-red-500 @enderror">
                            @error('shipping_city') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Provincia') }}</label>
                            <input type="text" wire:model="shipping_state"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Código postal') }} *</label>
                            <input type="text" wire:model="shipping_postal_code"
                                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('shipping_postal_code') border-red-500 @enderror">
                            @error('shipping_postal_code') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('País') }}</label>
                            <select wire:model="shipping_country" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
                                @foreach($shippingCountries as $countryCode => $countryName)
                                    <option value="{{ $countryCode }}">{{ __($countryName) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="bg-white border rounded-xl p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('Notas del pedido') }}</h2>
                    <textarea wire:model="notes" rows="3" placeholder="{{ __('Instrucciones especiales de entrega...') }}"
                              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500"></textarea>
                </div>

                {{-- Terms --}}
                <div class="flex items-start space-x-3">
                    <input type="checkbox" wire:model="acceptTerms" id="acceptTerms"
                           class="mt-1 h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                    <label for="acceptTerms" class="text-sm text-gray-600">
                        {{ __('He leído y acepto las') }}
                        <a href="{{ route('legal.page', 'terms-of-sale') }}" target="_blank" class="text-amber-600 hover:underline">{{ __('condiciones de venta') }}</a>
                        {{ __('y la') }}
                        <a href="{{ route('legal.page', 'privacy-policy') }}" target="_blank" class="text-amber-600 hover:underline">{{ __('política de privacidad') }}</a>.
                    </label>
                </div>
                @error('acceptTerms') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Order Summary --}}
            <div class="bg-gray-50 border rounded-xl p-6 h-fit sticky top-24">
                <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('Resumen del pedido') }}</h2>

                <div class="space-y-3 mb-4">
                    @foreach($cartItems as $item)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ $item['name'] }} x{{ $item['quantity'] }}</span>
                            <span class="font-semibold">{{ number_format($item['price'] * $item['quantity'], 2, ',', '.') }} €</span>
                        </div>
                    @endforeach
                </div>

                <div class="border-t pt-4 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Subtotal') }}</span>
                        <span class="font-semibold">{{ number_format($this->subtotal, 2, ',', '.') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Envío') }}</span>
                        <span class="font-semibold">{{ number_format($shippingCost, 2, ',', '.') }} €</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('IVA estimado') }} ({{ number_format($this->taxRate * 100, 2, ',', '.') }}%)</span>
                        <span class="font-semibold">{{ number_format($this->tax, 2, ',', '.') }} €</span>
                    </div>
                    <div class="border-t pt-3 flex justify-between text-lg font-bold">
                        <span>{{ __('Total') }}</span>
                        <span class="text-amber-600">{{ number_format($this->total, 2, ',', '.') }} €</span>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4">
                    <p class="font-semibold text-gray-900">{{ __('Pago seguro con tarjeta vía Stripe') }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('La confirmación del pedido se recibe directamente de Stripe.') }}</p>
                </div>

                <button type="submit"
                        class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-6 rounded-lg transition mt-6 disabled:opacity-50"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50">
                    <span wire:loading.remove wire:target="placeOrder">
                        {{ __('Continuar al pago seguro') }} — {{ number_format($this->total, 2, ',', '.') }} €
                    </span>
                    <span wire:loading wire:target="placeOrder">{{ __('Procesando...') }}</span>
                </button>

                <div class="mt-4 flex items-center justify-center space-x-2 text-xs text-gray-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>{{ __('Pago seguro vía Stripe • SSL/HTTPS • PSD2/SCA') }}</span>
                </div>
            </div>
        </div>
    </form>
</div>
