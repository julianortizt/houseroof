@php
    $currentLocale = app()->getLocale();
    $flags = [
        'en' => ['flag' => '🇬🇧', 'name' => 'English'],
        'es' => ['flag' => '🇪🇸', 'name' => 'Español'],
        'de' => ['flag' => '🇩🇪', 'name' => 'Deutsch'],
        'it' => ['flag' => '🇮🇹', 'name' => 'Italiano'],
        'fr' => ['flag' => '🇫🇷', 'name' => 'Français'],
        'pt' => ['flag' => '🇵🇹', 'name' => 'Português'],
        'nl' => ['flag' => '🇳🇱', 'name' => 'Nederlands'],
    ];
    $current = $flags[$currentLocale] ?? $flags['en'];
@endphp

<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open" class="flex items-center space-x-1 px-2 py-1 rounded-md text-sm text-gray-700 hover:text-amber-600 hover:bg-gray-100 transition">
        <span class="text-lg">{{ $current['flag'] }}</span>
        <span class="hidden sm:inline text-xs font-medium">{{ strtoupper($currentLocale) }}</span>
        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="open" x-cloak x-transition
         class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-2xl border z-50 py-2 max-h-96 overflow-y-auto">
        @foreach($flags as $locale => $info)
            <a href="{{ route('locale.switch', $locale) }}"
               class="flex items-center w-full px-4 py-2.5 text-sm hover:bg-gray-50 transition {{ $currentLocale === $locale ? 'bg-amber-50 text-amber-700 font-semibold' : 'text-gray-700' }}">
                <span class="text-xl mr-3">{{ $info['flag'] }}</span>
                <span>{{ $info['name'] }}</span>
                @if($currentLocale === $locale)
                    <svg class="w-4 h-4 ml-auto text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                @endif
            </a>
        @endforeach
    </div>
</div>
