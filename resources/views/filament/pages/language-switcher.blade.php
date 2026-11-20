<x-filament::page>
    <div class="space-y-6">
        <p class="text-sm text-gray-600">
            Selecciona el idioma del panel de administración y del sitio web.
        </p>

        @php
            $languages = [
                'en' => ['flag' => '🇬🇧', 'name' => 'English', 'region' => 'United Kingdom', 'default' => true],
                'es' => ['flag' => '🇪🇸', 'name' => 'Español', 'region' => 'España'],
                'de' => ['flag' => '🇩🇪', 'name' => 'Deutsch', 'region' => 'Deutschland'],
                'it' => ['flag' => '🇮🇹', 'name' => 'Italiano', 'region' => 'Italia'],
                'fr' => ['flag' => '🇫🇷', 'name' => 'Français', 'region' => 'France'],
                'pt' => ['flag' => '🇵🇹', 'name' => 'Português', 'region' => 'Portugal'],
                'nl' => ['flag' => '🇳🇱', 'name' => 'Nederlands', 'region' => 'Nederland'],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($languages as $locale => $lang)
                <button
                    wire:click="switchLocale('{{ $locale }}')"
                    class="flex items-center gap-4 p-6 rounded-xl border-2 transition-all {{ $currentLocale === $locale ? 'border-amber-500 bg-amber-50 shadow-md' : 'border-gray-200 hover:border-gray-300 bg-white' }}"
                >
                    <span class="text-4xl">{{ $lang['flag'] }}</span>
                    <div class="text-left">
                        <p class="font-bold text-lg {{ $currentLocale === $locale ? 'text-amber-700' : 'text-gray-900' }}">{{ $lang['name'] }}</p>
                        <p class="text-sm text-gray-500">{{ $lang['region'] }}</p>
                        @if($currentLocale === $locale)
                            <span class="inline-flex items-center mt-2 text-xs font-semibold text-amber-700 bg-amber-100 px-2 py-1 rounded-full">
                                ✓ {{ $lang['default'] ?? false ? 'Default' : 'Activo' }}
                            </span>
                        @endif
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</x-filament::page>
