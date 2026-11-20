{{-- This is the billing address section that goes inside the checkout form --}}

{{-- Billing Address --}}
<div x-data="{ show: false }" class="bg-white border rounded-xl p-6">
    <label class="flex items-center space-x-2 mb-4">
        <input type="checkbox" wire:model="use_different_billing" @click="show = !show"
               class="h-4 w-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
        <span class="text-sm font-medium text-gray-700">{{ __('Usar dirección de facturación diferente') }}</span>
    </label>

    <div x-show="show" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Dirección de facturación') }}</label>
            <input type="text" wire:model="billing_address_line_1"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>
        <div class="md:col-span-2">
            <input type="text" wire:model="billing_address_line_2"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>
        <div>
            <input type="text" wire:model="billing_city" placeholder="{{ __('Ciudad') }}"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>
        <div>
            <input type="text" wire:model="billing_postal_code" placeholder="{{ __('Código postal') }}"
                   class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
        </div>
    </div>
</div>
