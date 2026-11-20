{{-- Los nombres de capa se resuelven en Alpine con las traducciones ya
     cargadas: antes se llamaba a $wire.getLayerLabel(), un método de servidor
     que devuelve una promesa y se pintaba como "[object Promise]". --}}
<div class="relative inline-block"
     x-data="{
         labels: {
             satellite: '{{ __('mapscalculator.satellite') }}',
             pnoa: '{{ __('mapscalculator.pnoa') }}',
             hybrid: '{{ __('mapscalculator.hybrid') }}',
             roadmap: '{{ __('mapscalculator.roadmap') }}',
             terrain: '{{ __('mapscalculator.terrain') }}'
         }
     }">
    <button 
        @click="$wire.showDropdown = !$wire.showDropdown"
        class="flex items-center gap-2 px-3 py-1.5 text-sm bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
        x-ref="dropdownButton"
    >
        <div class="flex items-center gap-1.5">
            <div 
                class="w-5 h-5 rounded border"
                :class="{
                    'bg-blue-500': $wire.selectedLayer === 'satellite',
                    'bg-cyan-600': $wire.selectedLayer === 'pnoa',
                    'bg-green-500': $wire.selectedLayer === 'hybrid',
                    'bg-gray-500': $wire.selectedLayer === 'roadmap',
                    'bg-amber-500': $wire.selectedLayer === 'terrain',
                }"
            ></div>
            <span x-text="labels[$wire.selectedLayer] || $wire.selectedLayer"></span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </div>
    </button>

    <div 
        x-show="$wire.showDropdown" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        @click.outside="$wire.showDropdown = false"
        class="absolute z-50 mt-1 min-w-[160px] bg-white rounded-lg shadow-lg border ring-1 ring-black ring-opacity-5"
    >
        <div class="py-1">
            <button 
                @click="$wire.setLayer('satellite')"
                class="flex items-center gap-3 w-full px-4 py-2 text-sm hover:bg-gray-50"
                :class="{'bg-blue-50 text-blue-700': $wire.selectedLayer === 'satellite'}"
            >
                <div class="w-5 h-5 rounded border bg-blue-500"></div>
                <span class="font-medium">{{ __('mapscalculator.satellite') }}</span>
            </button>

            <button
                @click="$wire.setLayer('pnoa')"
                class="flex items-center gap-3 w-full px-4 py-2 text-sm hover:bg-gray-50"
                :class="{'bg-blue-50 text-blue-700': $wire.selectedLayer === 'pnoa'}"
            >
                <div class="w-5 h-5 rounded border bg-cyan-600"></div>
                <span class="font-medium">{{ __('mapscalculator.pnoa') }}</span>
            </button>
            
            <button 
                @click="$wire.setLayer('hybrid')"
                class="flex items-center gap-3 w-full px-4 py-2 text-sm hover:bg-gray-50"
                :class="{'bg-green-50 text-green-700': $wire.selectedLayer === 'hybrid'}"
            >
                <div class="w-5 h-5 rounded border bg-gradient-to-br from-green-500 to-blue-500"></div>
                <span class="font-medium">{{ __('mapscalculator.hybrid') }}</span>
            </button>
            
            <button 
                @click="$wire.setLayer('roadmap')"
                class="flex items-center gap-3 w-full px-4 py-2 text-sm hover:bg-gray-50"
                :class="{'bg-gray-50 text-gray-700': $wire.selectedLayer === 'roadmap'}"
            >
                <div class="w-5 h-5 rounded border bg-gray-500"></div>
                <span class="font-medium">{{ __('mapscalculator.roadmap') }}</span>
            </button>
            
            <button 
                @click="$wire.setLayer('terrain')"
                class="flex items-center gap-3 w-full px-4 py-2 text-sm hover:bg-gray-50"
                :class="{'bg-amber-50 text-amber-700': $wire.selectedLayer === 'terrain'}"
            >
                <div class="w-5 h-5 rounded border bg-gradient-to-br from-amber-500 to-yellow-500"></div>
                <span class="font-medium">{{ __('mapscalculator.terrain') }}</span>
            </button>
        </div>
    </div>
</div>