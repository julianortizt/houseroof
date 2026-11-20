<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ url('/contacto') }}" target="_blank" class="filament-button filament-page-button inline-flex items-center justify-center gap-1 rounded-lg bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow ring-1 ring-secondary-300 hover:bg-secondary-50 focus:ring-primary-600 dark:bg-secondary-700 dark:text-white dark:ring-secondary-600 dark:hover:bg-secondary-600 transition">
                <x-heroicon-o-eye class="w-5 h-5" />
                Ver en el sitio
            </a>
            <x-filament::button type="submit" size="lg">
                <x-heroicon-o-check class="w-5 h-5 mr-2" />
                Guardar Contacto
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
