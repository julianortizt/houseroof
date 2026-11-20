@auth
    @if(auth()->user()->isMapscalculatorAdmin())
        <a 
            href="{{ route('mapscalculator.index') }}" 
            target="_blank"
            class="fi-btn fi-btn-sm fi-btn-primary hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-colors hover:bg-amber-700"
            title="{{ __('MapsCalculator - Herramienta de medición') }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span class="font-medium">MapsCalculator</span>
        </a>
    @endif
@endauth