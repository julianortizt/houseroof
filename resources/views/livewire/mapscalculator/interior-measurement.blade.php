<div x-data="interiorMeasurement(@js([
        'imageUrl' => $referenceImageUrl,
        'scaleFactor' => (float) $scaleFactor,
        'calibrationLine' => $calibrationLine,
        'rooms' => $rooms,
        'label' => $label,
    ]))"
     x-init="boot()"
     {{-- Los nombres de evento van en kebab-case: los atributos del HTML se
          pasan a minúsculas, así que un listener en camelCase no oiría nunca el
          evento (Livewire lo dispara con el nombre literal). --}}
     @image-loaded.window="onImageLoaded($event.detail)"
     @reset-canvas.window="hardReset()"
     {{-- El editor ocupa todo el alto disponible de la ventana: es el hijo
          flexible de la página de medición. --}}
     class="w-full flex-1 flex flex-col min-h-0">

    {{-- Barra superior: totales en vivo + herramientas --}}
    <div class="mb-3 flex flex-wrap gap-2 shrink-0">
        <div class="bg-white rounded-lg shadow p-3 flex flex-wrap items-center gap-x-4 gap-y-2 flex-1 min-w-[280px]">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">{{ __('mapscalculator.area') }}:</span>
                <span class="text-lg font-bold text-primary-600" x-text="formattedArea"></span>
            </div>
            <div class="flex items-center gap-2 border-l pl-3">
                <span class="text-sm font-medium text-gray-700">{{ __('mapscalculator.perimeter') }}:</span>
                <span class="text-lg font-bold text-secondary-600" x-text="formattedPerimeter"></span>
            </div>
            <div class="flex items-center gap-2 border-l pl-3">
                <span class="text-sm font-medium text-gray-700">{{ __('mapscalculator.scale_text') }}:</span>
                <span class="text-lg font-bold" :class="scaleFactor ? 'text-gray-900' : 'text-red-500'"
                      x-text="scaleText"></span>
            </div>
            <div class="flex items-center gap-2 border-l pl-3">
                <span class="text-sm font-medium text-gray-700">{{ __('mapscalculator.total_rooms') }}:</span>
                <span class="text-lg font-bold" x-text="rooms.length"></span>
            </div>
            <div class="flex items-center gap-2 border-l pl-3">
                <span class="text-sm font-medium text-gray-700">{{ __('mapscalculator.points') }}:</span>
                <span class="text-lg font-bold" x-text="totalPoints"></span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-2 flex flex-wrap items-center gap-2">
            <button type="button" x-show="imageLoaded" @click="startCalibration()"
                    class="px-3 py-1.5 text-sm text-white rounded transition"
                    :class="isCalibrating ? 'bg-amber-700 ring-2 ring-amber-300' : 'bg-amber-600 hover:bg-amber-700'">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l3-1m0 0l3-1m-3 1l3 1m0 0l3-1m-3 1l3 1m0 0l3-1m-3 1l3 1"></path></svg>
                <span x-text="isCalibrating ? '{{ __('mapscalculator.calibrating') }}' : '{{ __('mapscalculator.calibrate') }}'"></span>
            </button>

            <button type="button" x-show="imageLoaded" @click="startDrawing()"
                    class="px-3 py-1.5 text-sm text-white rounded transition"
                    :class="mode === 'draw' ? 'bg-primary-700 ring-2 ring-primary-300' : 'bg-primary-600 hover:bg-primary-700'"
                    :disabled="!canDraw">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                {{ __('mapscalculator.draw') }}
            </button>

            <button type="button" x-show="imageLoaded" @click="toggleEdgeMode()"
                    class="px-3 py-1.5 text-sm rounded transition"
                    :class="edgeMode === 'curve' ? 'bg-secondary-600 text-white ring-2 ring-secondary-300' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'"
                    title="{{ __('mapscalculator.edge_mode') }}">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 17c3-6 6 6 9 0s6-6 9 0"></path></svg>
                <span x-text="edgeMode === 'curve' ? '{{ __('mapscalculator.edge_curve') }}' : '{{ __('mapscalculator.edge_straight') }}'"></span>
            </button>

            <button type="button" x-show="imageLoaded && mode === 'edit' && selectedRoom !== null" @click="toggleSelectedSmooth()"
                    class="px-3 py-1.5 text-sm bg-secondary-50 text-secondary-700 rounded hover:bg-secondary-100 transition"
                    title="{{ __('mapscalculator.selected_smooth_hint') }}">
                {{ __('mapscalculator.selected_smooth') }}
            </button>

            <button type="button" x-show="imageLoaded" @click="startEditing()"
                    class="px-3 py-1.5 text-sm rounded transition"
                    :class="mode === 'edit' ? 'bg-gray-300' : 'bg-gray-100 hover:bg-gray-200 text-gray-700'">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                {{ __('mapscalculator.edit') }}
            </button>

            <button type="button" x-show="imageLoaded" @click="closeCurrentRoom()"
                    class="px-3 py-1.5 text-sm bg-emerald-600 text-white rounded hover:bg-emerald-700 transition"
                    :disabled="draftPoints.length < 3">
                {{ __('mapscalculator.close_polygon') }}
            </button>

            <button type="button" x-show="imageLoaded" @click="undo()" :disabled="!canUndo"
                    class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition disabled:opacity-40">
                {{ __('mapscalculator.undo') }}
            </button>

            <button type="button" x-show="imageLoaded" @click="redo()" :disabled="!canRedo"
                    class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition disabled:opacity-40">
                {{ __('mapscalculator.redo') }}
            </button>

            <button type="button" x-show="imageLoaded" @click="replaceImage()"
                    class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition">
                {{ __('mapscalculator.reference_image') }}
            </button>
        </div>
    </div>

    {{-- Aviso de modo activo --}}
    <template x-if="imageLoaded && isCalibrating">
        <div class="mb-3 shrink-0 bg-amber-100 border border-amber-400 text-amber-800 px-4 py-2 rounded-lg text-sm">
            {{ __('mapscalculator.calibration_hint') }}
        </div>
    </template>
        <template x-if="imageLoaded && mode === 'draw'">
        <div class="mb-3 shrink-0 bg-blue-100 border border-blue-400 text-blue-800 px-4 py-2 rounded-lg text-sm">
            {{ __('mapscalculator.drawing_hint') }}
            <span x-show="edgeMode === 'curve'" class="ml-2 font-medium">{{ __('mapscalculator.edge_curve') }} ✓</span>
        </div>
    </template>
    <template x-if="imageLoaded && mode !== 'draw' && !isCalibrating">
        <div class="mb-3 shrink-0 bg-gray-50 border border-gray-200 text-gray-600 px-4 py-2 rounded-lg text-sm">
            {{ __('mapscalculator.select_room_hint') }}
        </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 flex-1 min-h-0">
        {{-- Lienzo --}}
        <div class="lg:col-span-4 flex flex-col min-h-0">
            {{-- wire:ignore: el bitmap del canvas lo gestiona Alpine; si Livewire
                 vuelve a pintar este nodo, borra el dibujo del usuario. --}}
            {{-- El input va fuera del bloque wire:ignore para que Livewire siga
                 gestionando la subida y los errores de validación. --}}
            <input type="file" x-ref="fileInput" wire:model="referenceImage" accept="image/*" class="hidden">
            @error('referenceImage')
                <p class="mb-2 p-2 text-sm text-red-600 bg-red-50 rounded-lg">{{ $message }}</p>
            @enderror
            <p class="mb-2 text-sm text-gray-500" wire:loading wire:target="referenceImage">{{ __('mapscalculator.processing') }}</p>

            {{-- El lienzo ocupa el alto disponible de la ventana y se puede
                 ampliar/reducir con zoom (antes quedaba reducido al ancho). --}}
            <div class="mb-2 shrink-0 flex items-center justify-between gap-2">
                <div class="flex items-center gap-1 bg-white rounded-lg shadow-sm border px-2 py-1 flex-wrap">
                    <button type="button" @click="zoomOut()" class="px-3 py-1 text-base font-bold text-gray-700 hover:bg-gray-100 rounded" title="{{ __('mapscalculator.zoom_out') }}">−</button>
                    <input type="range" class="w-24 sm:w-40 accent-primary-600 cursor-pointer" aria-label="Zoom"
                           :min="zoomMin" :max="zoomMax" step="0.05" :value="zoom"
                           @input="setZoom(parseFloat($event.target.value))">
                    <button type="button" @click="zoomIn()" class="px-3 py-1 text-base font-bold text-gray-700 hover:bg-gray-100 rounded" title="{{ __('mapscalculator.zoom_in') }}">+</button>
                    <span class="text-xs font-medium text-gray-600 w-12 text-center" x-text="Math.round(zoom * 100) + '%'"></span>
                    <div class="w-px h-4 bg-gray-200 mx-1"></div>
                    <button type="button" @click="setZoom(1)" class="px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100 rounded">100%</button>
                    <button type="button" @click="fitZoom()" class="px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-100 rounded">{{ __('mapscalculator.fit_image') }}</button>
                </div>
                <span class="text-xs text-gray-400 hidden sm:inline">{{ __('mapscalculator.close_polygon_hint') }}</span>
            </div>

            {{-- El lienzo se queda con todo el alto restante (antes eran 480 px
                 fijos y el área de trabajo quedaba minúscula). --}}
            <div x-ref="canvasWrap" class="relative bg-gray-100 rounded-lg overflow-auto border flex-1 min-h-[320px] lg:min-h-[420px]" wire:ignore>
                <canvas x-ref="canvas"
                        x-show="imageLoaded"
                        @click="onCanvasClick($event)"
                        @dblclick="onCanvasDoubleClick($event)"
                        @mousedown="onMouseDown($event)"
                        @mousemove="onMouseMove($event)"
                        @mouseup="onMouseUp($event)"
                        @mouseleave="onMouseUp($event)"
                        @wheel.prevent="onWheel($event)"
                        @touchstart="onTouchStart($event)"
                        @touchmove="onTouchMove($event)"
                        @touchend="onTouchEnd($event)"
                        @touchcancel="onTouchEnd($event)"
                        :style="'width: ' + zoomedWidth + 'px; max-width: none; height: auto;'"
                        class="block cursor-crosshair select-none"></canvas>

                <div x-show="!imageLoaded" class="w-full h-full min-h-[440px] flex flex-col items-center justify-center text-gray-500 p-8">
                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p class="text-lg font-medium">{{ __('mapscalculator.drag_drop_image') }}</p>
                    <p class="text-sm">{{ __('mapscalculator.image_hint') }}</p>
                    <button type="button" @click="replaceImage()" class="mt-4 px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">{{ __('mapscalculator.upload_button') }}</button>
                </div>
            </div>

            <div class="mt-3 shrink-0 flex flex-wrap gap-2">
                <button type="button" @click="resetMeasurement()" :disabled="!imageLoaded"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition disabled:opacity-40">
                    {{ __('mapscalculator.reset_measurement') }}
                </button>
                <button type="button" @click="exportData()" :disabled="rooms.length === 0"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition disabled:opacity-40">
                    {{ __('mapscalculator.export_data') }}
                </button>
            </div>
        </div>

        {{-- Panel lateral: ambientes + guardar --}}
        <div class="space-y-4 min-h-0 lg:overflow-y-auto lg:pr-1">
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-medium text-gray-900 mb-3">{{ __('mapscalculator.rooms') }}</h3>

                <template x-if="rooms.length === 0">
                    <p class="text-sm text-gray-500">{{ __('mapscalculator.no_rooms_yet') }}</p>
                </template>

                <ul class="space-y-2">
                    <template x-for="(room, index) in rooms" :key="room.uid">
                        <li class="border rounded-lg p-2 cursor-pointer"
                            :class="selectedRoom === index ? 'border-primary-500 bg-primary-50' : 'border-gray-200'"
                            @click="selectedRoom = index; mode = mode === 'draw' ? mode : 'edit'; render()">
                            <div class="flex items-center gap-2">
                                <input type="text" x-model="room.name"
                                       @click.stop
                                       class="flex-1 px-2 py-1 text-sm border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-primary-500"
                                       placeholder="{{ __('mapscalculator.room_name_placeholder') }}">
                                <button type="button" x-show="!!room.smooth" @click.stop="selectedRoom = index; toggleSelectedSmooth()"
                                        title="{{ __('mapscalculator.selected_smooth_hint') }}"
                                        class="px-1.5 py-0.5 text-[10px] font-medium bg-secondary-100 text-secondary-700 rounded">
                                    {{ __('mapscalculator.edge_curve') }}
                                </button>
                                <button type="button" @click.stop="removeRoom(index)" title="{{ __('mapscalculator.delete_room') }}"
                                        class="text-red-500 hover:text-red-700 p-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v12m-6 0h12a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12"></path></svg>
                                </button>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span x-text="room.points.length + ' {{ __('mapscalculator.points') }}'"></span>
                                <span x-text="formatArea(roomArea(room))"></span>
                            </div>
                        </li>
                    </template>
                </ul>

                <template x-if="draftPoints.length > 0">
                    <div class="mt-3 p-2 rounded-lg border border-dashed border-primary-300 bg-primary-50/50 text-sm">
                        <span class="font-medium text-primary-700">{{ __('mapscalculator.room') }}…</span>
                        <span class="text-gray-600" x-text="draftPoints.length + ' {{ __('mapscalculator.points') }}'"></span>
                    </div>
                </template>
            </div>

            <div class="bg-white rounded-lg shadow p-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.label') }}</label>
                    <input type="text" x-model="label"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="{{ __('mapscalculator.label_placeholder') }}">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ __('mapscalculator.units') }}</label>
                    <select x-model="unit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="m2">{{ __('mapscalculator.square_meters') }}</option>
                        <option value="ft2">{{ __('mapscalculator.square_feet') }}</option>
                        <option value="ha">{{ __('mapscalculator.hectares') }}</option>
                    </select>
                </div>
                <button type="button" @click="saveMeasurement()" :disabled="saving || !canSave"
                        class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition font-medium disabled:opacity-50"
                        wire:loading.attr="disabled" wire:target="saveFromEditor">
                    <span x-show="!saving">{{ __('mapscalculator.save_measurement') }}</span>
                    <span x-show="saving">{{ __('mapscalculator.processing') }}</span>
                </button>

                <ul class="text-xs text-gray-500 space-y-1">
                    <li>
                        <span :class="referenceImageUrl ? 'text-green-600' : 'text-red-500'">●</span>
                        {{ __('mapscalculator.reference_image') }}
                    </li>
                    <li>
                        <span :class="scaleFactor ? 'text-green-600' : 'text-red-500'">●</span>
                        {{ __('mapscalculator.scale_text') }}
                    </li>
                    <li>
                        <span :class="rooms.length ? 'text-green-600' : 'text-red-500'">●</span>
                        {{ __('mapscalculator.rooms') }}
                    </li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Modal de calibración --}}
    <template x-if="showCalibrationInput">
        <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="cancelCalibration()">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold mb-4">{{ __('mapscalculator.calibration_real_distance') }}</h3>
                <p class="text-gray-600 mb-4 text-sm">
                    {{ __('mapscalculator.calibration_pixel_distance') }}:
                    <strong x-text="calibrationPixelDistance.toFixed(2)"></strong> px
                </p>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('mapscalculator.real_distance_meters') }}</label>
                <input type="number" step="0.01" min="0.01" x-model.number="calibrationRealMeters"
                       @keydown.enter="confirmCalibration()"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 mb-4"
                       placeholder="{{ __('mapscalculator.real_distance_placeholder') }}">
                <div class="flex gap-2 justify-end">
                    <button type="button" @click="cancelCalibration()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">{{ __('mapscalculator.cancel_calibration_btn') }}</button>
                    <button type="button" @click="confirmCalibration()" :disabled="!calibrationRealMeters" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition disabled:opacity-50">{{ __('mapscalculator.confirm_calibration') }}</button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
