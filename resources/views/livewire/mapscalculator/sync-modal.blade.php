<div x-data="{ open: @entangle($showSyncModal) }" class="relative">
    <button 
        @click="open = true"
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        :class="{'animate-pulse': $wire.syncing}"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <span>{{ __('mapscalculator.sync') }}</span>
    </button>

    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @click.self="open = false">
        
        <div class="bg-white rounded-2xl shadow-xl border overflow-hidden flex flex-col w-96 max-w-[calc(100vw-3rem)]"
             style="max-height: 500px;">

            <div class="bg-amber-500 text-white px-4 py-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <p class="font-bold text-sm">{{ __('mapscalculator.sync_status') }}</p>
                </div>
                <button @click="open = false" class="text-white/80 hover:text-white">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
                
                <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
                    {{ __('mapscalculator.sync_unavailable') }}
                </div>

                <div class="space-y-4">
                    @foreach($measurements as $m)
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium">{{ $m['label'] }}</h4>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t bg-gray-50 px-6 py-4 flex justify-end gap-3">
                <button @click="open = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.close') }}</button>
            </div>
        </div>
    </div>
</div>