<div x-data="{ showNewMeasurementModal: false }" class="w-full">
    {{-- Este panel ya no incrusta los editores: cada medición abre su propia
         página de trabajo, que carga lo que necesita. Antes se traían aquí
         Leaflet, Leaflet.draw, Turf y Fabric.js (≈1 MB) sin usarlos. --}}

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $project ? __('mapscalculator.project') . ': ' . $project->name : __('mapscalculator.new_project') }}
            </h1>
            <p class="text-gray-500 mt-1">
                @if($project)
                    {{ __('mapscalculator.client') }}: {{ $project->client_name }} •
                    {{ __('mapscalculator.project_type') }}: {{ $project->type_label }} •
                    {{ __('mapscalculator.status') }}: {{ $project->status_label }}
                    @if($project->total_area > 0)
                        • {{ __('mapscalculator.total_area') }}: {{ number_format($project->total_area, 2, ',', '.') }} m²
                    @endif
                @else
                    {{ __('mapscalculator.select_client_create') }}
                @endif
            </p>
        </div>

        @if($project)
            <div class="flex flex-wrap items-center gap-2">
                <!-- Project Type Switcher -->
                <div class="flex gap-1 bg-gray-100 rounded-lg p-1" role="group" aria-label="{{ __('mapscalculator.project_type') }}">
                    <button wire:click="updateProjectType('interior')" class="px-3 py-1.5 text-sm rounded-md {{ $project->type === 'interior' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-700 hover:bg-white' }} transition">{{ __('mapscalculator.interior') }}</button>
                    <button wire:click="updateProjectType('exterior')" class="px-3 py-1.5 text-sm rounded-md {{ $project->type === 'exterior' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-700 hover:bg-white' }} transition">{{ __('mapscalculator.exterior') }}</button>
                    <button wire:click="updateProjectType('mixto')" class="px-3 py-1.5 text-sm rounded-md {{ $project->type === 'mixto' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-700 hover:bg-white' }} transition">{{ __('mapscalculator.mixed') }}</button>
                </div>

                <div class="w-px h-6 bg-gray-300"></div>

                <!-- Map Layer Selector -->
                <livewire:maps-calculator.map-layer-selector :defaultLayer="$project->default_map_layer ?? 'pnoa'" />

                <div class="w-px h-6 bg-gray-300"></div>

                <!-- Accesos directos a los editores de medición: antes había que
                     crear una medición y esperar la redirección para llegar al mapa. -->
                <div class="flex items-center gap-2">
                    <button wire:click="startMeasurement('exterior')"
                            class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-1.5 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                        {{ __('mapscalculator.measure_on_map') }}
                    </button>
                    <button wire:click="startMeasurement('interior')"
                            class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-1.5 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        {{ __('mapscalculator.measure_on_plan') }}
                    </button>
                </div>

                <div class="w-px h-6 bg-gray-300"></div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <button wire:click="$set('activeTab', 'settings')" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition" title="{{ __('mapscalculator.settings') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573-1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.065c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-2.573-1.065c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 002.573-1.065z"></path></svg>
                    </button>
                    
                    <button wire:click="syncProject" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-1" title="{{ __('mapscalculator.sync') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    </button>
                    
                    <button wire:click="shareProject" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition" title="{{ __('mapscalculator.share_project') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684z"></path></svg>
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if(!$project)
        <div class="bg-white rounded-xl shadow-sm border p-8 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('mapscalculator.new_project') }}</h3>
            <p class="text-gray-500 mb-6 max-w-md mx-auto">{{ __('mapscalculator.select_client_create') }}</p>

            <div class="max-w-md mx-auto space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.client') }}</label>
                    <select wire:model="client.id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">{{ __('mapscalculator.select_client') }}</option>
                        @foreach($clients as $c)
                            <option value="{{ $c['id'] }}">{{ $c['name'] }} {{ $c['email'] ? '(' . $c['email'] . ')' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <button wire:click="createProject" class="w-full px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium" wire:loading.attr="disabled">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('mapscalculator.create_project') }}
                </button>
                <button wire:click="$set('showClientModal', true)" class="w-full px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.new_client') }}</button>
            </div>
        </div>
    @else
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex gap-1 overflow-x-auto" role="tablist">
                <button role="tab" wire:click="$set('activeTab', 'measurements')" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $activeTab === 'measurements' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">{{ __('mapscalculator.measurements_tab') }}</button>
                @if($this->quotesEnabled())
                    <button role="tab" wire:click="$set('activeTab', 'quote')" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $activeTab === 'quote' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">{{ __('mapscalculator.quote_tab') }}</button>
                @endif
                @if($this->workOrdersEnabled())
                    <button role="tab" wire:click="$set('activeTab', 'work')" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $activeTab === 'work' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">{{ __('mapscalculator.work_tab') }}</button>
                @endif
                <button role="tab" wire:click="$set('activeTab', 'settings')" class="px-4 py-2 text-sm font-medium rounded-t-lg {{ $activeTab === 'settings' ? 'bg-primary-600 text-white' : 'text-gray-500 hover:text-gray-700' }} whitespace-nowrap">{{ __('mapscalculator.settings_tab') }}</button>
            </nav>
        </div>

        <div x-show="$wire.activeTab === 'measurements'" x-cloak>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div class="flex gap-2">
                    <button @click="showNewMeasurementModal = true" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        {{ __('mapscalculator.new_measurement') }}
                    </button>
                    <div class="hidden sm:flex items-center gap-2 text-sm text-gray-500">
                        <span>{{ __('mapscalculator.total_area') }}:</span>
                        <span class="font-bold text-primary-600">{{ number_format($project->total_area ?? 0, 2, ',', '.') }} m²</span>
                        <span class="text-gray-300">|</span>
                        <span>{{ __('mapscalculator.perimeter') }}:</span>
                        <span class="font-bold text-secondary-600">{{ number_format($project->total_perimeter ?? 0, 2, ',', '.') }} m</span>
                    </div>
                </div>
                <div class="flex gap-1">
                    <button wire:click="$set('measurementFilter', 'all')" class="px-3 py-1.5 text-sm rounded-l-lg transition {{ $measurementFilter === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">{{ __('mapscalculator.all') }}</button>
                    <button wire:click="$set('measurementFilter', 'exterior')" class="px-3 py-1.5 text-sm transition {{ $measurementFilter === 'exterior' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">{{ __('mapscalculator.exterior') }}</button>
                    <button wire:click="$set('measurementFilter', 'interior')" class="px-3 py-1.5 text-sm rounded-r-lg transition {{ $measurementFilter === 'interior' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">{{ __('mapscalculator.interior') }}</button>
                </div>
            </div>

            @php
                // La lista se renderiza en Blade: antes usaba x-for sobre
                // `filteredMeasurements`, una variable Alpine que no existía en
                // el x-data raíz, así que la rejilla de mediciones nunca salía.
                $visibleMeasurements = $measurementFilter === 'all'
                    ? $measurements
                    : array_values(array_filter($measurements, fn ($item) => ($item['type'] ?? null) === $measurementFilter));
            @endphp

            @if(empty($visibleMeasurements))
            <div class="bg-white rounded-xl shadow-sm border p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('mapscalculator.no_measurements') }}</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">{{ __('mapscalculator.create_first_measurement') }}</p>
                <button @click="showNewMeasurementModal = true" class="px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    {{ __('mapscalculator.new_measurement') }}
                </button>
            </div>

            @else
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($visibleMeasurements as $item)
                        <div class="bg-white rounded-lg border shadow-sm p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <h4 class="font-medium text-gray-900">{{ $item['label'] }}</h4>
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ ($item['type'] ?? '') === 'exterior' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">{{ ($item['type'] ?? '') === 'exterior' ? __('mapscalculator.exterior') : __('mapscalculator.interior') }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-sm mb-3">
                                <div><span class="text-gray-500">{{ __('mapscalculator.area') }}:</span> <span class="font-medium">{{ number_format((float) ($item['area_m2'] ?? 0), 2, ',', '.') }} m²</span></div>
                                <div><span class="text-gray-500">{{ __('mapscalculator.perimeter') }}:</span> <span class="font-medium">{{ number_format((float) ($item['perimeter_m'] ?? 0), 2, ',', '.') }} m</span></div>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="loadMeasurement({{ $item['id'] }})" class="flex-1 px-3 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                                    {{ __('mapscalculator.edit') }}
                                </button>
                                <button wire:click="duplicateMeasurement({{ $item['id'] }})" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition" title="{{ __('mapscalculator.duplicate') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                                </button>
                                <button wire:click="deleteMeasurement({{ $item['id'] }})" wire:confirm="{{ __('mapscalculator.delete_confirmation') }}" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition" title="{{ __('mapscalculator.delete') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v12m-6 0h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12"></path></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
            </div>

            @endif

            <!-- New Measurement Modal -->
            <div x-show="showNewMeasurementModal" x-cloak class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showNewMeasurementModal = false">
                <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('mapscalculator.new_measurement') }}</h3>
                    <p class="text-gray-500 mb-6">{{ __('mapscalculator.choose_measurement_type') }}</p>
                    <div class="grid gap-3">
                        <button wire:click="startMeasurement('exterior')" class="p-4 border-2 border-blue-200 rounded-xl hover:border-blue-400 hover:bg-blue-50 transition flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ __('mapscalculator.exterior_measurement') }}</p>
                                <p class="text-sm text-gray-500">{{ __('mapscalculator.exterior_measurement_desc') }}</p>
                            </div>
                        </button>
                        <button wire:click="startMeasurement('interior')" class="p-4 border-2 border-green-200 rounded-xl hover:border-green-400 hover:bg-green-50 transition flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ __('mapscalculator.interior_measurement') }}</p>
                                <p class="text-sm text-gray-500">{{ __('mapscalculator.interior_measurement_desc') }}</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        @if($this->quotesEnabled())
        <div x-show="$wire.activeTab === 'quote'" x-cloak class="space-y-6">
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!$project->latestQuote)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                    <h3 class="font-medium text-amber-800 mb-2">{{ __('mapscalculator.generate_auto_quote') }}</h3>
                    <p class="text-amber-700 text-sm mb-4">{{ __('mapscalculator.auto_quote_desc', ['area' => number_format($project->total_area, 2, ',', '.')]) }}</p>
                    <button wire:click="generateAutoQuote" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">{{ __('mapscalculator.generate_auto') }}</button>
                </div>
            @endif

            @if($quoteItems)
                <div class="bg-white rounded-lg border shadow-sm overflow-hidden">
                    <div class="p-4 border-b flex items-center justify-between">
                        <h3 class="font-medium text-gray-900">{{ __('mapscalculator.quote_items') }}</h3>
                        <span class="text-sm text-gray-500">{{ count($quoteItems) }} {{ __('mapscalculator.concepts') }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.concept') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.quantity') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.unit_price') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.discount') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('mapscalculator.total') }}</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($quoteItems as $index => $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            {{-- wire:model es diferido en Livewire 3: sin .live
                                                 los totales no se recalculaban al editar. --}}
                                            <input type="text" wire:model.live.debounce.400ms="quoteItems.{{ $index }}.name" class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-primary-500">
                                            @if(!empty($item['description']))
                                                <p class="text-xs text-gray-500 mt-1">{{ $item['description'] }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0" wire:model.live.debounce.400ms="quoteItems.{{ $index }}.quantity" class="w-20 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0" wire:model.live.debounce.400ms="quoteItems.{{ $index }}.unit_price" class="w-24 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <input type="number" step="0.01" min="0" max="100" wire:model.live.debounce.400ms="quoteItems.{{ $index }}.discount_percent" class="w-16 px-2 py-1 border border-gray-300 rounded text-sm text-right focus:outline-none focus:ring-1 focus:ring-primary-500">
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium">{{ number_format($item['total_price'], 2, ',', '.') }} €</td>
                                        <td class="px-4 py-3 text-right">
                                            <button wire:click="removeQuoteItem({{ $index }})" class="text-red-500 hover:text-red-700" title="{{ __('mapscalculator.delete') }}"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v12m-6 0h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12"></path></svg></button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 border-t bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.vat_percent') }}</label>
                                <input type="number" step="0.01" min="0" max="100" wire:model.live.debounce.400ms="quoteTaxPercent" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="{{ $quoteTaxPercent }}">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.conditions') }}</label>
                                <textarea wire:model="quoteNotes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm">{{ $quoteNotes }}</textarea>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-4 pt-4 border-t">
                            <div class="text-right">
                                <div class="text-sm text-gray-500">{{ __('mapscalculator.subtotal') }}</div>
                                <div class="text-xl font-bold text-gray-900">{{ number_format($quoteSubtotal, 2, ',', '.') }} €</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">{{ __('mapscalculator.vat', ['percent' => number_format($quoteTaxPercent, 2, ',', '.')]) }}</div>
                                <div class="text-xl font-bold text-amber-600">{{ number_format($quoteTaxAmount, 2, ',', '.') }} €</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500">{{ __('mapscalculator.total') }}</div>
                                <div class="text-2xl font-bold text-primary-600">{{ number_format($quoteTotal, 2, ',', '.') }} €</div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-4">
                            <button wire:click="addQuoteItem" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">+ {{ __('mapscalculator.concept') }}</button>
                            @if(!$project->latestQuote)
                                <button wire:click="createQuote" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium">{{ __('mapscalculator.create_quote') }}</button>
                            @else
                                @if($project->latestQuote->status === 'borrador')
                                    <button wire:click="sendQuoteEmail" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">{{ __('mapscalculator.send_email_pdf') }}</button>
                                    <button wire:click="sendQuoteWhatsApp" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">{{ __('mapscalculator.send_whatsapp') }}</button>
                                    <a href="{{ route('mapscalculator.quote.pdf.view', $project->latestQuote) }}" target="_blank" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">{{ __('mapscalculator.view_quote_pdf') }}</a>
                                @elseif($project->latestQuote->status === 'enviado')
                                    <button wire:click="approveQuote" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">{{ __('mapscalculator.approve') }}</button>
                                    <button wire:click="rejectQuote" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">{{ __('mapscalculator.reject') }}</button>
                                @elseif($project->latestQuote->status === 'aprobado')
                                    <span class="px-6 py-2 bg-green-100 text-green-800 rounded-lg font-medium">{{ __('mapscalculator.approved') }} ✓</span>
                                @elseif($project->latestQuote->status === 'rechazado')
                                    <span class="px-6 py-2 bg-red-100 text-red-800 rounded-lg font-medium">{{ __('mapscalculator.rejected') }} ✗</span>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Historial de versiones (Módulo 2): al modificar un presupuesto ya
                 enviado se crea una versión nueva sin perder la anterior. --}}
            @if($project->quotes->isNotEmpty())
                <div class="bg-white rounded-lg border shadow-sm">
                    <div class="p-4 border-b flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-medium text-gray-900">{{ __('mapscalculator.quote_versions') }}</h3>
                        <button wire:click="createQuoteVersion" class="px-3 py-1.5 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                            + {{ __('mapscalculator.new_version') }}
                        </button>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach($project->quotes as $versionQuote)
                            <li class="p-4 flex flex-wrap items-center justify-between gap-2 {{ $project->latestQuote?->id === $versionQuote->id ? 'bg-primary-50' : '' }}">
                                <div class="text-sm">
                                    <span class="font-medium text-gray-900">{{ __('mapscalculator.version') }} {{ $versionQuote->version ?: 1 }}</span>
                                    <span class="text-gray-500">· {{ $versionQuote->formatted_number }}</span>
                                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-700">{{ $versionQuote->status_label }}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-3 text-sm">
                                    <span class="text-gray-500">{{ $versionQuote->created_at?->format('d/m/Y H:i') }}</span>
                                    <span class="font-medium text-gray-900">{{ number_format((float) $versionQuote->total, 2, ',', '.') }} €</span>
                                    <a href="{{ route('mapscalculator.quote.pdf', $versionQuote) }}" class="text-primary-600 hover:underline">{{ __('mapscalculator.download_quote_pdf') }}</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        @endif

        @if($this->workOrdersEnabled())
        <div x-show="$wire.activeTab === 'work'" x-cloak>
            @if($project->workOrder)
                <livewire:maps-calculator.work-order :workOrder="$project->workOrder" :project="$project" />
            @elseif($project->latestQuote && $project->latestQuote->status === 'aprobado')
                <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                    <svg class="w-12 h-12 mx-auto text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-lg font-medium text-green-800 mb-2">{{ __('mapscalculator.quote_approved') }}</h3>
                    <p class="text-green-700 mb-4">{{ __('mapscalculator.create_work_order_desc') }}</p>
                    <button wire:click="createWorkOrder" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">{{ __('mapscalculator.create_work_order') }}</button>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <h3 class="text-lg font-medium text-gray-600 mb-2">{{ __('mapscalculator.no_active_work') }}</h3>
                    <p class="text-gray-500">{{ __('mapscalculator.work_activated_desc') }}</p>
                </div>
            @endif
        </div>
        @endif

        <div x-show="$wire.activeTab === 'settings'" x-cloak class="space-y-6">
            <div class="bg-white rounded-lg border shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('mapscalculator.project_settings') }}</h3>
                
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.default_map_layer') }}</label>
                        <select wire:model="defaultMapLayer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="satellite">{{ __('mapscalculator.satellite') }}</option>
                            <option value="pnoa">{{ __('mapscalculator.pnoa') }}</option>
                            <option value="hybrid">{{ __('mapscalculator.hybrid') }}</option>
                            <option value="roadmap">{{ __('mapscalculator.roadmap') }}</option>
                            <option value="terrain">{{ __('mapscalculator.terrain') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.default_zoom') }}</label>
                        <input type="number" step="0.1" min="1" max="20" wire:model="defaultMapZoom" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="{{ $defaultMapZoom }}">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.default_map_center') }}</label>
                        <div class="flex gap-2">
                            <input type="number" step="0.000001" placeholder="{{ __('mapscalculator.latitude') }}" wire:model="defaultMapCenter.0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="{{ $defaultMapCenter[0] ?? '' }}">
                            <input type="number" step="0.000001" placeholder="{{ __('mapscalculator.longitude') }}" wire:model="defaultMapCenter.1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" value="{{ $defaultMapCenter[1] ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h4 class="font-medium text-gray-900 mb-4">{{ __('mapscalculator.share_project') }}</h4>
                    <div class="flex items-center gap-4">
                        <input type="text" readonly value="{{ $project->share_url ?? __('mapscalculator.generate_link_for_sharing') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm">
                        <button wire:click="shareProject" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                            {{ $project->share_token ? __('mapscalculator.copy_link') : __('mapscalculator.generate_link') }}
                        </button>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h4 class="font-medium text-gray-900 mb-1">{{ __('mapscalculator.client_portal') }}</h4>
                    <p class="text-sm text-gray-500 mb-4">{{ __('mapscalculator.client_portal_help') }}</p>
                    <div class="flex items-center gap-4">
                        <input type="text" readonly value="{{ $project->client?->portal_url ?? __('mapscalculator.generate_link_for_sharing') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm">
                        <button wire:click="generatePortalLink" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
                            {{ $project->client?->portal_token ? __('mapscalculator.copy_link') : __('mapscalculator.generate_link') }}
                        </button>
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h4 class="font-medium text-gray-900 mb-4">{{ __('mapscalculator.sync') }}</h4>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ __('mapscalculator.auto_sync') }}</p>
                            <p class="text-sm text-gray-500">{{ __('mapscalculator.auto_sync_desc') }}</p>
                        </div>
                        <button wire:click="syncProject" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">{{ __('mapscalculator.sync_now') }}</button>
                    </div>
                </div>
            </div>

        </div>
    @endif

    {{-- Modal: alta de cliente desde el módulo (CRM) --}}
    @if($showClientModal)
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" wire:key="client-modal">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ __('mapscalculator.new_client') }}</h3>
                <div class="grid gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.name') }} *</label>
                        <input type="text" wire:model="clientName" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('clientName') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.company') }}</label>
                            <input type="text" wire:model="clientCompany" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.phone') }}</label>
                            <input type="text" wire:model="clientPhone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.email') }}</label>
                        <input type="email" wire:model="clientEmail" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error('clientEmail') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.address') }}</label>
                        <textarea wire:model="clientAddress" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="$set('showClientModal', false)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.cancel') }}</button>
                    <button type="button" wire:click="createClient" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium">{{ __('mapscalculator.save') }}</button>
                </div>
            </div>
        </div>
    @endif

    <!-- El selector de capa ya se renderiza en la cabecera; aquí sólo queda el
         modal de sincronización, dentro de la raíz única que exige Livewire. -->
    @livewire('maps-calculator.sync-modal', ['projectId' => $project?->id ?? 0])
</div>