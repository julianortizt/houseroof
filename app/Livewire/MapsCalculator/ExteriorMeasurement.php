<?php

namespace App\Livewire\MapsCalculator;

use App\Models\MapsCalculator\Measurement;
use App\Models\MapsCalculator\Project;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\Attributes\On;

class ExteriorMeasurement extends Component
{
    public Project $project;
    public ?Measurement $measurement = null;
    public ?int $measurementId = null;
    public bool $isEditing = false;

    // Map settings
    public string $mapLayer = 'pnoa'; // satellite, pnoa, hybrid, roadmap, terrain
    public ?array $mapBounds = null;
    public ?float $mapZoom = null;
    public ?array $mapCenter = null;

    // Drawing
    public array $geojson = [
        'type' => 'FeatureCollection',
        'features' => [],
    ];
    public array $circles = [];
    public array $lines = [];
    public array $compasses = [];
    public array $dragpins = [];

    // Drawing state
    public string $drawingMode = 'polygon'; // polygon, circle, line, compass, dragpin
    public bool $isDrawing = false;

    /**
     * Nombre de la medición. El editor lo escribe desde Alpine, pero antes NO
     * existía esta propiedad: `$wire.set('label', ...)` apuntaba a un atributo
     * inexistente (Livewire lanza PropertyNotFoundException), así que guardar
     * desde el navegador fallaba y toda medición quedaba como "Medición exterior".
     */
    public string $label = '';

    // Calculated
    public ?float $totalArea = 0;
    public ?float $totalPerimeter = 0;

    // Unidad preferida para mostrar los totales: m2, ha o ft2.
    public string $unit = 'm2';

    // Export
    public bool $showExportModal = false;

    protected $listeners = [
        'geojsonUpdated' => 'handleGeojsonUpdate',
        'circlesUpdated' => 'handleCirclesUpdate',
        'linesUpdated' => 'handleLinesUpdate',
        'compassesUpdated' => 'handleCompassesUpdate',
        'dragpinsUpdated' => 'handleDragpinsUpdate',
        'mapViewChanged' => 'handleMapViewChange',
    ];

    public function mount(Project $project, ?Measurement $measurement = null, ?int $measurementId = null): void
    {
        $this->project = $project;
        
        if ($measurementId) {
            $this->measurement = Measurement::find($measurementId);
            $this->measurementId = $measurementId;
        } else {
            $this->measurement = $measurement;
        }

        // Livewire resuelve el parámetro tipado por contenedor aunque no se pase,
        // así que puede llegar un Measurement vacío: se descarta.
        if (! $this->measurement?->exists) {
            $this->measurement = null;
        }

        // Preferencia de unidades del usuario (m², ha, ft²), se recuerda entre
        // mediciones: el prompt pide un selector de unidades.
        $this->unit = (string) session('mapscalculator.unit', 'm2');

        // Load project default map settings if no measurement
        if (!$this->measurement) {
            $this->mapLayer = $this->project->default_map_layer ?? 'pnoa';
            $this->mapBounds = $this->project->default_map_bounds;
            $this->mapZoom = $this->project->default_map_zoom;
            $this->mapCenter = $this->project->default_map_center;
        }

        // Livewire resuelve por contenedor el parámetro tipado `?Measurement`
        // aunque no se pase, así que $this->measurement puede ser un Measurement
        // vacío (exists = false). Sólo se carga si de verdad existe en BD.
        if ($this->measurement?->exists) {
            $this->loadMeasurementData();
        }
    }

    private function loadMeasurementData(): void
    {
        if ($this->measurement) {
            $this->geojson = $this->measurement->geojson ?? ['type' => 'FeatureCollection', 'features' => []];
            $this->circles = $this->measurement->circles ?? [];
            $this->lines = $this->measurement->lines ?? [];
            $this->compasses = $this->measurement->compasses ?? [];
            $this->dragpins = $this->measurement->dragpins ?? [];
            $this->mapLayer = $this->measurement->map_layer ?? 'pnoa';
            $this->mapBounds = $this->measurement->map_bounds;
            $this->mapZoom = $this->measurement->map_zoom;
            $this->mapCenter = $this->measurement->map_center;
            $this->totalArea = $this->measurement->area_m2;
            $this->totalPerimeter = $this->measurement->perimeter_m;
            $this->label = (string) ($this->measurement->label ?? '');
            $this->isEditing = true;
        }
    }

