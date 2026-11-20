@if($submitted)
    <div class="bg-green-50 border border-green-200 rounded-xl p-8 text-center">
        <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h3 class="text-xl font-bold text-green-800 mb-2">{{ __('¡Solicitud enviada!') }}</h3>
        <p class="text-green-600">{{ __('Gracias por su interés. Nos pondremos en contacto con usted lo antes posible.') }}</p>
    </div>
@else
    <form wire:submit="submit" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Nombre completo') }} *</label>
                <input type="text" wire:model="name"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('name') border-red-500 @enderror">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Email') }} *</label>
                <input type="email" wire:model="email"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('email') border-red-500 @enderror">
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Teléfono') }}</label>
                <input type="tel" wire:model="phone"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Empresa') }}</label>
                <input type="text" wire:model="company"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Dirección') }}</label>
                <input type="text" wire:model="address"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Ciudad') }}</label>
                <input type="text" wire:model="city"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Código postal') }}</label>
                <input type="text" wire:model="postal_code"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Área estimada (m²)') }}</label>
                <input type="number" wire:model="area_m2" step="0.01" min="0"
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Tipo de servicio') }} *</label>
            <select wire:model="service_type"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('service_type') border-red-500 @enderror">
                <option value="resin">{{ app()->getLocale() === 'es' ? 'Limpieza de techos' : 'Roof cleaning' }}</option>
                <option value="granite">{{ app()->getLocale() === 'es' ? 'Reparación de techos' : 'Roof repair' }}</option>
                <option value="other">{{ __('Otro servicio') }}</option>
            </select>
            @error('service_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Descripción del proyecto') }}</label>
            <textarea wire:model="description" rows="5" placeholder="{{ app()->getLocale() === 'es' ? 'Describa el alcance, la ubicación, las medidas conocidas y cualquier requisito importante.' : 'Describe the scope, location, known measurements, and any important requirements.' }}"
                      class="w-full border-gray-300 rounded-lg shadow-sm focus:border-amber-500 focus:ring-amber-500 @error('description') border-red-500 @enderror"></textarea>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <button type="submit"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-8 rounded-lg transition disabled:opacity-50"
                wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="submit">{{ __('Enviar solicitud de cotización') }}</span>
            <span wire:loading wire:target="submit">{{ __('Enviando...') }}</span>
        </button>
    </form>
@endif
