<x-filament-panels::page>
    <div class="max-w-5xl space-y-6">
        <div class="rounded-xl border border-primary-200 bg-primary-50 p-5 dark:border-primary-800 dark:bg-primary-950">
            <h2 class="text-lg font-semibold text-primary-950 dark:text-primary-100">Personaliza el sitio público</h2>
            <p class="mt-1 text-sm text-primary-800 dark:text-primary-200">
                Elige una plantilla para reorganizar la portada y un estilo visual. Los cambios se aplican al guardar.
            </p>
        </div>

        <form wire:submit="save" class="space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Estilo visual</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Selecciona la paleta y el aspecto general del sitio.
                </p>

                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach(config('platform.styles') as $key => $style)
                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition hover:border-primary-500 dark:border-gray-700">
                            <input type="radio" wire:model="siteStyle" value="{{ $key }}" class="mt-1 text-primary-600 focus:ring-primary-500">
                            <span>
                                <span class="mb-3 flex gap-2">
                                    <span class="h-8 w-8 rounded-full border border-black/10" style="background: {{ $style['primary'] }}"></span>
                                    <span class="h-8 w-8 rounded-full border border-black/10" style="background: {{ $style['surface'] }}"></span>
                                    <span class="h-8 w-8 rounded-full border border-black/10" style="background: {{ $style['canvas'] }}"></span>
                                </span>
                                <span class="font-medium text-gray-950 dark:text-white">{{ $style['label'] }}</span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('siteStyle')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Cambiar plantilla de la portada</h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Selecciona una opción. La plantilla cambia el orden de las secciones, no elimina su contenido.
                </p>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach(config('platform.home_templates') as $key => $label)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:border-primary-500 dark:border-gray-700">
                            <input type="radio" wire:model="homeTemplate" value="{{ $key }}" class="mt-1 text-primary-600 focus:ring-primary-500">
                            <span>
                                <span class="block font-medium text-gray-950 dark:text-white">{{ $label }}</span>
                                <span class="mt-1 block text-sm text-gray-600 dark:text-gray-400">
                                    @switch($key)
                                        @case('roof') Destaca la limpieza, el mantenimiento y la reparación de techos de House Roof en toda Europa, con equipos locales y materiales certificados. @break
                                        @case('standard') Mantiene el orden actual de las secciones. @break
                                        @case('portfolio') Destaca primero los proyectos de la organización. @break
                                        @case('services') Da prioridad a los servicios y flujos de trabajo. @break
                                        @case('corporate') Conserva la antigua portada de la plataforma como alternativa. @break
                                    @endswitch
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('homeTemplate')
                    <p class="mt-2 text-sm text-danger-600">{{ $message }}</p>
                @enderror
            </section>

            <div class="flex flex-wrap gap-3">
                <x-filament::button type="submit" icon="heroicon-o-check">Guardar plantilla y estilo</x-filament::button>
                <x-filament::button tag="a" :href="route('home')" color="gray" icon="heroicon-o-arrow-top-right-on-square">
                    Ver sitio público
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