    public function handleGeojsonUpdate(array $geojson): void
    {
        $this->geojson = $geojson;
        $this->calculateTotals();
    }

    public function handleCirclesUpdate(array $circles): void
    {
        $this->circles = $circles;
        $this->calculateTotals();
    }

    public function handleLinesUpdate(array $lines): void
    {
        $this->lines = $lines;
        $this->calculateTotals();
    }

    public function handleCompassesUpdate(array $compasses): void
    {
        $this->compasses = $compasses;
    }

    public function handleDragpinsUpdate(array $dragpins): void
    {
        $this->dragpins = $dragpins;
    }

    public function handleMapViewChange(array $data): void
    {
        $this->mapBounds = $data['bounds'] ?? $this->mapBounds;
        $this->mapZoom = $data['zoom'] ?? $this->mapZoom;
        $this->mapCenter = $data['center'] ?? $this->mapCenter;
    }

    public function setMapLayer(string $layer): void
    {
        $this->mapLayer = $layer;
    }

    /**
     * Importa una medición en GeoJSON (FeatureCollection, Feature suelta o una
     * geometría sin envoltorio). El prompt pide poder exportar *e importar* las
     * mediciones para respaldo o para reutilizarlas desde otro sistema.
     */
    public function importGeoJSON(string $json): void
    {
        $data = json_decode($json, true);

        if (! is_array($data) || $data === []) {
            $this->dispatch('notify', message: __('mapscalculator.import_invalid'), type: 'error');

            return;
        }

        $features = $this->normalizeGeoJsonFeatures($data);

        if ($features === []) {
            $this->dispatch('notify', message: __('mapscalculator.import_invalid'), type: 'error');

            return;
        }

        $this->geojson = [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];

        $this->calculateTotals();

        // El nombre va en kebab-case: el HTML convierte a minúsculas los
        // atributos Alpine, así que un `@geojson-imported.window` nunca oiría un
        // evento llamado `geojsonImported` y el mapa no se repintaba al importar.
        $this->dispatch('geojson-imported', geojson: $this->geojson);
        $this->dispatch('notify', message: __('mapscalculator.import_done'), type: 'success');
    }

    /**
     * Acepta FeatureCollection, Feature o geometría suelta y devuelve una lista
     * de Features de polígono listas para pintar y medir.
     */
    private function normalizeGeoJsonFeatures(array $data): array
    {
        $candidates = ($data['type'] ?? null) === 'FeatureCollection' && is_array($data['features'] ?? null)
            ? $data['features']
            : [$data];

        $features = [];

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                continue;
            }

            $geometry = $candidate['geometry'] ?? $candidate;

            if (! is_array($geometry) || ! in_array($geometry['type'] ?? null, ['Polygon', 'MultiPolygon'], true)) {
                continue;
            }

