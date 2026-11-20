@php
    $adminLocale = session('admin_locale', 'en');
    $languages = [
        'en' => ['name' => 'English', 'flag' => '🇬🇧'],
        'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
    ];
    $current = $languages[$adminLocale] ?? $languages['en'];
@endphp

<div class="flex items-center gap-1 ml-4 relative" x-data="{ open: false }">
    <button
        @click="open = !open"
        @click.away="open = false"
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5 transition-colors"
    >
        <span class="text-lg">{{ $current['flag'] }}</span>
        <span class="hidden sm:inline">{{ strtoupper($adminLocale) }}</span>
        <x-heroicon-m-chevron-down class="w-4 h-4" />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50 py-2"
    >
        <div class="px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">
            {{ __('Admin Language') }}
        </div>

        @foreach($languages as $code => $lang)
            <a
                href="{{ route('admin.locale.switch', $code) }}"
                class="flex items-center gap-3 px-3 py-2 mx-1 rounded-lg text-sm transition-colors {{ $adminLocale === $code ? 'bg-amber-50 text-amber-700 font-semibold dark:bg-amber-900/20 dark:text-amber-400' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5' }}"
            >
                <span class="text-xl">{{ $lang['flag'] }}</span>
                <span>{{ $lang['name'] }}</span>
                @if($adminLocale === $code)
                    <x-heroicon-m-check class="w-4 h-4 ml-auto text-amber-500" />
                @endif
            </a>
        @endforeach
    </div>
</div>
