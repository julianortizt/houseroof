{{-- Editor de medición sobre mapa satelital.
     El área de trabajo ocupa todo el alto disponible de la ventana (antes eran
     600 px fijos) y las capas flotantes se colocan abajo, para no tapar las
     herramientas de Leaflet.draw que viven arriba a la izquierda.

     OJO: el mapa NO puede quedar dentro de la zona que Livewire vuelve a pintar.
     Cada evento que llega al componente (`geojsonUpdated`, `linesUpdated`,
     `circlesUpdated`...) provoca un re-render y Livewire eliminaba los paneles y
     las teselas que Leaflet crea dentro de `#map`, dejando el área del mapa en
     blanco aunque los totales de abajo siguieran ahí. De ahí `wire:ignore.self`
     en la raíz (para que no se reescriba el `x-data`, que reinicializaba Alpine)
     y `wire:ignore` en el contenedor del mapa. --}}
<div x-data="exteriorMeasurement(@js([
        'label' => $label,
        'unit' => $unit,
        'geojson' => $geojson,
        'lines' => $lines,
        'circles' => $circles,
        'layer' => $mapLayer,
        'center' => $mapCenter,
        'zoom' => $mapZoom,
        'maptilerKey' => config('services.maptiler.key', ''),
    ]))"
     wire:ignore.self
     x-init="initMap($el)"
     @geojson-imported.window="loadGeojson($event.detail.geojson)"
     @measurement-saved.window="onSaved()"
     x-ref="editor"
     class="w-full flex-1 flex flex-col gap-2 min-h-0">

    {{-- Barra superior. El buscador satelital vive aquí, siempre visible:
         dentro del mapa quedaba tapado por las herramientas de dibujo, y hay
         que poder buscar otra dirección en cualquier momento. --}}
    <div class="shrink-0 bg-white rounded-lg shadow p-3 space-y-2">
        {{-- Buscar por dirección, por coordenadas o por geolocalización --}}
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-1 flex-1 min-w-[260px]">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input type="text" x-model="addressQuery" aria-label="{{ __('mapscalculator.search_address') }}"
                       placeholder="{{ __('mapscalculator.address_placeholder') }}"
                       class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                       @keydown.enter="searchAddress()">
                <button type="button" @click="searchAddress()" :disabled="isSearchingAddress"
                        class="px-3 py-2 text-sm bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition disabled:opacity-50 whitespace-nowrap">
                    <span x-show="!isSearchingAddress">{{ __('mapscalculator.search') }}</span>
                    <span x-show="isSearchingAddress">{{ __('mapscalculator.searching_address') }}</span>
                </button>
            </div>

            <div class="flex items-center gap-1">
                <span class="text-xs font-medium text-gray-500 hidden xl:inline">{{ __('mapscalculator.search_coordinates') }}</span>
                <input type="text" x-model="searchLat" placeholder="{{ __('mapscalculator.latitude') }}"
                       class="w-28 px-2 py-2 text-sm border border-gray-300 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-primary-500" @keydown.enter="searchCoordinates()">
                <input type="text" x-model="searchLng" placeholder="{{ __('mapscalculator.longitude') }}"
                       class="w-28 px-2 py-2 text-sm border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500" @keydown.enter="searchCoordinates()">
                <button type="button" @click="searchCoordinates()" class="px-3 py-2 text-sm bg-primary-600 text-white rounded-r-lg hover:bg-primary-700 transition" title="{{ __('mapscalculator.search_coordinates') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                </button>
            </div>

            <button type="button" @click="useCurrentLocation()"
                    class="px-3 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-1"
                    title="{{ __('mapscalculator.use_current_location') }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 2 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
                <span class="hidden sm:inline">{{ __('mapscalculator.current_location') }}</span>
            </button>

            <span class="text-xs font-mono text-gray-500 ml-auto" x-text="formatLatLng(currentLat, currentLng)"></span>
        </div>

        {{-- Nombre, unidades y acciones --}}
        <div class="flex flex-wrap items-end gap-2 border-t border-gray-100 pt-2">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.label') }}</label>
                <input type="text" x-model="label"
                       class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                       placeholder="{{ __('mapscalculator.label_placeholder') }}">
            </div>
            <div class="w-40">
                <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.units') }}</label>
                <select x-model="unit" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="m2">{{ __('mapscalculator.square_meters') }}</option>
                    <option value="ha">{{ __('mapscalculator.hectares') }}</option>
                    <option value="ft2">{{ __('mapscalculator.square_feet') }}</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="button" @click="openImport()" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm" title="{{ __('mapscalculator.import_geojson') }}">
                    {{ __('mapscalculator.import_geojson') }}
                </button>
                <button type="button" @click="openExport()" :disabled="shapeCount === 0" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm disabled:opacity-40" title="{{ __('mapscalculator.export_geojson') }}">
                    {{ __('mapscalculator.export_geojson') }}
                </button>
                <button type="button" @click="saveMeasurement()" :disabled="shapeCount === 0"
                        class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium text-sm disabled:opacity-50"
                        wire:loading.attr="disabled" wire:target="save">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                    <span wire:loading.remove wire:target="save">{{ __('mapscalculator.save_measurement') }}</span>
                    <span wire:loading wire:target="save">{{ __('mapscalculator.processing') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Área de trabajo: el mapa se queda con todo el espacio restante y el
         panel de Street View comparte la fila a la derecha. `wire:ignore`:
         Leaflet y Leaflet.draw crean sus panes y controles dentro de `#map`;
         si Livewire los vuelve a pintar, el mapa queda en blanco. --}}
    <div class="relative flex-1 min-h-0 flex gap-2" wire:ignore>
        <div class="relative flex-1 min-w-0">
            <div id="map" class="absolute inset-0 rounded-lg border border-gray-200"></div>

        {{-- Herramientas de dibujo y edición --}}
        <div class="absolute top-3 right-3 z-20 bg-white/95 backdrop-blur-sm rounded-lg shadow-lg p-1.5 flex flex-col gap-1">
            <span class="px-1 pt-1 text-[10px] uppercase tracking-wide text-gray-400 text-center">{{ __('mapscalculator.tools') }}</span>

            <button type="button" @click="drawPolygon()" :class="mode === 'polygon' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="p-2 rounded-md transition" title="{{ __('mapscalculator.tool_polygon') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l8 5v8l-8 5-8-5V8l8-5z"></path></svg>
            </button>
            <button type="button" @click="drawRectangle()" :class="mode === 'rectangle' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="p-2 rounded-md transition" title="{{ __('mapscalculator.tool_rectangle') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16v14H4z"></path></svg>
            </button>
            <button type="button" @click="drawCircle()" :class="mode === 'circle' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="p-2 rounded-md transition" title="{{ __('mapscalculator.tool_circle') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18z"></path></svg>
            </button>
            <button type="button" @click="drawLine()" :class="mode === 'polyline' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="p-2 rounded-md transition" title="{{ __('mapscalculator.tool_line') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 18L20 6"></path></svg>
            </button>
            <button type="button" @click="toggleSmoothMode()" :class="smoothDraw ? 'bg-secondary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="p-2 rounded-md transition" title="{{ __('mapscalculator.tool_smooth') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17c3-6 6 6 9 0s6-6 9 0"></path></svg>
            </button>

            <div class="h-px bg-gray-200 mx-1"></div>

            <button type="button" @click="editMode()" :class="mode === 'edit' ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="p-2 rounded-md transition" title="{{ __('mapscalculator.edit') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </button>
            <button type="button" @click="deleteMode()" :class="mode === 'remove' ? 'bg-red-600 text-white' : 'bg-gray-100 text-red-600 hover:bg-red-100'"
                    class="p-2 rounded-md transition" title="{{ __('mapscalculator.delete') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v12m-6 0h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12"></path></svg>
            </button>

            <div class="h-px bg-gray-200 mx-1"></div>

            <button type="button" @click="undo()" :disabled="history.length === 0"
                    class="p-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition disabled:opacity-40" title="{{ __('mapscalculator.undo') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a5 5 0 015 5v2M3 10l5-5M3 10l5 5"></path></svg>
            </button>
            <button type="button" @click="redo()" :disabled="redoStack.length === 0"
                    class="p-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition disabled:opacity-40" title="{{ __('mapscalculator.redo') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a5 5 0 00-5 5v2M21 10l-5-5M21 10l-5 5"></path></svg>
            </button>

            <div class="h-px bg-gray-200 mx-1"></div>

            <button type="button" @click="useCurrentLocation()"
                    class="p-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition" title="{{ __('mapscalculator.use_current_location') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 2 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path></svg>
            </button>
            <button type="button" @click="toggleFullscreen()"
                    class="p-2 rounded-md bg-gray-100 text-gray-700 hover:bg-gray-200 transition"
                    :title="isFullscreen ? '{{ __('mapscalculator.exit_fullscreen') }}' : '{{ __('mapscalculator.fullscreen') }}'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4h4m8 0h4v4m0 8v4h-4M8 20H4v-4"></path></svg>
            </button>
            <button type="button" @click="clearAll()" :disabled="shapeCount === 0"
                    class="p-2 rounded-md bg-gray-100 text-red-600 hover:bg-red-100 transition disabled:opacity-40" title="{{ __('mapscalculator.clear_all') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Totales en vivo. Van abajo, dentro del mapa, sin tapar las
             herramientas de dibujo (arriba a la izquierda) ni el selector de capas. --}}
        {{-- Ojo: `w-[min(...)]` no lo genera Tailwind (los paréntesis del valor
             arbitrario lo rompen); con max-w sí. --}}
        <div class="absolute bottom-3 left-3 right-3 z-20 w-auto max-w-[32rem]">
            <div class="bg-white/95 backdrop-blur-sm rounded-lg shadow-lg p-3 space-y-2">
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-medium text-gray-500">{{ __('mapscalculator.total_area') }}:</span>
                        <span class="text-base font-bold text-primary-600" x-text="formatArea(totalArea)"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-medium text-gray-500">{{ __('mapscalculator.perimeter') }}:</span>
                        <span class="text-base font-bold text-secondary-600" x-text="formatLength(totalPerimeter)"></span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span class="text-xs font-medium text-gray-500">{{ __('mapscalculator.shapes') }}:</span>
                        <span class="text-base font-bold" x-text="shapeCount"></span>
                    </div>
                </div>
            </div>
        </div>
        </div>{{-- cierra el wrapper del mapa --}}

        {{-- Panel de Street View (Mapillary): hermano del mapa dentro de la
             fila; el mapa sigue siendo usable para medir mientras está abierto. --}}
        <div x-show="svOpen" x-cloak
             class="absolute md:static inset-y-0 right-0 z-30 w-[85%] md:w-[44%] md:min-w-[300px] md:max-w-[600px] shrink-0 flex flex-col bg-white md:rounded-lg border border-gray-200 shadow-xl md:shadow-none overflow-hidden">
            <div class="flex items-center justify-between px-3 py-2 bg-gray-50 border-b border-gray-200 shrink-0">
                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ __('mapscalculator.street_view') }}</span>
                <button type="button" @click="toggleStreetView()" class="p-1 rounded hover:bg-gray-200 text-gray-500"
                        title="{{ __('mapscalculator.close') }}" aria-label="{{ __('mapscalculator.close') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="mly" class="flex-1 min-h-0"></div>
        </div>
    </div>

    {{-- Modal: importar GeoJSON --}}
    <template x-if="showImport">
        <div class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4" @click.self="showImport = false">
            <div class="bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold mb-2">{{ __('mapscalculator.import_geojson') }}</h3>
                <p class="text-sm text-gray-500 mb-4">{{ __('mapscalculator.import_geojson_help') }}</p>
                <input type="file" accept=".geojson,.json,application/geo+json,application/json" @change="readImportFile($event)"
                       class="w-full text-sm mb-3">
                <textarea x-model="importText" class="w-full h-48 font-mono text-xs p-3 border rounded-lg" placeholder='{"type":"FeatureCollection","features":[]}'></textarea>
                <div class="mt-4 flex gap-2 justify-end">
                    <button type="button" @click="showImport = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.cancel') }}</button>
                    <button type="button" @click="doImport()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">{{ __('mapscalculator.import_geojson') }}</button>
                </div>
            </div>
        </div>
    </template>

    {{-- Modal: exportar GeoJSON --}}
    <template x-if="showExport">
        <div class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4" @click.self="showExport = false">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('mapscalculator.export_geojson') }}</h3>
                <textarea class="w-full h-64 font-mono text-xs p-3 border rounded-lg" readonly x-text="exportText"></textarea>
                <div class="mt-4 flex gap-2 justify-end">
                    <button type="button" @click="copyGeojson()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.copy_geojson') }}</button>
                    <button type="button" @click="downloadGeojson()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">{{ __('mapscalculator.download_geojson') }}</button>
                    <button type="button" @click="showExport = false" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.close') }}</button>
                </div>
            </div>
        </div>
    </template>

{{-- Estilos del panel de zoom propio de Leaflet. Va en la vista porque es
     contenido dinámico del mapa y no debe colar en el CSS global. --}}
<style>
    .mczoom { display: flex; flex-direction: column; gap: 4px; padding: 5px; width: 134px; }
    .mczoom-row { display: flex; align-items: center; gap: 4px; }
    .mczoom-btn { flex: 1; min-width: 0; height: 32px; display: flex; align-items: center; justify-content: center; padding: 0; font-size: 17px; line-height: 1; color: #1f2937; background: #fff; border: 1px solid #d1d5db; border-radius: 4px; cursor: pointer; }
    .mczoom-btn:hover { background: #f3f4f6; }
    .mczoom-btn svg { width: 16px; height: 16px; }
    .mczoom-street { font-size: 11px; font-weight: 700; }
    .mczoom-level { flex: 1; text-align: center; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 11px; font-weight: 600; color: #374151; user-select: none; }
    .mczoom-slider { width: 100%; margin: 0; accent-color: #2563eb; cursor: pointer; }
    [x-cloak] { display: none !important; }
</style>

<script>
// Visor Mapillary fuera del estado reactivo de Alpine: los objetos de
// librerías externas no deben pasar por el Proxy de reactividad.
let mlyViewer = null;
let mlyLibPromise = null;
/**
 * Editor de medición exterior sobre mapa satelital (Leaflet + Leaflet.draw).
 *
 * Todas las figuras (polígonos, rectángulos, círculos y líneas) viven en UN
 * único FeatureGroup. Antes había tres grupos separados y el de edición/borrado
 * era sólo el de polígonos: un círculo o una línea dibujados no se podían
 * editar ni borrar nunca.
 */
function exteriorMeasurement(initial = {}) {
    const emptyCollection = { type: 'FeatureCollection', features: [] };

    return {
        map: null,
        drawnItems: null,
        drawControl: null,
        mode: 'view',           // view | polygon | rectangle | circle | polyline | edit | remove
        history: [],
        redoStack: [],

        label: initial.label || '',
        unit: initial.unit || 'm2',
        totalArea: 0,
        totalPerimeter: 0,
        shapeCount: 0,

        mapLayer: initial.layer || 'pnoa',
        baseLayers: {},
        labelsLayer: null,
        smoothDraw: false,
        svPoint: null,          // último punto pinchado/buscado para Street View
        svOpen: false,
        svToken: @js(config('services.mapillary.token')),

        isFullscreen: false,
        isSearching: false,
        isSearchingAddress: false,
        addressQuery: '',
        searchLat: '',
        searchLng: '',
        currentLat: 40.4168,
        currentLng: -3.7038,
        searchMarker: null,

        showImport: false,
        importText: '',
        showExport: false,

        geojson: initial.geojson && initial.geojson.features ? initial.geojson : emptyCollection,
        initialLines: initial.lines || [],
        initialCircles: initial.circles || [],
        initialCenter: initial.center || null,
        initialZoom: initial.zoom || null,

        initMap(root) {
            this.root = root;

            const container = document.getElementById('map');
            if (!container) return;

            // Si por lo que sea Alpine vuelve a inicializar este componente
            // (por ejemplo tras un re-render de Livewire), el contenedor aún
            // conserva el `_leaflet_id` del mapa anterior y `L.map()` lanzaría
            // "Map container is already initialized", dejando el mapa en blanco.
            if (container._leaflet_id) {
                container._leaflet_id = null;
                container.innerHTML = '';
            }

            // Zoom continuo (zoomSnap 0) y pasos de medio nivel: permite
            // encuadrar una parcela sin quedarse atascado en niveles enteros,
            // y la rueda del ratón avanza despacio (wheelPxPerZoomLevel).
            // El +/- por defecto se sustituye por el panel propio (buildZoomControl).
            this.map = L.map(container, {
                zoomControl: false,
                zoomSnap: 0,
                zoomDelta: 0.5,
                wheelPxPerZoomLevel: 120,
                minZoom: 3,
                maxZoom: 21,
            }).setView(this.initialCenter || [40.4168, -3.7038], this.initialZoom || 15);

            this.baseLayers = {
                satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    // Esri publica teselas nativas hasta Z20 en la mayoría de
                    // zonas; el sobre-zoom a Z21 sigue disponible.
                    maxNativeZoom: 20,
                    maxZoom: 21,
                    attribution: 'Tiles © Esri',
                }),
                pnoa: L.tileLayer('https://www.ign.es/wmts/pnoa-ma?request=getTile&service=WMTS&version=1.0.0&layer=OI.OrthoimageCoverage&TileMatrixSet=GoogleMapsCompatible&format=image/jpeg&TileMatrix={z}&TileCol={x}&TileRow={y}', {
                    // Ortofotos PNOA del IGN: teselas nativas garantizadas
                    // hasta Z19; por encima, Leaflet estira la tesela de Z19.
                    maxNativeZoom: 19,
                    maxZoom: 21,
                    attribution: '© IGN España — PNOA',
                }),
                roadmap: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxNativeZoom: 19,
                    maxZoom: 21,
                    attribution: '© OpenStreetMap',
                }),
                terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
                    maxNativeZoom: 17,
                    maxZoom: 21,
                    attribution: '© OpenTopoMap',
                }),
            };

            // Capa opcional MapTiler Satellite. Solo se registra si hay una API key
            // configurada (MAPTILER_KEY en .env). Sin ella, la medición funciona igual
            // con PNOA/Esri como hasta ahora.
            const maptilerKey = (initial.maptilerKey || '').trim();
            if (maptilerKey) {
                this.baseLayers.maptiler = L.tileLayer(
                    'https://api.maptiler.com/tiles/satellite/{z}/{y}/{x}.jpg?key=' + maptilerKey,
                    {
                        maxNativeZoom: 19,
                        maxZoom: 22,
                        attribution: '© MapTiler © OpenStreetMap contributors',
                    }
                );
            }

            // Capa de etiquetas: la vista "híbrida" es el satélite con nombres.
            this.labelsLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
                maxNativeZoom: 19,
                maxZoom: 21,
                attribution: 'Tiles © Esri',
            });

            // La capa por defecto del proyecto se guardaba en el panel pero el
            // editor la ignoraba: siempre abría en satélite.
            (this.baseLayers[this.mapLayer] || this.baseLayers.satellite).addTo(this.map);

            if (this.mapLayer === 'hybrid') this.labelsLayer.addTo(this.map);

            // El selector de capas se colocaba arriba a la derecha, justo debajo
            // de esta barra de herramientas: quedaba tapado y no se podía usar.
            // Panel de zoom propio: se añade antes que el selector de capas
            // para que quede arriba del todo en la columna izquierda.
            this.buildZoomControl();

            const baseLayerOptions = {
                '{{ __('mapscalculator.satellite') }}': this.baseLayers.satellite,
                '{{ __('mapscalculator.pnoa') }}': this.baseLayers.pnoa,
                '{{ __('mapscalculator.roadmap') }}': this.baseLayers.roadmap,
                '{{ __('mapscalculator.terrain') }}': this.baseLayers.terrain,
            };
            if (this.baseLayers.maptiler) {
                baseLayerOptions['Satellite (MapTiler)'] = this.baseLayers.maptiler;
            }
            L.control.layers(baseLayerOptions, {
                '{{ __('mapscalculator.hybrid') }}': this.labelsLayer,
            }, { position: 'topleft' }).addTo(this.map);

            this.drawnItems = new L.FeatureGroup();
            this.map.addLayer(this.drawnItems);

            this.buildDrawControl();
            this.loadInitialState();
            this.setupEventListeners();
            this.updateTotals();

            // Leaflet mide el contenedor una sola vez, al crearse. Con el layout
            // flexible (o al girar el móvil) las teselas quedaban recortadas: se
            // recalcula el tamaño del mapa cuando cambia el contenedor.
            setTimeout(() => this.map && this.map.invalidateSize(), 200);
            if (window.ResizeObserver) {
                new ResizeObserver(() => this.map && this.map.invalidateSize()).observe(this.map.getContainer());
            }
            document.addEventListener('fullscreenchange', () => {
                this.isFullscreen = !!document.fullscreenElement;
                setTimeout(() => this.map && this.map.invalidateSize(), 250);
            });
        },

        /**
         * Panel de zoom propio que sustituye al +/- por defecto de Leaflet:
         * botones grandes, indicador de nivel (Z18), deslizador continuo,
         * «encuadrar figuras» y acceso a Google Street View del centro del mapa.
         */
        buildZoomControl() {
            const map = this.map;
            const component = this;
            const minZoom = map.getMinZoom();
            const maxZoom = map.getMaxZoom();
            const labelIn = '{{ __('mapscalculator.zoom_in') }}';
            const labelOut = '{{ __('mapscalculator.zoom_out') }}';
            const labelFit = '{{ __('mapscalculator.fit_shapes') }}';
            const labelStreet = '{{ __('mapscalculator.street_view') }}';

            const ZoomPanel = L.Control.extend({
                options: { position: 'topleft' },

                onAdd() {
                    const el = L.DomUtil.create('div', 'leaflet-bar mczoom');
                    el.innerHTML = `
                        <div class="mczoom-row">
                            <button type="button" class="mczoom-btn" data-zoom="out" title="${labelOut}" aria-label="${labelOut}">−</button>
                            <span class="mczoom-level" data-zoom-level>Z${map.getZoom()}</span>
                            <button type="button" class="mczoom-btn" data-zoom="in" title="${labelIn}" aria-label="${labelIn}">+</button>
                        </div>
                        <input type="range" class="mczoom-slider" data-zoom-slider
                               min="${minZoom}" max="${maxZoom}" step="0.5"
                               value="${map.getZoom()}" aria-label="Zoom">
                        <div class="mczoom-row">
                            <button type="button" class="mczoom-btn" data-zoom="fit" title="${labelFit}" aria-label="${labelFit}">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"></path></svg>
                            </button>
                            <button type="button" class="mczoom-btn mczoom-street" data-zoom="street" title="${labelStreet}" aria-label="${labelStreet}">SV</button>
                        </div>`;

                    // Nada de lo de dentro debe arrastrar el mapa ni hacer zoom
                    // con la rueda: Leaflet lo escucha en el contenedor.
                    L.DomEvent.disableClickPropagation(el);
                    L.DomEvent.disableScrollPropagation(el);
                    ['mousedown', 'touchstart', 'pointerdown', 'dblclick'].forEach((type) => {
                        L.DomEvent.on(el, type, L.DomEvent.stopPropagation);
                    });

                    const slider = el.querySelector('[data-zoom-slider]');
                    const level = el.querySelector('[data-zoom-level]');

                    el.querySelector('[data-zoom="out"]').addEventListener('click', () => map.zoomOut(0.5));
                    el.querySelector('[data-zoom="in"]').addEventListener('click', () => map.zoomIn(0.5));
                    el.querySelector('[data-zoom="fit"]').addEventListener('click', () => component.fitShapes());
                    el.querySelector('[data-zoom="street"]').addEventListener('click', () => component.toggleStreetView());
                    slider.addEventListener('input', () => map.setZoom(Number(slider.value), { animate: false }));

                    const sync = () => {
                        const zoom = map.getZoom();
                        level.textContent = 'Z' + (Math.round(zoom * 10) / 10);
                        // No pisar el valor mientras el usuario mueve el deslizador.
                        if (document.activeElement !== slider) slider.value = String(zoom);
                    };
                    map.on('zoomend', sync);
                    sync();

                    return el;
                },
            });

            map.addControl(new ZoomPanel());
        },

        buildDrawControl() {
            this.drawControl = new L.Control.Draw({
                position: 'topleft',
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        metric: true,
                        feet: false,
                        shapeOptions: { color: '#2563eb', weight: 3, opacity: 0.8, fillColor: '#2563eb', fillOpacity: 0.2 },
                    },
                    rectangle: {
                        showArea: true,
                        metric: true,
                        feet: false,
                        shapeOptions: { color: '#2563eb', weight: 3, fillColor: '#2563eb', fillOpacity: 0.2 },
                    },
                    circle: {
                        showRadius: true,
                        metric: true,
                        feet: false,
                        shapeOptions: { color: '#d97706', weight: 3, fillColor: '#d97706', fillOpacity: 0.2 },
                    },
                    polyline: {
                        metric: true,
                        feet: false,
                        shapeOptions: { color: '#059669', weight: 3 },
                    },
                    marker: false,
                    circlemarker: false,
                },
                edit: {
                    featureGroup: this.drawnItems,
                    remove: true,
                    edit: {
                        selectedPathOptions: { color: '#dc2626', weight: 3, opacity: 0.8, fillColor: '#dc2626', fillOpacity: 0.2 },
                    },
                },
            });

            this.map.addControl(this.drawControl);
        },

        // ----------------------------------------------------------- herramientas

        drawHandler(name) {
            const toolbar = this.drawControl && this.drawControl._toolbars ? this.drawControl._toolbars.draw : null;
            return toolbar && toolbar._modes && toolbar._modes[name] ? toolbar._modes[name].handler : null;
        },

        editHandler(name) {
            const toolbar = this.drawControl && this.drawControl._toolbars ? this.drawControl._toolbars.edit : null;
            return toolbar && toolbar._modes && toolbar._modes[name] ? toolbar._modes[name].handler : null;
        },

        disableHandlers() {
            ['polygon', 'rectangle', 'circle', 'polyline'].forEach((name) => {
                const handler = this.drawHandler(name);
                if (handler) handler.disable();
            });
            ['edit', 'remove'].forEach((name) => {
                const handler = this.editHandler(name);
                if (handler) handler.disable();
            });
        },

        startTool(tool) {
            const next = this.mode === tool ? 'view' : tool;
            this.disableHandlers();
            this.mode = next;

            if (next === 'view') return;

            const handler = (next === 'edit' || next === 'remove')
                ? this.editHandler(next)
                : this.drawHandler(next);

            if (handler) handler.enable();
        },

        drawPolygon() { this.startTool('polygon'); },
        drawRectangle() { this.startTool('rectangle'); },
        drawCircle() { this.startTool('circle'); },
        drawLine() { this.startTool('polyline'); },
        editMode() { this.startTool('edit'); },
        deleteMode() { this.startTool('remove'); },

        toggleSmoothMode() {
            this.smoothDraw = !this.smoothDraw;
            this.$dispatch('notify', {
                message: this.smoothDraw
                    ? '{{ __('mapscalculator.tool_smooth_hint') }}'
                    : '{{ __('mapscalcalculator.edge_straight') }}',
                type: 'info',
            });
        },

        /**
         * Densifica un anillo/polyline de latlngs con Catmull-Rom cerrado
         * (o abierto si open=true). Las coordenadas se tratan en el plano
         * local lng/lat; para parcelas la distorsión es irrelevante.
         */
        smoothLatLngs(latlngs, open = false) {
            if (!latlngs || latlngs.length < 3) return latlngs ? latlngs.slice() : [];
            const pts = latlngs.map((ll) => ({ x: ll.lng, y: ll.lat }));
            const n = pts.length;
            const t = 0.3;
            const steps = 16;
            const out = [];
            const segCount = open ? n - 1 : n;

            for (let i = 0; i < segCount; i++) {
                const p0 = open ? pts[Math.max(0, i - 1)] : pts[(i - 1 + n) % n];
                const p1 = pts[i];
                const p2 = open ? pts[Math.min(n - 1, i + 1)] : pts[(i + 1) % n];
                const p3 = open ? pts[Math.min(n - 1, i + 2)] : pts[(i + 2) % n];

                for (let s = 0; s < steps; s++) {
                    const st = s / steps;
                    const st2 = st * st;
                    const st3 = st2 * st;
                    out.push({
                        lat: (2 * st3 - 3 * st2 + 1) * p1.y
                            + (-2 * st3 + 3 * st2) * p2.y
                            + (st3 - 2 * st2 + st) * t * (p2.y - p0.y)
                            + (-st3 + st2) * t * (p3.y - p1.y),
                        lng: (2 * st3 - 3 * st2 + 1) * p1.x
                            + (-2 * st3 + 3 * st2) * p2.x
                            + (st3 - 2 * st2 + st) * t * (p2.x - p0.x)
                            + (-st3 + st2) * t * (p3.x - p1.x),
                    });
                }
            }

            if (open) out.push({ lat: pts[n - 1].y, lng: pts[n - 1].x });
            return out;
        },

        /** Suaviza un layer polilínea/polígono en sitio (mantiene estilos). */
        smoothLayer(layer) {
            if (!layer || this.isCircle(layer)) return false;
            if (layer instanceof L.Rectangle) return false;

            if (this.isPolygon(layer)) {
                const rings = layer.getLatLngs();
                // L.Polygon: [outerRing] o [outer, hole…]
                const smoothed = rings.map((ring) => this.smoothLatLngs(ring, false));
                layer.setLatLngs(smoothed);
                return true;
            }

            if (this.isLine(layer)) {
                layer.setLatLngs(this.smoothLatLngs(layer.getLatLngs(), true));
                return true;
            }

            return false;
        },

        /** Capa bajo el punto (polígono por ray-cast; línea por proximidad). */
        layerAt(latlng) {
            let hit = null;
            const layers = this.drawnItems.getLayers().slice().reverse();

            for (const layer of layers) {
                if (this.isCircle(layer)) continue;
                if (!layer.getBounds || !layer.getBounds().contains(latlng)) continue;

                if (this.isPolygon(layer)) {
                    const ring = layer.getLatLngs()[0] || [];
                    let inside = false;
                    for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                        const yi = ring[i].lat;
                        const yj = ring[j].lat;
                        const xi = ring[i].lng;
                        const xj = ring[j].lng;
                        const intersect = ((yi > latlng.lat) !== (yj > latlng.lat))
                            && (latlng.lng < ((xj - xi) * (latlng.lat - yi)) / ((yj - yi) || 1e-12) + xi);
                        if (intersect) inside = !inside;
                    }
                    if (inside) { hit = layer; break; }
                } else if (this.isLine(layer)) {
                    const pts = layer.getLatLngs();
                    for (let i = 0; i < pts.length - 1; i++) {
                        // Distancia aproximada en grados → ~metros razonables.
                        const mx = (pts[i].lng + pts[i + 1].lng) / 2;
                        const my = (pts[i].lat + pts[i + 1].lat) / 2;
                        if (Math.hypot(latlng.lng - mx, latlng.lat - my) < 0.00015) {
                            hit = layer;
                            break;
                        }
                    }
                    if (hit) break;
                }
            }

            return hit;
        },

        toggleFullscreen() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
                return;
            }
            const target = this.root || this.$el;
            if (target && target.requestFullscreen) target.requestFullscreen();
        },

        clearAll() {
            if (this.shapeCount === 0) return;
            if (!window.confirm('{{ __('mapscalculator.delete_confirmation') }}')) return;
            this.pushHistory();
            this.drawnItems.clearLayers();
            this.updateTotals();
            this.sync();
            this.$dispatch('notify', { message: '{{ __('mapscalculator.measurement_cleared') }}', type: 'info' });
        },

        /** Encuadra todas las figuras dibujadas en la vista. */
        fitShapes() {
            if (!this.drawnItems || this.drawnItems.getLayers().length === 0) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.no_shapes_fit') }}', type: 'info' });
                return;
            }

            this.map.fitBounds(this.drawnItems.getBounds(), { padding: [50, 50] });
        },

        /**
         * Alterna el panel de Street View (Mapillary) dentro de la página.
         * Sin token configurado cae al clásico comportamiento de pestaña nueva
         * de Google. Con token, abre un panel lateral: el mapa sigue usable
         * para medir mientras se mira la calle.
         */
        toggleStreetView() {
            try {
                if (!this.svToken) {
                    this.openStreetView();
                    return;
                }
                if (this.svOpen) {
                    this.closeStreetView();
                    return;
                }
                this.svOpen = true;
                this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_loading') }}', type: 'info' });
                this.$nextTick(() => {
                    Promise.resolve()
                        .then(() => this.mountMapViewillary())
                        .catch(() => {
                            this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_error') }}', type: 'error' });
                            this.svOpen = false;
                        });
                    setTimeout(() => this.map && this.map.invalidateSize(), 300);
                });
            } catch (e) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_error') }}', type: 'error' });
                this.svOpen = false;
            }
        },

        closeStreetView() {
            this.svOpen = false;
            if (mlyViewer) {
                try { mlyViewer.remove(); } catch (e) { /* noop */ }
                mlyViewer = null;
            }
            setTimeout(() => this.map && this.map.invalidateSize(), 300);
        },

        loadMapillaryLib() {
            if (window.mapillary) return Promise.resolve();
            if (!mlyLibPromise) {
                const scriptSources = [
                    'https://unpkg.com/mapillary-js@4.1.2/dist/mapillary.js',
                    'https://cdn.jsdelivr.net/npm/mapillary-js@4.1.2/dist/mapillary.js',
                ];
                const cssSources = [
                    'https://unpkg.com/mapillary-js@4.1.2/dist/mapillary.css',
                    'https://cdn.jsdelivr.net/npm/mapillary-js@4.1.2/dist/mapillary.css',
                ];
                mlyLibPromise = new Promise((resolve, reject) => {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = cssSources[0];
                    // El CSS es best-effort: si unpkg falla, jsdelivr.
                    link.onerror = () => { link.onerror = null; link.href = cssSources[1]; };
                    document.head.appendChild(link);
                    // Si unpkg falla (bloqueo/caída), se prueba jsdelivr antes de rendirse.
                    const tryScript = (i) => {
                        const s = document.createElement('script');
                        s.src = scriptSources[i];
                        s.async = true;
                        s.onload = () => resolve();
                        s.onerror = () => {
                            if (i + 1 < scriptSources.length) tryScript(i + 1);
                            else reject(new Error('mapillary.js failed to load'));
                        };
                        document.head.appendChild(s);
                    };
                    tryScript(0);
                });
            }
            return mlyLibPromise;
        },

        /**
         * Imagen Mapillary más cercana a un punto: bbox en la Graph API.
         * El span máximo seguro en zonas densas es ~0.01°; el bbox de 0.02°
         * que se usaba antes devolvía "reduce the amount of data" y el
         * flujo acababa en toast de error con el panel cerrado. Un bbox
         * rechazado por la API se trata como "siguiente caja", no como
         * excepción.
         */
        async findNearestImage(lat, lng) {
            const tryBox = async (d) => {
                const url = 'https://graph.mapillary.com/images?fields=id,computed_geometry'
                    + '&bbox=' + (lng - d) + ',' + (lat - d) + ',' + (lng + d) + ',' + (lat + d)
                    + '&access_token=' + encodeURIComponent(this.svToken);
                let res;
                const ctrl = typeof AbortController !== 'undefined' ? new AbortController() : null;
                const timer = ctrl ? setTimeout(() => ctrl.abort(), 8000) : null;
                try {
                    res = await fetch(url, ctrl ? { signal: ctrl.signal } : {});
                } catch (e) {
                    throw new Error('network');
                } finally {
                    if (timer) clearTimeout(timer);
                }
                const json = await res.json().catch(() => ({}));
                if (!res.ok || json.error) return null;
                const rows = Array.isArray(json.data) ? json.data : [];
                let best = null;
                let bestD = Infinity;
                for (const row of rows) {
                    const g = row.computed_geometry || row.geometry;
                    if (!Array.isArray(g) || typeof g[0] !== 'number') continue; // Point [lng, lat]
                    const dd = (g[1] - lat) ** 2 + (g[0] - lng) ** 2;
                    if (dd < bestD) { bestD = dd; best = row; }
                }
                return best;
            };
            // 0.002 (~220 m) y 0.005 (span 0.01°) siempre válidos; 0.008 y 0.01
            // son mejor-esfuerzo: en rural amplían la búsqueda y en ciudad densa
            // la API los rechaza → null → toast de "sin imágenes".
            for (const d of [0.002, 0.005, 0.008, 0.01]) {
                const img = await tryBox(d);
                if (img) return img;
            }
            return null;
        },

        async mountMapViewillary() {
            try {
                const p = this.svPoint || this.map.getCenter();
                if (!p || typeof p.lat !== 'number' || typeof p.lng !== 'number') {
                    throw new Error('bad point');
                }
                let img = null;
                try {
                    img = await this.findNearestImage(p.lat, p.lng);
                } catch (e) {
                    this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_error') }}', type: 'error' });
                    this.svOpen = false;
                    return;
                }
                if (!img) {
                    this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_no_imagery') }} (' + Number(p.lat).toFixed(5) + ', ' + Number(p.lng).toFixed(5) + ')', type: 'info' });
                    this.svOpen = false;
                    return;
                }
                try {
                    await this.loadMapillaryLib();
                } catch (e) {
                    this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_error') }}', type: 'error' });
                    this.svOpen = false;
                    return;
                }
                if (mlyViewer) {
                    try { mlyViewer.remove(); } catch (e) { /* noop */ }
                    mlyViewer = null;
                }
                mlyViewer = new mapillary.Viewer({
                    container: 'mly',
                    accessToken: this.svToken,
                    imageId: img.id,
                    component: { cover: false },
                });
            } catch (e) {
                mlyViewer = null;
                this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_error') }}', type: 'error' });
                this.svOpen = false;
            }
        },

        async jumpStreetView(pt) {
            if (!this.svOpen || !mlyViewer || !pt) return;
            try {
                const img = await this.findNearestImage(pt.lat, pt.lng);
                if (img) await mlyViewer.moveTo(img.id);
                else this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_no_imagery') }} (' + Number(pt.lat).toFixed(5) + ', ' + Number(pt.lng).toFixed(5) + ')', type: 'info' });
            } catch (e) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_error') }}', type: 'error' });
            }
        },

        /**
         * Abre Google Street View en una pestaña nueva sobre el último punto
         * pinchado o buscado en el mapa (o el centro si no hay punto). La URL
         * con `layer=c` no necesita clave de API ni SDK. Es el fallback cuando
         * no hay token de Mapillary configurado.
         */
        openStreetView() {
            try {
                const p = this.svPoint || (this.map && this.map.getCenter());
                if (!p || typeof p.lat !== 'number' || typeof p.lng !== 'number') {
                    this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_error') }}', type: 'error' });
                    return;
                }
                const url = 'https://www.google.com/maps?layer=c&cbll=' + Number(p.lat).toFixed(6) + ',' + Number(p.lng).toFixed(6);
                const win = window.open(url, '_blank', 'noopener,noreferrer');
                if (!win) {
                    this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_popup_blocked') }}', type: 'error' });
                }
            } catch (e) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.street_view_error') }}', type: 'error' });
            }
        },

        // ------------------------------------------------------------ eventos

        setupEventListeners() {
            // Último punto pinchado en vista: Street View abre/salta sobre él.
            // Si el modo "suavizar" está activo, el clic convierte la figura
            // existente en curva (o suaviza la recién tocada).
            this.map.on('click', (e) => {
                if (this.smoothDraw && this.mode === 'view') {
                    const layer = this.layerAt(e.latlng);
                    if (layer && (this.isPolygon(layer) || this.isLine(layer)) && !(layer instanceof L.Rectangle)) {
                        this.pushHistory();
                        if (this.smoothLayer(layer)) {
                            this.updateTotals();
                            this.sync();
                            this.$dispatch('notify', {
                                message: '{{ __('mapscalculator.tool_smooth') }}: ok',
                                type: 'success',
                            });
                            return;
                        }
                    }
                }

                if (this.mode !== 'view') return;
                this.svPoint = e.latlng;
                this.jumpStreetView(e.latlng);
            });

            this.map.on(L.Draw.Event.CREATED, (e) => {
                this.pushHistory();
                const layer = e.layer;

                if (e.layerType === 'polyline') {
                    layer.setStyle({ color: '#059669', weight: 3 });
                } else if (e.layerType === 'circle') {
                    layer.setStyle({ color: '#d97706', weight: 3, fillColor: '#d97706', fillOpacity: 0.2 });
                } else {
                    // Los rectángulos también son Polygon en GeoJSON, así que
                    // comparten grupo y totales con el polígono dibujado a mano.
                    layer.setStyle({ color: '#2563eb', weight: 3, fillColor: '#2563eb', fillOpacity: 0.2 });
                }

                // Con el modo curva activo, el polil/polígono recién creado
                // se densifica (los rectángulos se mantienen rectos).
                if (this.smoothDraw && e.layerType !== 'rectangle' && e.layerType !== 'circle') {
                    this.smoothLayer(layer);
                }

                this.drawnItems.addLayer(layer);
                this.updateTotals();
                this.sync();
                this.mode = 'view';
            });

            // El historial debe guardar el estado ANTERIOR al cambio: por eso se
            // apunta al empezar a editar o borrar, no al terminar.
            this.map.on('draw:editstart', () => this.pushHistory());
            this.map.on('draw:deletestart', () => this.pushHistory());

            this.map.on(L.Draw.Event.EDITED, () => { this.updateTotals(); this.sync(); });
            this.map.on(L.Draw.Event.DELETED, () => { this.updateTotals(); this.sync(); });

            this.map.on('draw:drawstart', (e) => { this.mode = e.layerType || 'polygon'; });
            this.map.on('draw:drawstop', () => { this.mode = 'view'; });
            this.map.on('draw:editstop', () => { this.mode = 'view'; });
            this.map.on('draw:deletestop', () => { this.mode = 'view'; });

            this.map.on('moveend', () => {
                const center = this.map.getCenter();
                this.currentLat = center.lat;
                this.currentLng = center.lng;
            });

            // Recuerda qué capa se está viendo para guardarla con la medición.
            this.map.on('baselayerchange', () => this.captureCurrentLayer());
            this.map.on('overlayadd overlayremove', () => this.captureCurrentLayer());
        },

        captureCurrentLayer() {
            const active = Object.keys(this.baseLayers).find((key) => this.map.hasLayer(this.baseLayers[key]));

            if (active === 'satellite' && this.labelsLayer && this.map.hasLayer(this.labelsLayer)) {
                this.mapLayer = 'hybrid';
                return;
            }

            if (active) this.mapLayer = active;
        },

        // ------------------------------------------------------------- figuras

        isCircle(layer) {
            return layer instanceof L.Circle;
        },

        isLine(layer) {
            return layer instanceof L.Polyline && !(layer instanceof L.Polygon);
        },

        isPolygon(layer) {
            return layer instanceof L.Polygon;
        },

        eachShape(callback) {
            this.drawnItems.eachLayer((layer) => {
                if (layer && layer.toGeoJSON) callback(layer);
            });
        },

        getPolygonFeatures() {
            const features = [];
            this.eachShape((layer) => {
                if (this.isPolygon(layer)) features.push(layer.toGeoJSON());
            });
            return features;
        },

        getLineFeatures() {
            const features = [];
            this.eachShape((layer) => {
                if (this.isLine(layer)) features.push(layer.toGeoJSON());
            });
            return features;
        },

        getCircleEntries() {
            const entries = [];
            this.eachShape((layer) => {
                if (!this.isCircle(layer)) return;
                const geojson = layer.toGeoJSON();
                entries.push({
                    properties: geojson.properties || {},
                    geometry: geojson.geometry || null,
                    radius: layer.getRadius(),
                });
            });
            return entries;
        },

        getGeojson() {
            return { type: 'FeatureCollection', features: this.getPolygonFeatures() };
        },

        /** Publica el estado al componente Livewire (totales del servidor). */
        sync() {
            // Livewire v3 rellena los parámetros del listener POR NOMBRE: hay que
            // mandar { geojson: ... } y no la geometría suelta, si no el binding
            // no encuentra `array $geojson` y devuelve un 500.
            this.$dispatch('geojsonUpdated', { geojson: this.getGeojson() });
            this.$dispatch('linesUpdated', { lines: this.getLineFeatures() });
            this.$dispatch('circlesUpdated', { circles: this.getCircleEntries() });
        },

        loadInitialState() {
            this.addPolygonFeatures(this.geojson.features || []);
            this.addLineFeatures(this.initialLines);
            this.addCircleEntries(this.initialCircles);

            if (this.drawnItems.getLayers().length > 0) {
                this.map.fitBounds(this.drawnItems.getBounds(), { padding: [40, 40] });
            }
        },

        addPolygonFeatures(features) {
            (features || []).forEach((feature) => {
                if (!feature || !feature.geometry) return;
                const group = L.geoJSON(feature, {
                    style: { color: '#2563eb', weight: 3, fillColor: '#2563eb', fillOpacity: 0.2 },
                });
                group.eachLayer((layer) => this.drawnItems.addLayer(layer));
            });
        },

        addLineFeatures(features) {
            (features || []).forEach((feature) => {
                if (!feature || !feature.geometry) return;
                const coords = feature.geometry.coordinates || [];
                const latlngs = coords.map((pair) => [pair[1], pair[0]]);
                if (latlngs.length < 2) return;
                this.drawnItems.addLayer(L.polyline(latlngs, { color: '#059669', weight: 3 }));
            });
        },

        addCircleEntries(entries) {
            (entries || []).forEach((entry) => {
                const coords = entry && entry.geometry ? entry.geometry.coordinates : null;
                const radius = entry ? Number(entry.radius || 0) : 0;
                if (!coords || !radius) return;
                this.drawnItems.addLayer(L.circle([coords[1], coords[0]], {
                    radius,
                    color: '#d97706',
                    weight: 3,
                    fillColor: '#d97706',
                    fillOpacity: 0.2,
                }));
            });
        },

        /**
         * Reemplaza TODA la medición por el GeoJSON dado (importación). Se
         * dibujan sólo los polígonos: líneas y círculos anteriores se descartan
         * porque la importación es un reemplazo completo.
         */
        loadGeojson(geojson) {
            this.geojson = geojson && geojson.features ? geojson : emptyCollection;
            this.drawnItems.clearLayers();
            this.addPolygonFeatures(this.geojson.features);

            if (this.drawnItems.getLayers().length > 0) {
                this.map.fitBounds(this.drawnItems.getBounds(), { padding: [40, 40] });
            }

            this.updateTotals();
            this.sync();
        },

        // ---------------------------------------------------------- totales

        updateTotals() {
            let area = 0;
            let perimeter = 0;
            let count = 0;

            this.eachShape((layer) => {
                if (this.isCircle(layer)) {
                    // El radio de Leaflet ya está en metros: π·r² son m² reales.
                    const radius = layer.getRadius();
                    area += Math.PI * radius * radius;
                    perimeter += 2 * Math.PI * radius;
                    count++;
                    return;
                }

                if (this.isLine(layer)) {
                    perimeter += this.latLngLength(layer.getLatLngs(), false);
                    count++;
                    return;
                }

                if (this.isPolygon(layer)) {
                    const ring = layer.getLatLngs()[0] || [];
                    area += this.ringArea(ring);
                    perimeter += this.latLngLength(ring, true);
                    count++;
                }
            });

            this.totalArea = Math.round(area * 100) / 100;
            this.totalPerimeter = Math.round(perimeter * 100) / 100;
            this.shapeCount = count;
            this.geojson = this.getGeojson();
        },

        earthRadiusMeters() { return 6378137; },

        latLngLength(ring, close) {
            const points = (ring || []).filter((p) => p && p.lat !== undefined);
            if (points.length < 2) return 0;

            let length = 0;
            const last = close ? points.length : points.length - 1;

            for (let i = 0; i < last; i++) {
                length += this.haversine(points[i], points[(i + 1) % points.length]);
            }

            return length;
        },

        /**
         * Área geodésica del anillo. El shoelace sobre grados devolvía grados²
         * (una parcela real daba 0,000001 "m²"), así que se proyectan los puntos
         * a un plano local en metros y ahí el shoelace sí da m².
         */
        ringArea(ring) {
            const points = (ring || []).filter((p) => p && p.lat !== undefined);
            if (points.length < 3) return 0;

            const R = this.earthRadiusMeters();
            const lat0 = points.reduce((sum, p) => sum + (p.lat * Math.PI / 180), 0) / points.length;
            const cosLat = Math.cos(lat0);

            let area = 0;
            for (let i = 0; i < points.length; i++) {
                const j = (i + 1) % points.length;
                const x1 = (points[i].lng * Math.PI / 180) * R * cosLat;
                const y1 = (points[i].lat * Math.PI / 180) * R;
                const x2 = (points[j].lng * Math.PI / 180) * R * cosLat;
                const y2 = (points[j].lat * Math.PI / 180) * R;
                area += x1 * y2 - x2 * y1;
            }

            return Math.abs(area) / 2;
        },

        haversine(a, b) {
            const R = 6371000;
            const dLat = (b.lat - a.lat) * Math.PI / 180;
            const dLng = (b.lng - a.lng) * Math.PI / 180;
            const s = Math.sin(dLat / 2) ** 2
                + Math.cos(a.lat * Math.PI / 180) * Math.cos(b.lat * Math.PI / 180) * Math.sin(dLng / 2) ** 2;

            return 2 * R * Math.asin(Math.min(1, Math.sqrt(s)));
        },

        formatArea(m2) {
            if (this.unit === 'ha') return (m2 / 10000).toFixed(4) + ' ha';
            if (this.unit === 'ft2') return (m2 * 10.764).toFixed(2) + ' ft²';
            return m2.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' m²';
        },

        formatLength(m) {
            if (this.unit === 'ft2') return (m * 3.281).toFixed(2) + ' ft';
            return m.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' m';
        },

        formatLatLng(lat, lng) {
            if (!lat || !lng) return '{{ __('mapscalculator.no_coordinates') }}';
            return `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
        },

        // --------------------------------------------------- deshacer / rehacer

        snapshot() {
            return {
                polygons: this.getPolygonFeatures(),
                lines: this.getLineFeatures(),
                circles: this.getCircleEntries(),
            };
        },

        pushHistory() {
            this.history.push(this.snapshot());
            if (this.history.length > 25) this.history.shift();
            this.redoStack = [];
        },

        restore(state) {
            if (!state) return;
            this.drawnItems.clearLayers();
            this.addPolygonFeatures(state.polygons || []);
            this.addLineFeatures(state.lines || []);
            this.addCircleEntries(state.circles || []);
            this.updateTotals();
            this.sync();
        },

        undo() {
            if (this.history.length === 0) return;
            this.redoStack.push(this.snapshot());
            this.restore(this.history.pop());
        },

        redo() {
            if (this.redoStack.length === 0) return;
            this.history.push(this.snapshot());
            this.restore(this.redoStack.pop());
        },

        // ------------------------------------------------------ buscar ubicación

        dropSearchMarker(lat, lng) {
            if (this.searchMarker) this.map.removeLayer(this.searchMarker);
            this.svPoint = L.latLng(lat, lng);
            this.jumpStreetView(this.svPoint);

            // Icono por defecto de Leaflet servido desde CDN (las rutas locales
            // de las imágenes no existen en esta instalación).
            delete L.Icon.Default.prototype._getIconUrl;
            L.Icon.Default.mergeOptions({
                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
            });

            this.searchMarker = L.marker([lat, lng])
                .addTo(this.map)
                .bindPopup(`Lat: ${Number(lat).toFixed(6)}<br>Lng: ${Number(lng).toFixed(6)}`)
                .openPopup();
        },

        searchCoordinates() {
            const lat = parseFloat(this.searchLat);
            const lng = parseFloat(this.searchLng);

            if (isNaN(lat) || isNaN(lng)) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.invalid_coordinates') }}', type: 'error' });
                return;
            }

            this.currentLat = lat;
            this.currentLng = lng;
            this.map.setView([lat, lng], 18);
            this.dropSearchMarker(lat, lng);
            this.$dispatch('notify', { message: '{{ __('mapscalculator.location_found') }}', type: 'success' });
        },

        /**
         * Geocodificación con Nominatim (OpenStreetMap): coherente con las
         * teselas OSM/Esri del mapa y sin necesidad de clave de API.
         */
        async searchAddress() {
            const query = (this.addressQuery || '').trim();

            if (query.length < 3) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.address_not_found') }}', type: 'error' });
                return;
            }

            this.isSearchingAddress = true;

            try {
                const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(query);
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                const results = await response.json();

                if (!Array.isArray(results) || results.length === 0) {
                    this.$dispatch('notify', { message: '{{ __('mapscalculator.address_not_found') }}', type: 'error' });
                    return;
                }

                const lat = parseFloat(results[0].lat);
                const lng = parseFloat(results[0].lon);
                this.searchLat = lat.toFixed(6);
                this.searchLng = lng.toFixed(6);
                this.currentLat = lat;
                this.currentLng = lng;
                this.map.setView([lat, lng], 18);
                this.dropSearchMarker(lat, lng);
                this.$dispatch('notify', { message: '{{ __('mapscalculator.location_found') }}', type: 'success' });
            } catch (error) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.address_error') }}', type: 'error' });
            } finally {
                this.isSearchingAddress = false;
            }
        },

        useCurrentLocation() {
            if (!navigator.geolocation) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.geolocation_not_supported') }}', type: 'error' });
                return;
            }

            this.isSearching = true;
            this.$dispatch('notify', { message: '{{ __('mapscalculator.getting_location') }}', type: 'info' });

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.isSearching = false;
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    this.searchLat = lat.toFixed(6);
                    this.searchLng = lng.toFixed(6);
                    this.currentLat = lat;
                    this.currentLng = lng;
                    this.map.setView([lat, lng], 18);
                    this.dropSearchMarker(lat, lng);
                    this.$dispatch('notify', { message: '{{ __('mapscalculator.location_found') }}', type: 'success' });
                },
                (error) => {
                    this.isSearching = false;
                    let message = '{{ __('mapscalculator.geolocation_error') }}';

                    if (error.code === error.PERMISSION_DENIED) {
                        message = '{{ __('mapscalculator.geolocation_denied') }}';
                    } else if (error.code === error.TIMEOUT) {
                        message = '{{ __('mapscalculator.geolocation_timeout') }}';
                    }

                    this.$dispatch('notify', { message, type: 'error' });
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        },

        // -------------------------------------------------- importar / exportar

        openImport() {
            this.importText = '';
            this.showImport = true;
        },

        readImportFile(event) {
            const file = event.target.files && event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => { this.importText = e.target.result; };
            reader.readAsText(file);
        },

        doImport() {
            if (!this.importText || this.importText.trim() === '') {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.import_invalid') }}', type: 'error' });
                return;
            }

            this.$wire.importGeoJSON(this.importText);
            this.showImport = false;
        },

        openExport() {
            this.showExport = true;
        },

        /** Ojo: antes era un getter llamado igual que el método `exportGeojson()`,
         *  así que la segunda definición pisaba a la primera y el botón de
         *  exportar lanzaba "exportGeojson is not a function". */
        get exportText() {
            return JSON.stringify({
                type: 'FeatureCollection',
                features: this.getPolygonFeatures().concat(this.getLineFeatures(), this.getCircleEntries().map((entry) => ({
                    type: 'Feature',
                    properties: Object.assign({}, entry.properties, { type: 'circle', radius: entry.radius }),
                    geometry: entry.geometry,
                }))),
            }, null, 2);
        },

        async copyGeojson() {
            try {
                await navigator.clipboard.writeText(this.exportText);
                this.$dispatch('notify', { message: '{{ __('mapscalculator.geojson_copied') }}', type: 'success' });
            } catch (error) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.geojson_copied') }}', type: 'error' });
            }
        },

        downloadGeojson() {
            const blob = new Blob([this.exportText], { type: 'application/geo+json' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `medicion-${this.label || 'exterior'}-${Date.now()}.geojson`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);
        },

        // ---------------------------------------------------------------- guardar

        /**
         * Manda el estado completo en la MISMA petición que guarda: así no
         * depende del orden de los eventos y el área guardada es exactamente la
         * que se está viendo.
         */
        saveMeasurement() {
            if (this.shapeCount === 0) return;

            const center = this.map.getCenter();
            const bounds = this.map.getBounds();

            this.$wire.save({
                label: this.label || '',
                unit: this.unit,
                geojson: this.getGeojson(),
                lines: this.getLineFeatures(),
                circles: this.getCircleEntries(),
                layer: this.mapLayer,
                center: [center.lat, center.lng],
                zoom: this.map.getZoom(),
                bounds: {
                    north: bounds.getNorth(),
                    south: bounds.getSouth(),
                    east: bounds.getEast(),
                    west: bounds.getWest(),
                },
            });
        },

        onSaved() {
            this.$dispatch('notify', { message: '{{ __('mapscalculator.measurement_saved') }}', type: 'success' });
        },
    };
}
</script>
</div>