            $features[] = [
                'type' => 'Feature',
                'properties' => is_array($candidate['properties'] ?? null) ? $candidate['properties'] : [],
                'geometry' => [
                    'type' => $geometry['type'],
                    'coordinates' => $geometry['coordinates'] ?? [],
                ],
            ];
        }

        return $features;
    }

    public function setDrawingMode(string $mode): void
    {
        $this->drawingMode = $mode;
        $this->isDrawing = in_array($mode, ['polygon', 'circle', 'line', 'compass', 'dragpin']);
    }

    public function calculateTotals(): void
    {
        $area = 0;
        $perimeter = 0;

        // Polygons from geojson
        foreach ($this->geojson['features'] ?? [] as $feature) {
            $geometry = $feature['geometry'] ?? null;
            if (!$geometry) continue;

            if ($geometry['type'] === 'Polygon') {
                $coords = $geometry['coordinates'][0] ?? [];
                $area += $this->calculatePolygonArea($coords);
                $perimeter += $this->calculatePolygonPerimeter($coords);
            } elseif ($geometry['type'] === 'MultiPolygon') {
                foreach ($geometry['coordinates'] as $polygon) {
                    $coords = $polygon[0] ?? [];
                    $area += $this->calculatePolygonArea($coords);
                    $perimeter += $this->calculatePolygonPerimeter($coords);
                }
            }
        }

        // Circles
        foreach ($this->circles as $circle) {
            $radius = $circle['radius'] ?? 0;
            $area += pi() * $radius * $radius;
            $perimeter += 2 * pi() * $radius;
        }

        // Lines
        foreach ($this->lines as $line) {
            $coords = $line['geometry']['coordinates'] ?? [];
            if (count($coords) >= 2) {
                $perimeter += $this->calculateLineLength($coords);
            }
        }

        $this->totalArea = round($area, 2);
        $this->totalPerimeter = round($perimeter, 2);
    }

    private function calculateLineLength(array $coords): float
    {
        $n = count($coords);
        if ($n < 2) return 0.0;

        $length = 0.0;
        for ($i = 0; $i < $n - 1; $i++) {
            $length += $this->haversineDistance($coords[$i][1], $coords[$i][0], $coords[$i + 1][1], $coords[$i + 1][0]);
        }
        return $length;
    }

    /**
     * Área geodésica de un anillo GeoJSON ([lng, lat]) en metros cuadrados.
     *
     * El shoelace sobre grados devuelve grados² —un número sin sentido físico—
     * y era lo que se guardaba como "area_m2" en las mediciones por satélite.
     * Se proyecta cada punto a un plano local equirectangular (metros) y ahí sí
     * el shoelace da m². El error es despreciable a escala de parcela.
     */
    private function calculatePolygonArea(array $coords): float
    {
        $coords = $this->openRing($coords);
        $n = count($coords);
        if ($n < 3) return 0.0;

        $latSum = 0.0;
        foreach ($coords as $point) {
            $latSum += deg2rad((float) $point[1]);
        }
        $lat0 = $latSum / $n;

        $earthRadius = 6378137.0; // WGS84, metros
        $cosLat = cos($lat0);

        $area = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;

            $x1 = deg2rad((float) $coords[$i][0]) * $earthRadius * $cosLat;
            $y1 = deg2rad((float) $coords[$i][1]) * $earthRadius;
            $x2 = deg2rad((float) $coords[$j][0]) * $earthRadius * $cosLat;
            $y2 = deg2rad((float) $coords[$j][1]) * $earthRadius;

            $area += $x1 * $y2 - $x2 * $y1;
        }

        return abs($area) / 2;
    }

    /**
     * Perímetro geodésico de un anillo GeoJSON ([lng, lat]) en metros.
     */
    private function calculatePolygonPerimeter(array $coords): float
    {
        $coords = $this->openRing($coords);
        $n = count($coords);
        if ($n < 2) return 0.0;

        $perimeter = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $perimeter += $this->haversineDistance($coords[$i][1], $coords[$i][0], $coords[$j][1], $coords[$j][0]);
        }

        return $perimeter;
    }

    /**
     * GeoJSON cierra los anillos repitiendo el primer punto; el shoelace y el
     * haversine ya cierran por su cuenta, así que se quita el duplicado.
     */
    private function openRing(array $coords): array
    {
        $n = count($coords);
        if ($n > 1 && $coords[0] === $coords[$n - 1]) {
            array_pop($coords);
        }
        return $coords;
    }

    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0; // radio medio terrestre, metros

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadius * asin(min(1.0, sqrt($a)));
    }

    /**
     * Guarda la medición. El editor manda en la misma petición el nombre, la
     * unidad y la vista actual del mapa, de modo que al reabrirla se recupera
     * exactamente dónde se estaba midiendo.
     *
     * @param  array|null  $payload  Estado que envía el editor: label, unit, geojson,
     *                               lines, circles, center, zoom, bounds.
     */
    public function save(?array $payload = null): void
    {
        Gate::authorize('createForProject', [Measurement::class, $this->project]);

        if (is_array($payload)) {
            // La geometría viaja en la misma petición que el guardado: si se
            // dependiese de los eventos `geojsonUpdated`/`linesUpdated` (cada
            // uno en su petición) el área guardada podría llegar desfasada.
            if (isset($payload['geojson']) && is_array($payload['geojson'])) {
                $this->geojson = $payload['geojson'];
            }

            if (isset($payload['lines']) && is_array($payload['lines'])) {
                $this->lines = $payload['lines'];
            }

            if (isset($payload['circles']) && is_array($payload['circles'])) {
                $this->circles = $payload['circles'];
            }

            if (array_key_exists('label', $payload)) {
                $this->label = trim((string) $payload['label']);
            }

            if (! empty($payload['unit'])) {
                $this->unit = (string) $payload['unit'];
                session(['mapscalculator.unit' => $this->unit]);
            }

            if (! empty($payload['center']) && is_array($payload['center'])) {
                $this->mapCenter = array_map('floatval', array_values($payload['center']));
            }

            if (! empty($payload['zoom'])) {
                $this->mapZoom = (float) $payload['zoom'];
            }

            if (! empty($payload['layer']) && is_string($payload['layer'])) {
                $this->mapLayer = $payload['layer'];
            }

            if (! empty($payload['bounds']) && is_array($payload['bounds'])) {
                $this->mapBounds = $payload['bounds'];
            }

            $this->calculateTotals();
        }

        $data = [
            'project_id' => $this->project->id,
            'type' => 'exterior',
            'label' => $this->label !== ''
                ? $this->label
                : ($this->measurement?->label ?? __('mapscalculator.exterior_measurement')),
            'geojson' => $this->geojson,
            'circles' => $this->circles,
            'lines' => $this->lines,
            'compasses' => $this->compasses,
            'dragpins' => $this->dragpins,
            'map_layer' => $this->mapLayer,
            'map_bounds' => $this->mapBounds,
            'map_zoom' => $this->mapZoom,
            'map_center' => $this->mapCenter,
            'area_m2' => $this->totalArea,
            'perimeter_m' => $this->totalPerimeter,
            'sort_order' => $this->measurement?->sort_order ?? $this->project->measurements()->count(),
        ];

        if ($this->measurement && $this->measurement->exists) {
            $this->measurement->update($data);
            $this->dispatch('notify', message: __('mapscalculator.measurement_updated'), type: 'success');
        } else {
            $measurement = Measurement::create($data);
            $this->measurement = $measurement;
            $this->measurementId = $measurement->id;
            $this->isEditing = true;
            $this->dispatch('notify', message: __('mapscalculator.measurement_created'), type: 'success');
        }

        // Guardar una medición deja el proyecto en "medido" (igual que interior).
        $this->project->update(['status' => 'medido']);

        $this->dispatch('measurement-saved', measurementId: $this->measurement->id);
        $this->dispatch('geojsonUpdated', geojson: $this->geojson);
    }

    public function exportGeoJSON(): void
    {
        $geojson = $this->buildFullGeoJSON();
        $this->showExportModal = true;
        $this->dispatch('exportReady', geojson: $geojson);
    }

    public function buildFullGeoJSON(): array
    {
        $features = [];

        // Polygons from geojson
        foreach ($this->geojson['features'] ?? [] as $feature) {
            $features[] = $feature;
        }

        // Circles
        foreach ($this->circles as $circle) {
            $features[] = [
                'type' => 'Feature',
                'properties' => array_merge($circle['properties'] ?? [], ['type' => 'circle']),
                'geometry' => $circle['geometry'] ?? null,
            ];
        }

        // Lines
        foreach ($this->lines as $line) {
            $features[] = [
                'type' => 'Feature',
                'properties' => array_merge($line['properties'] ?? [], ['type' => 'line']),
                'geometry' => $line['geometry'] ?? null,
            ];
        }

        // Compasses
        if (!empty($this->compasses)) {
            foreach ($this->compasses as $compass) {
                $features[] = [
                    'type' => 'Feature',
                    'properties' => array_merge($compass['properties'] ?? [], ['type' => 'compass']),
                    'geometry' => $compass['geometry'] ?? null,
                ];
            }
        }

        // Drag pins
        foreach ($this->dragpins as $pin) {
            $features[] = [
                'type' => 'Feature',
                'properties' => array_merge($pin['properties'] ?? [], ['type' => 'pin']),
                'geometry' => $pin['geometry'] ?? null,
            ];
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    public function downloadGeoJSON(): void
    {
        $geojson = $this->buildFullGeoJSON();
        $filename = 'medicion-' . ($this->measurement?->label ?? 'exterior') . '-' . date('Ymd-His') . '.geojson';
        
        $this->dispatch('downloadFile', [
            'filename' => $filename,
            'content' => json_encode($geojson, JSON_PRETTY_PRINT),
            'mimeType' => 'application/geo+json',
        ]);
    }

    public function delete(): void
    {
        if ($this->measurement?->exists) {
            Gate::authorize('delete', $this->measurement);
            $this->measurement->delete();
            $this->dispatch('measurementDeleted');
            $this->dispatch('notify', message: __('mapscalculator.measurement_deleted'), type: 'success');
        }
    }

    public function render()
    {
        return view('livewire.mapscalculator.exterior-measurement');
    }
}