/**
 * Editor de medición interior sobre plano/foto.
 *
 * El componente nunca existió: la vista hacía x-data="interiorMeasurement()"
 * pero no había ningún script que definiera la función, así que Alpine fallaba
 * al inicializar y el módulo de interiores no se podía usar en absoluto
 * (ni subir imagen, ni calibrar, ni dibujar, ni guardar).
 */
function interiorMeasurement(initial = {}) {
    return {
        initial: initial || {},

        canvas: null,
        ctx: null,
        img: null,
        imageLoaded: false,
        referenceImageUrl: initial.imageUrl || '',
        loadedImageUrl: '',

        scaleFactor: Number(initial.scaleFactor) || 0, // metros por píxel
        calibrationLine: initial.calibrationLine || null,

        mode: 'view',            // view | draw | edit
        isCalibrating: false,
        calibrationStart: null,
        calibrationEnd: null,
        calibrationPixelDistance: 0,
        calibrationRealMeters: 0,
        showCalibrationInput: false,
        edgeMode: 'straight',    // straight | curve (trazo al cerrar ambiente)
        imgW: 0,
        imgH: 0,

        rooms: [],
        draftPoints: [],
        selectedRoom: null,
        dragVertex: null,
        mouse: null,
        pinchStart: null,
        zoom: 1,
        zoomMin: 0.1,        // 10 %
        zoomMax: 8,          // 800 %: permite medir punto a punto sobre la foto
        unit: 'm2',
        label: initial.label || '',
        saving: false,

        history: [],
        redoStack: [],
        _uid: 0,

        get canDraw() {
            return this.scaleFactor > 0;
        },
        get canSave() {
            return this.imageLoaded && this.scaleFactor > 0 && this.rooms.length > 0;
        },
        get canUndo() {
            return this.history.length > 0;
        },
        get canRedo() {
            return this.redoStack.length > 0;
        },
        get totalPoints() {
            const closed = this.rooms.reduce((sum, r) => sum + r.points.length, 0);
            return closed + this.draftPoints.length;
        },
        get scaleText() {
            if (!this.scaleFactor) return '—';
            return '1 px = ' + this.scaleFactor.toFixed(4) + ' m';
        },
        get zoomedWidth() {
            if (!this.imgW) return 0;
            return Math.max(1, Math.round(this.imgW * this.zoom));
        },

        /** Área total (cerrados + borrador en curso) en m². */
        get rawArea() {
            let total = 0;
            this.rooms.forEach((room) => { total += this.roomArea(room); });
            if (this.draftPoints.length >= 3) total += this.roomAreaM2(this.draftPoints);
            return total;
        },
        get rawPerimeter() {
            let total = 0;
            this.rooms.forEach((room) => { total += this.roomPerimeter(room); });
            if (this.draftPoints.length >= 2) total += this.roomPerimeterM(this.draftPoints);
            return total;
        },
        get formattedArea() {
            return this.formatArea(this.rawArea);
        },
        get formattedPerimeter() {
            return this.formatLength(this.rawPerimeter);
        },

        // ---------------------------------------------------------------- init

        boot() {
            this.rooms = (this.initial.rooms || []).map((room) => ({
                uid: 'r' + (++this._uid),
                name: room.name || '',
                points: (room.points || []).map((p) => ({ x: Number(p.x), y: Number(p.y) })),
                smooth: !!room.smooth,
            }));

            if (this.initial.imageUrl) {
                this.loadImage(this.initial.imageUrl);
            }

            if (this.calibrationLine) {
                this.calibrationStart = { x: Number(this.calibrationLine.x1), y: Number(this.calibrationLine.y1) };
                this.calibrationEnd = { x: Number(this.calibrationLine.x2), y: Number(this.calibrationLine.y2) };
            }

            this.$nextTick(() => this.setupCanvas());
        },

        setupCanvas() {
            this.canvas = this.$refs.canvas;
            if (!this.canvas) return;
            this.ctx = this.canvas.getContext('2d');
            this.render();
        },

        loadImage(url) {
            const image = new Image();
            image.crossOrigin = 'anonymous';
            image.onload = () => {
                this.img = image;

                // Resolución lógica de la geometría: hasta 4096 px de lado
                // (antes 2400, que dejaba el plano borroso al hacer zoom).
                const maxSide = 4096;
                const ratio = Math.min(1, maxSide / Math.max(image.naturalWidth, image.naturalHeight));
                this.imgW = Math.max(1, Math.round(image.naturalWidth * ratio));
                this.imgH = Math.max(1, Math.round(image.naturalHeight * ratio));

                this.$nextTick(() => {
                    this.setupCanvas();
                    if (!this.canvas) return;
                    this.syncCanvasRaster();
                    this.imageLoaded = true;
                    this.referenceImageUrl = url;
                    this.loadedImageUrl = url;
                    this.fitZoom();
                    this.render();
                });
            };
            image.onerror = () => {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.error_image_required') }}', type: 'error' });
            };
            image.src = url;
        },

        /**
         * Raster del canvas = tamaño CSS × devicePixelRatio (tope 4096).
         * La geometría vive en espacio de imagen (imgW/imgH); el canvas sólo
         * cambia de resolución para que el zoom no borre el bitmap.
         */
        syncCanvasRaster() {
            if (!this.canvas || !this.imgW || !this.imgH) return;

            const cssW = Math.max(1, Math.round(this.imgW * this.zoom));
            const dpr = Math.min(2, window.devicePixelRatio || 1);
            let rw = Math.round(cssW * dpr);
            let rh = Math.round((this.imgH / this.imgW) * rw);

            const maxRaster = 4096;
            const over = Math.max(rw, rh);
            if (over > maxRaster) {
                const s = maxRaster / over;
                rw = Math.max(1, Math.round(rw * s));
                rh = Math.max(1, Math.round(rh * s));
            }

            if (this.canvas.width !== rw || this.canvas.height !== rh) {
                this.canvas.width = rw;
                this.canvas.height = rh;
                this.ctx = this.canvas.getContext('2d');
            }

            this.canvas.style.width = cssW + 'px';
        },

        /** Evento del servidor tras subir una imagen nueva. */
        onImageLoaded(detail) {
            const url = detail && detail.path ? detail.path : null;
            if (!url) return;

            this.imageLoaded = false;
            this.rooms = [];
            this.draftPoints = [];
            this.selectedRoom = null;
            this.history = [];
            this.redoStack = [];
            this.scaleFactor = 0;
            this.calibrationLine = null;
            this.calibrationStart = null;
            this.calibrationEnd = null;
            this.isCalibrating = true;
            this.mode = 'view';
            this.loadImage(url);
        },

        hardReset() {
            this.rooms = [];
            this.draftPoints = [];
            this.selectedRoom = null;
            this.history = [];
            this.redoStack = [];
            this.scaleFactor = 0;
            this.calibrationLine = null;
            this.calibrationStart = null;
            this.calibrationEnd = null;
            this.isCalibrating = false;
            this.imageLoaded = false;
            this.img = null;
            this.imgW = 0;
            this.imgH = 0;
            this.referenceImageUrl = '';
            this.label = '';
            this.render();
        },

        replaceImage() {
            if (this.$refs.fileInput) this.$refs.fileInput.click();
        },

        zoomIn() {
            this.setZoom(this.zoom + 0.25);
        },

        zoomOut() {
            this.setZoom(this.zoom - 0.25);
        },

        /**
         * Cambia el nivel de zoom (1 = 100 %) respetando `zoomMin`/`zoomMax`.
         * Si se pasa el ancla (`atX`/`atY`, coordenadas del contenido dentro
         * del contenedor con scroll), el punto de la imagen que había bajo el
         * cursor se queda exactamente donde estaba.
         */
        setZoom(next, atX, atY) {
            let clamped = Math.round(Number(next) * 100) / 100;
            if (!Number.isFinite(clamped)) return;
            clamped = Math.min(this.zoomMax, Math.max(this.zoomMin, clamped));

            const wrap = this.$refs.canvasWrap;
            if (!wrap || !this.canvas) {
                this.zoom = clamped;
                return;
            }

            if (clamped === this.zoom) return;

            // El ancla se guarda en coordenadas de IMAGEN + offset visible (y
            // no en las de contenido): aunque lleguen varios eventos antes del
            // repintado, el cálculo final usa el zoom vigente y no se acumula
            // error. Sin ancla (botones o deslizador) el punto fijo es el
            // centro del área visible.
            const viewX = atX === undefined ? wrap.clientWidth / 2 : atX - wrap.scrollLeft;
            const viewY = atY === undefined ? wrap.clientHeight / 2 : atY - wrap.scrollTop;
            const imgX = (wrap.scrollLeft + viewX) / this.zoom;
            const imgY = (wrap.scrollTop + viewY) / this.zoom;

            this.zoom = clamped;

            // El ancho del lienzo se aplica YA en este mismo turno (el `:style`
            // de Alpine escribirá exactamente el mismo valor), de modo que el
            // scroll se recoloca aquí mismo sin esperar a ningún tick: escribir
            // `scrollLeft` fuerza el reflow con el nuevo ancho.
            this.syncCanvasRaster();
            wrap.scrollLeft = Math.round(imgX * clamped - viewX);
            wrap.scrollTop = Math.round(imgY * clamped - viewY);
            this.render();
        },

        /** Zoom con ancla: el punto de la imagen bajo el cursor se queda fijo. */
        zoomToCursor(event, factor) {
            const wrap = this.$refs.canvasWrap;
            if (!wrap) return;

            const rect = wrap.getBoundingClientRect();
            this.setZoom(
                this.zoom * factor,
                event.clientX - rect.left + wrap.scrollLeft,
                event.clientY - rect.top + wrap.scrollTop
            );
        },

        /** Rueda del ratón: zoom suave y continuo hacia el cursor. */
        onWheel(event) {
            if (!this.imageLoaded) return;

            // deltaY viene en píxeles (o en líneas si deltaMode es 1):
            // el factor es exponencial y acotado para que nunca dispare el zoom.
            const delta = event.deltaY * (event.deltaMode === 1 ? 16 : 1);
            const factor = Math.min(2, Math.max(0.5, Math.pow(1.0015, -delta)));
            this.zoomToCursor(event, factor);
        },

        // ------------------------------------------------------- gestos táctiles

        onTouchStart(event) {
            if (event.touches.length === 2) this.pinchStart = this.pinchState(event);
        },

        onTouchMove(event) {
            if (event.touches.length < 2 || !this.pinchStart) return;
            // Sin preventDefault el navegador haría zoom a la página.
            event.preventDefault();

            const state = this.pinchState(event);
            if (!state) return;

            // El zoom se calcula desde el inicio del gesto (no se acumula error
            // en cada evento) y el ancla es el punto medio actual.
            this.setZoom(this.pinchStart.zoom * (state.distance / this.pinchStart.distance), state.x, state.y);
        },

        onTouchEnd(event) {
            if (event.touches.length < 2) this.pinchStart = null;
        },

        pinchState(event) {
            const wrap = this.$refs.canvasWrap;
            if (!wrap || event.touches.length < 2) return null;

            const rect = wrap.getBoundingClientRect();
            const a = event.touches[0];
            const b = event.touches[1];

            return {
                zoom: this.zoom,
                distance: Math.hypot(b.clientX - a.clientX, b.clientY - a.clientY) || 1,
                x: (a.clientX + b.clientX) / 2 - rect.left + wrap.scrollLeft,
                y: (a.clientY + b.clientY) / 2 - rect.top + wrap.scrollTop,
            };
        },

        /** Encaja la imagen en el alto/ancho disponible del área de trabajo. */
        fitZoom() {
            const wrap = this.$refs.canvasWrap;
            if (!wrap || !this.imgW || !this.imgH) {
                this.zoom = 1;
                return;
            }

            const rect = wrap.getBoundingClientRect();
            const fit = Math.min(rect.width / this.imgW, rect.height / this.imgH);
            // Antes el ajuste estaba topeado al 100 %: una imagen pequeña
            // quedaba minúscula. Ahora puede ampliarse hasta zoomMax.
            this.setZoom(fit);
        },

        // ----------------------------------------------------------- geometría

        /** Evento → punto en espacio de imagen (imgW/imgH), no del raster. */
        pointFromEvent(event) {
            const rect = this.canvas.getBoundingClientRect();
            const scaleX = this.imgW / Math.max(1, rect.width);
            const scaleY = this.imgH / Math.max(1, rect.height);

            return {
                x: (event.clientX - rect.left) * scaleX,
                y: (event.clientY - rect.top) * scaleY,
            };
        },

        /** Unidades de imagen por píxel CSS (para trazos de tamaño fijo en pantalla). */
        get uiScale() {
            return 1 / Math.max(0.05, this.zoom);
        },

        polygonAreaPx(points) {
            if (points.length < 3) return 0;
            let area = 0;
            for (let i = 0; i < points.length; i++) {
                const j = (i + 1) % points.length;
                area += points[i].x * points[j].y - points[j].x * points[i].y;
            }
            return Math.abs(area) / 2;
        },

        polylineLengthPx(points) {
            let length = 0;
            for (let i = 0; i < points.length - 1; i++) {
                length += Math.hypot(points[i + 1].x - points[i].x, points[i + 1].y - points[i].y);
            }
            if (points.length > 2) {
                length += Math.hypot(points[0].x - points[points.length - 1].x, points[0].y - points[points.length - 1].y);
            }
            return length;
        },

        roomAreaM2(points) {
            return this.polygonAreaPx(points) * Math.pow(this.scaleFactor, 2);
        },

        roomPerimeterM(points) {
            return this.polylineLengthPx(points) * this.scaleFactor;
        },

        /**
         * Catmull-Rom cerrado (misma fórmula que el simulador) para
         * polígonos con `smooth: true`.
         */
        getSmoothedPath(pts, closed = true, tension = 0.3) {
            if (!pts || pts.length < 3) return pts ? pts.slice() : [];
            const out = [];
            const n = pts.length;
            const t = tension;
            const steps = closed ? 16 : 12;

            for (let i = 0; i < (closed ? n : n - 1); i++) {
                const p0 = closed ? pts[(i - 1 + n) % n] : pts[Math.max(0, i - 1)];
                const p1 = pts[i];
                const p2 = closed ? pts[(i + 1) % n] : pts[Math.min(n - 1, i + 1)];
                const p3 = closed ? pts[(i + 2) % n] : pts[Math.min(n - 1, i + 2)];

                for (let s = 0; s < steps; s++) {
                    const st = s / steps;
                    const st2 = st * st;
                    const st3 = st2 * st;
                    out.push({
                        x: (2 * st3 - 3 * st2 + 1) * p1.x
                            + (-2 * st3 + 3 * st2) * p2.x
                            + (st3 - 2 * st2 + st) * t * (p2.x - p0.x)
                            + (-st3 + st2) * t * (p3.x - p1.x),
                        y: (2 * st3 - 3 * st2 + 1) * p1.y
                            + (-2 * st3 + 3 * st2) * p2.y
                            + (st3 - 2 * st2 + st) * t * (p2.y - p0.y)
                            + (-st3 + st2) * t * (p3.y - p1.y),
                    });
                }
            }

            return out;
        },

        /** Puntos a usar para medir/dibujar (densificados si es curva). */
        effectivePoints(roomOrPoints) {
            const room = Array.isArray(roomOrPoints) ? { points: roomOrPoints } : roomOrPoints;
            if (!room) return [];
            return room.smooth ? this.getSmoothedPath(room.points, true) : room.points;
        },

        roomArea(room) {
            return this.roomAreaM2(this.effectivePoints(room));
        },

        roomPerimeter(room) {
            return this.roomPerimeterM(this.effectivePoints(room));
        },

        formatArea(m2) {
            if (this.unit === 'ha') return (m2 / 10000).toFixed(4) + ' ha';
            if (this.unit === 'ft2') return (m2 * 10.7639).toFixed(2) + ' ft²';
            return m2.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' m²';
        },

        formatLength(m) {
            if (this.unit === 'ft2') return (m * 3.28084).toFixed(2) + ' ft';
            return m.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' m';
        },

        centroid(points) {
            const sum = points.reduce((acc, p) => ({ x: acc.x + p.x, y: acc.y + p.y }), { x: 0, y: 0 });
            return { x: sum.x / points.length, y: sum.y / points.length };
        },

        // --------------------------------------------------------- interacción

        startCalibration() {
            this.isCalibrating = true;
            this.mode = 'view';
            this.calibrationStart = null;
            this.calibrationEnd = null;
            this.calibrationPixelDistance = 0;
            this.render();
        },

        cancelCalibration() {
            this.isCalibrating = false;
            this.showCalibrationInput = false;
            this.calibrationStart = null;
            this.calibrationEnd = null;
            this.render();
        },

        confirmCalibration() {
            const meters = Number(this.calibrationRealMeters);

            if (!meters || meters <= 0 || this.calibrationPixelDistance <= 0) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.error_calibration_required') }}', type: 'error' });
                return;
            }

            this.scaleFactor = meters / this.calibrationPixelDistance;
            this.calibrationLine = {
                x1: this.calibrationStart.x,
                y1: this.calibrationStart.y,
                x2: this.calibrationEnd.x,
                y2: this.calibrationEnd.y,
                pixelDistance: this.calibrationPixelDistance,
                realMeters: meters,
            };
            this.showCalibrationInput = false;
            this.isCalibrating = false;
            this.mode = 'view';
            this.render();
            this.$dispatch('notify', {
                message: '{{ __('mapscalculator.scale_text') }}: 1 px = ' + this.scaleFactor.toFixed(4) + ' m',
                type: 'success',
            });
        },

        startDrawing() {
            if (!this.canDraw) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.error_calibration_required') }}', type: 'error' });
                return;
            }
            this.isCalibrating = false;
            this.mode = this.mode === 'draw' ? 'view' : 'draw';
            this.render();
        },

        toggleEdgeMode() {
            this.edgeMode = this.edgeMode === 'curve' ? 'straight' : 'curve';
            this.render();
        },

        toggleSelectedSmooth() {
            if (this.selectedRoom === null || !this.rooms[this.selectedRoom]) return;
            this.pushHistory();
            const room = this.rooms[this.selectedRoom];
            room.smooth = !room.smooth;
            this.render();
        },

        startEditing() {
            this.isCalibrating = false;
            this.mode = this.mode === 'edit' ? 'view' : 'edit';
            if (this.mode === 'edit' && this.selectedRoom === null && this.rooms.length) {
                this.selectedRoom = 0;
            }
            this.render();
        },

        closeCurrentRoom() {
            if (this.draftPoints.length < 3) return;
            this.pushHistory();
            this.rooms.push({
                uid: 'r' + (++this._uid),
                name: '',
                points: this.draftPoints.map((p) => ({ x: p.x, y: p.y })),
                smooth: this.edgeMode === 'curve',
            });
            this.draftPoints = [];
            this.selectedRoom = this.rooms.length - 1;
            this.render();
        },

        removeRoom(index) {
            this.pushHistory();
            this.rooms.splice(index, 1);
            if (this.selectedRoom === index) this.selectedRoom = null;
            if (this.selectedRoom !== null && this.selectedRoom > index) this.selectedRoom--;
            this.render();
        },

        onCanvasClick(event) {
            if (!this.imageLoaded) return;
            const point = this.pointFromEvent(event);

            if (this.isCalibrating) {
                if (!this.calibrationStart || (this.calibrationStart && this.calibrationEnd)) {
                    this.calibrationStart = point;
                    this.calibrationEnd = null;
                } else {
                    this.calibrationEnd = point;
                    this.calibrationPixelDistance = Math.hypot(
                        this.calibrationEnd.x - this.calibrationStart.x,
                        this.calibrationEnd.y - this.calibrationStart.y
                    );
                    this.calibrationRealMeters = 0;
                    this.showCalibrationInput = true;
                }
                this.render();
                return;
            }

            if (this.mode === 'draw') {
                this.draftPoints.push(point);
                this.render();
                return;
            }

            // Vista / edición: selección por clic (hit-test sobre curvas o rectas).
            this.selectRoomAt(point);
        },

        pointInPolygon(point, pts) {
            let inside = false;
            for (let i = 0, j = pts.length - 1; i < pts.length; j = i++) {
                const xi = pts[i].x;
                const yi = pts[i].y;
                const xj = pts[j].x;
                const yj = pts[j].y;
                const intersect = ((yi > point.y) !== (yj > point.y))
                    && (point.x < ((xj - xi) * (point.y - yi)) / ((yj - yi) || 1e-12) + xi);
                if (intersect) inside = !inside;
            }
            return inside;
        },

        distToSegment(p, a, b) {
            const dx = b.x - a.x;
            const dy = b.y - a.y;
            const len2 = dx * dx + dy * dy;
            if (len2 < 1e-12) return Math.hypot(p.x - a.x, p.y - a.y);
            let t = ((p.x - a.x) * dx + (p.y - a.y) * dy) / len2;
            t = Math.max(0, Math.min(1, t));
            return Math.hypot(p.x - (a.x + t * dx), p.y - (a.y + t * dy));
        },

        /** Devuelve el índice del ambiente bajo el punto (borde o interior). */
        hitTestRoom(point) {
            const tol = 12 * this.uiScale;
            for (let i = this.rooms.length - 1; i >= 0; i--) {
                const pts = this.effectivePoints(this.rooms[i]);
                if (pts.length < 3) continue;

                for (let k = 0; k < pts.length; k++) {
                    const a = pts[k];
                    const b = pts[(k + 1) % pts.length];
                    if (this.distToSegment(point, a, b) <= tol) return i;
                }

                if (this.pointInPolygon(point, pts)) return i;
            }
            return null;
        },

        selectRoomAt(point) {
            if (this.isCalibrating) return;
            const index = this.hitTestRoom(point);
            if (index !== null) {
                this.selectedRoom = index;
            } else if (this.mode !== 'edit') {
                this.selectedRoom = null;
            }
            this.render();
        },

        onCanvasDoubleClick(event) {
            if (!this.imageLoaded) return;

            // Fuera del modo dibujo, el doble clic amplía hacia el cursor.
            if (this.mode !== 'draw') {
                if (this.mode === 'view' && !this.isCalibrating) this.zoomToCursor(event, 1.75);
                return;
            }

            event.preventDefault();

            // El doble clic dispara dos 'click' antes: se descarta el duplicado.
            if (this.draftPoints.length > 1) {
                const last = this.draftPoints[this.draftPoints.length - 1];
                const prev = this.draftPoints[this.draftPoints.length - 2];
                if (Math.hypot(last.x - prev.x, last.y - prev.y) < 3) {
                    this.draftPoints.pop();
                }
            }

            if (this.draftPoints.length >= 3) this.closeCurrentRoom();
            this.render();
        },

        onMouseDown(event) {
            if (!this.imageLoaded || this.mode !== 'edit') return;

            const point = this.pointFromEvent(event);

            // 1) ¿Vértice del ambiente ya seleccionado?
            let index = this.selectedRoom;
            let best = this.findVertexNear(index, point);

            // 2) Si no, ¿otro ambiente bajo el cursor? (selecciona y engancha).
            if (best === null) {
                const hit = this.hitTestRoom(point);
                if (hit !== null && hit !== this.selectedRoom) {
                    this.selectedRoom = hit;
                    index = hit;
                    best = this.findVertexNear(index, point);
                    this.render();
                }
            }

            if (best !== null && index !== null) {
                this.pushHistory();
                this.dragVertex = { room: index, vertex: best };
                event.preventDefault();
            }
        },

        findVertexNear(index, point) {
            if (index === null || !this.rooms[index]) return null;
            const room = this.rooms[index];
            const tol = 12 * this.uiScale;
            let best = null;
            let bestDistance = tol;

            room.points.forEach((vertex, i) => {
                const distance = Math.hypot(vertex.x - point.x, vertex.y - point.y);
                if (distance < bestDistance) {
                    bestDistance = distance;
                    best = i;
                }
            });

            return best;
        },

        onMouseMove(event) {
            if (!this.imageLoaded) return;
            this.mouse = this.pointFromEvent(event);

            if (this.dragVertex) {
                const room = this.rooms[this.dragVertex.room];
                if (room) {
                    room.points[this.dragVertex.vertex] = this.mouse;
                }
            }

            if (this.mode === 'draw' || this.dragVertex || this.isCalibrating) this.render();
        },

        onMouseUp() {
            if (this.dragVertex) {
                this.dragVertex = null;
                this.render();
            }
        },

        // ------------------------------------------------------- deshacer/rehacer

        snapshot() {
            return {
                rooms: this.rooms.map((r) => ({ uid: r.uid, name: r.name, smooth: !!r.smooth, points: r.points.map((p) => ({ ...p })) })),
                draftPoints: this.draftPoints.map((p) => ({ ...p })),
            };
        },

        pushHistory() {
            this.history.push(this.snapshot());
            if (this.history.length > 30) this.history.shift();
            this.redoStack = [];
        },

        restore(state) {
            this.rooms = state.rooms.map((r) => ({ uid: r.uid, name: r.name, smooth: !!r.smooth, points: r.points.map((p) => ({ ...p })) }));
            this.draftPoints = state.draftPoints.map((p) => ({ ...p }));
            this.selectedRoom = null;
            this.render();
        },

        undo() {
            if (!this.history.length) return;
            this.redoStack.push(this.snapshot());
            this.restore(this.history.pop());
        },

        redo() {
            if (!this.redoStack.length) return;
            this.history.push(this.snapshot());
            this.restore(this.redoStack.pop());
        },

        // ------------------------------------------------------------- pintado

        render() {
            if (!this.ctx || !this.canvas || !this.imgW) return;

            const ctx = this.ctx;
            ctx.setTransform(1, 0, 0, 1, 0, 0);
            ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

            if (this.img) {
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.drawImage(this.img, 0, 0, this.canvas.width, this.canvas.height);
            }

            // Espacio de imagen: todo el overlay se dibuja en imgW/imgH.
            const sx = this.canvas.width / this.imgW;
            const sy = this.canvas.height / this.imgH;
            ctx.setTransform(sx, 0, 0, sy, 0, 0);

            const ui = this.uiScale;

            // Ambientes cerrados
            this.rooms.forEach((room, index) => {
                const selected = this.selectedRoom === index;
                this.drawPolygon(this.effectivePoints(room), selected ? '#2563eb' : '#059669', true, false, ui);
                const center = this.centroid(room.points);
                this.drawLabel(
                    (room.name || '{{ __('mapscalculator.room') }} ' + (index + 1)) + '\n' + this.formatArea(this.roomArea(room)),
                    center,
                    '#111827',
                    ui
                );
            });

            // Polígono en curso (+ línea elástica hasta el ratón)
            if (this.draftPoints.length) {
                const preview = this.mouse ? this.draftPoints.concat([this.mouse]) : this.draftPoints;
                const smoothDraft = this.edgeMode === 'curve' && preview.length >= 3;
                const drawPts = smoothDraft ? this.getSmoothedPath(preview, false) : preview;
                this.drawPolygon(drawPts, '#2563eb', this.draftPoints.length >= 3 && !smoothDraft, false, ui);
                this.draftPoints.forEach((p) => this.drawVertex(p, '#2563eb', ui));
            }

            // Vértices del ambiente seleccionado (modo edición)
            if (this.mode === 'edit' && this.selectedRoom !== null && this.rooms[this.selectedRoom]) {
                this.rooms[this.selectedRoom].points.forEach((p) => this.drawVertex(p, '#f59e0b', ui));
            }

            // Línea de calibración
            if (this.calibrationStart && this.calibrationEnd) {
                ctx.save();
                ctx.strokeStyle = '#dc2626';
                ctx.lineWidth = 3 * ui;
                ctx.setLineDash([8 * ui, 6 * ui]);
                ctx.beginPath();
                ctx.moveTo(this.calibrationStart.x, this.calibrationStart.y);
                ctx.lineTo(this.calibrationEnd.x, this.calibrationEnd.y);
                ctx.stroke();
                ctx.restore();

                const mid = this.centroid([this.calibrationStart, this.calibrationEnd]);
                this.drawLabel(
                    this.calibrationPixelDistance.toFixed(1) + ' px\n' +
                    (this.scaleFactor ? '1 px = ' + this.scaleFactor.toFixed(4) + ' m' : ''),
                    mid,
                    '#dc2626',
                    ui
                );
            }

            ctx.setTransform(1, 0, 0, 1, 0, 0);
        },

        drawPolygon(points, color, fill, closed = true, ui = 1) {
            if (points.length < 2) return;
            const ctx = this.ctx;

            ctx.save();
            ctx.strokeStyle = color;
            ctx.lineWidth = Math.max(1, 2 * ui);

            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 1; i < points.length; i++) ctx.lineTo(points[i].x, points[i].y);
            if (fill) {
                ctx.closePath();
                ctx.fillStyle = color + '22';
                ctx.fill();
            }
            ctx.stroke();
            ctx.restore();
        },

        drawVertex(point, color, ui = 1) {
            const ctx = this.ctx;
            ctx.save();
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(point.x, point.y, Math.max(3, 5 * ui), 0, Math.PI * 2);
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = Math.max(1, 1.5 * ui);
            ctx.stroke();
            ctx.restore();
        },

        drawLabel(text, point, color = '#111827', ui = 1) {
            const ctx = this.ctx;
            const lines = String(text).split('\n');

            ctx.save();
            ctx.font = (13 * ui) + 'px sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';

            const width = Math.max(...lines.map((line) => ctx.measureText(line).width)) + 12 * ui;
            const height = lines.length * 16 * ui + 6 * ui;

            ctx.fillStyle = 'rgba(255,255,255,0.85)';
            ctx.fillRect(point.x - width / 2, point.y - height / 2, width, height);

            ctx.fillStyle = color;
            lines.forEach((line, index) => {
                ctx.fillText(line, point.x, point.y - height / 2 + 11 * ui + index * 15 * ui);
            });
            ctx.restore();
        },

        // --------------------------------------------------------------- guardar

        saveMeasurement() {
            if (!this.canSave) {
                this.$dispatch('notify', { message: '{{ __('mapscalculator.error_polygon_required') }}', type: 'error' });
                return;
            }

            this.saving = true;

            this.$wire.saveFromEditor({
                rooms: this.rooms.map((room) => ({ name: room.name, smooth: !!room.smooth, points: room.points })),
                scaleFactor: this.scaleFactor,
                calibrationLine: this.calibrationLine,
                label: this.label,
            }).then(() => {
                this.saving = false;
                // La captura sólo se puede guardar cuando la medición ya existe
                // en la base de datos (la usa el PDF del presupuesto).
                const capture = this.captureImage();
                if (capture) this.$wire.saveCapture(capture);
            }).catch(() => {
                this.saving = false;
            });
        },

        /**
         * JPEG del lienzo (con los polígonos ya pintados) reducido a 1000 px de
         * ancho para no mandar varios MB en la petición Livewire.
         *
         * Ojo con el nombre: esta función se llamaba `snapshot()`, igual que la
         * del historial de deshacer/rehacer. En un objeto de JS gana la última
         * definición, así que el historial guardaba data URLs y `undo()` moría
         * al hacer `state.rooms.map(...)` sobre un string.
         */
        captureImage() {
            if (!this.canvas || !this.canvas.width) return null;

            const maxWidth = 1600;
            const ratio = Math.min(1, maxWidth / this.canvas.width);

            const temp = document.createElement('canvas');
            temp.width = Math.round(this.canvas.width * ratio);
            temp.height = Math.round(this.canvas.height * ratio);

            const ctx = temp.getContext('2d');
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, temp.width, temp.height);
            ctx.drawImage(this.canvas, 0, 0, temp.width, temp.height);

            try {
                return temp.toDataURL('image/jpeg', 0.9);
            } catch (error) {
                return null;
            }
        },

        resetMeasurement() {
            if (!window.confirm('{{ __('mapscalculator.delete_confirmation') }}')) return;
            this.$wire.resetMeasurement();
            this.hardReset();
        },

        exportData() {
            const payload = {
                label: this.label,
                scaleFactor: this.scaleFactor,
                calibrationLine: this.calibrationLine,
                rooms: this.rooms.map((room) => ({ name: room.name, smooth: !!room.smooth, points: room.points })),
                area_m2: Math.round(this.rawArea * 100) / 100,
                perimeter_m: Math.round(this.rawPerimeter * 100) / 100,
            };

            const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'medicion-interior-' + (this.label || 'data') + '.json';
            link.click();
            URL.revokeObjectURL(url);
        },
    };
}
</script>
