<?php

namespace App\Livewire\MapsCalculator;

use App\Models\MapsCalculator\Measurement;
use App\Models\MapsCalculator\Project;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class InteriorMeasurement extends Component
{
    use WithFileUploads;

    public Project $project;
    public ?Measurement $measurement = null;
    public ?int $measurementId = null;
    public bool $isEditing = false;

    // Image
    public $referenceImage;
    public string $referenceImagePath = '';
    public ?string $referenceImageUrl = null;
    public float $scaleFactor = 0; // 1 píxel = X metros
    public ?array $polygonPoints = []; // un único anillo: [{x, y}, ...]
    /**
     * Ambientes del plano: [{ name: 'Cocina', points: [{x, y}, ...] }, ...].
     * El prompt (módulo 1.2) exige poder medir varios ambientes en el mismo
     * plano, cada uno con su etiqueta; antes sólo se guardaba un polígono.
     */
    public array $rooms = [];
    public ?array $calibrationLine = null; // {x1, y1, x2, y2, realMeters, pixelDistance}
    public bool $isCalibrating = false;
    public bool $showImage = false;

    // Calculated
    public ?float $totalArea = 0;
    public ?float $totalPerimeter = 0;
    public string $label = '';

    public function mount(Project $project, ?Measurement $measurement = null, ?int $measurementId = null): void
    {
        $this->project = $project;

        if ($measurementId) {
            $this->measurement = Measurement::find($measurementId);
            $this->measurementId = $measurementId;
        } else {
            $this->measurement = $measurement;
        }

        // Livewire resuelve el parámetro tipado `?Measurement` por contenedor
        // aunque no se pase, así que puede llegar un Measurement vacío.
        if (! $this->measurement?->exists) {
            $this->measurement = null;
        }

        if ($this->measurement?->exists) {
            $this->loadMeasurementData();
        }
    }

    private function loadMeasurementData(): void
    {
        if (! $this->measurement) {
            return;
        }

        $this->referenceImagePath = $this->measurement->reference_image_path ?? '';
        $this->referenceImageUrl = $this->measurement->reference_image_path
            ? Storage::url($this->measurement->reference_image_path)
            : null;
        $this->scaleFactor = (float) ($this->measurement->scale_factor ?? 0);
        $this->rooms = $this->normalizeRooms($this->measurement->polygon_points ?? []);
        $this->polygonPoints = $this->rooms[0]['points'] ?? [];
        $this->calibrationLine = $this->measurement->calibration_line;
        $this->totalArea = $this->measurement->area_m2;
        $this->totalPerimeter = $this->measurement->perimeter_m;
        $this->label = $this->measurement->label ?? '';
        $this->isEditing = true;
        $this->showImage = true;
    }

    /**
     * Acepta tanto el formato nuevo ([{name, points}]) como el antiguo
     * ([{x, y}, ...]) y devuelve siempre una lista de ambientes.
     */
    private function normalizeRooms(?array $points): array
    {
        if (empty($points)) {
            return [];
        }

        $first = $points[array_key_first($points)] ?? null;

        // Formato nuevo: lista de ambientes con su nombre.
        if (is_array($first) && array_key_exists('points', $first)) {
            $rooms = [];
            foreach ($points as $room) {
                if (! is_array($room)) {
                    continue;
                }
                $rooms[] = [
                    'name' => (string) ($room['name'] ?? ''),
                    'points' => $this->normalizePoints($room['points'] ?? []),
                    'smooth' => (bool) ($room['smooth'] ?? false),
                ];
            }

            return array_values(array_filter($rooms, fn ($room) => count($room['points']) >= 3));
        }

        // Formato antiguo: un único polígono.
        $ring = $this->normalizePoints($points);

        return count($ring) >= 3 ? [['name' => '', 'points' => $ring, 'smooth' => false]] : [];
    }

    private function normalizePoints(?array $points): array
    {
        $clean = [];

        foreach ((array) $points as $point) {
            if (! is_array($point) || ! isset($point['x'], $point['y'])) {
                continue;
            }
            $clean[] = ['x' => (float) $point['x'], 'y' => (float) $point['y']];
        }

        return $clean;
    }

    public function updatedReferenceImage(): void
    {
        $this->validate([
            'referenceImage' => 'required|image|max:10240', // 10MB
        ]);

        // Al reemplazar la imagen hay que borrar la anterior para no acumular basura.
        if ($this->referenceImagePath && $this->referenceImagePath !== $this->measurement?->reference_image_path) {
            Storage::disk('public')->delete($this->referenceImagePath);
        }

        $path = $this->referenceImage->store('mapscalculator/measurements', 'public');
        $this->referenceImagePath = $path;
        $this->referenceImageUrl = Storage::url($path);
        $this->showImage = true;
        $this->rooms = [];
        $this->polygonPoints = [];
        $this->calibrationLine = null;
        $this->scaleFactor = 0;
        $this->totalArea = 0;
        $this->totalPerimeter = 0;
        $this->isCalibrating = true;

        // Kebab-case obligatorio: los atributos Alpine del HTML van en
        // minúsculas, así que `@image-loaded.window` no oiría `imageLoaded`.
        $this->dispatch('image-loaded', path: $this->referenceImageUrl);
    }

    /**
     * Punto de entrada del editor canvas (Alpine). Antes el editor mandaba el
     * estado a través de eventos que el componente escuchaba, pero la parte
     * Alpine nunca existió, así que el interior no se podía guardar nunca.
     */
    public function saveFromEditor(array $data = []): void
    {
        Gate::authorize('createForProject', [Measurement::class, $this->project]);

        if (isset($data['scaleFactor'])) {
            $this->scaleFactor = (float) $data['scaleFactor'];
        }

        if (isset($data['calibrationLine']) && is_array($data['calibrationLine'])) {
            $this->calibrationLine = $data['calibrationLine'];
        }

        if (isset($data['label'])) {
            $this->label = trim((string) $data['label']);
        }

        if (isset($data['rooms']) && is_array($data['rooms'])) {
            $this->rooms = $this->normalizeRooms($data['rooms']);
        }

        $this->polygonPoints = $this->rooms[0]['points'] ?? [];

        $this->calculateTotals();
        $this->save();
    }

    public function handleCalibrationDone(array $line): void
    {
        $this->calibrationLine = $line;
        $this->scaleFactor = $line['realMeters'] / max(0.0001, $line['pixelDistance']);
        $this->isCalibrating = false;
        $this->calculateTotals();
    }

    public function calculateTotals(): void
    {
        if (! $this->scaleFactor) {
            $this->totalArea = 0;
            $this->totalPerimeter = 0;

            return;
        }

        $areaPx = 0.0;
        $perimeterPx = 0.0;

        foreach ($this->rooms as $room) {
            $points = $room['points'] ?? [];
            if (count($points) < 3) {
                continue;
            }
            // Contornos curvas: el área/perímetro se calculan sobre la
            // ruta densificada (Catmull-Rom), no sobre los vértices toscos.
            if (! empty($room['smooth'])) {
                $points = self::densifyClosed($points);
            }
            $areaPx += $this->calculatePolygonArea($points);
            $perimeterPx += $this->calculatePolygonPerimeter($points);
        }

        $this->totalArea = round($areaPx * ($this->scaleFactor ** 2), 2);
        $this->totalPerimeter = round($perimeterPx * $this->scaleFactor, 2);
    }

    /**
     * Catmull-Rom cerrado (misma fórmula que el editor JS) → puntos densos.
     *
     * @param  array<int, array{x: float, y: float}>  $points
     * @return array<int, array{x: float, y: float}>
     */
    private static function densifyClosed(array $points, float $tension = 0.3, int $steps = 16): array
    {
        $n = count($points);

        if ($n < 3) {
            return $points;
        }

        $out = [];

        for ($i = 0; $i < $n; $i++) {
            $p0 = $points[($i - 1 + $n) % $n];
            $p1 = $points[$i];
            $p2 = $points[($i + 1) % $n];
            $p3 = $points[($i + 2) % $n];

            for ($s = 0; $s < $steps; $s++) {
                $st = $s / $steps;
                $st2 = $st * $st;
                $st3 = $st2 * $st;

                $out[] = [
                    'x' => (2 * $st3 - 3 * $st2 + 1) * $p1['x']
                        + (-2 * $st3 + 3 * $st2) * $p2['x']
                        + ($st3 - 2 * $st2 + $st) * $tension * ($p2['x'] - $p0['x'])
                        + (-$st3 + $st2) * $tension * ($p3['x'] - $p1['x']),
                    'y' => (2 * $st3 - 3 * $st2 + 1) * $p1['y']
                        + (-2 * $st3 + 3 * $st2) * $p2['y']
                        + ($st3 - 2 * $st2 + $st) * $tension * ($p2['y'] - $p0['y'])
                        + (-$st3 + $st2) * $tension * ($p3['y'] - $p1['y']),
                ];
            }
        }

        return $out;
    }

    private function calculatePolygonArea(array $points): float
    {
        if (count($points) < 3) {
            return 0;
        }

        $area = 0.0;
        $count = count($points);

        for ($i = 0; $i < $count; $i++) {
            $j = ($i + 1) % $count;
            $area += $points[$i]['x'] * $points[$j]['y'];
            $area -= $points[$j]['x'] * $points[$i]['y'];
        }

        return abs($area) / 2;
    }

    private function calculatePolygonPerimeter(array $points): float
    {
        if (count($points) < 2) {
            return 0;
        }

        $perimeter = 0.0;
        $count = count($points);

        for ($i = 0; $i < $count; $i++) {
            $j = ($i + 1) % $count;
            $dx = $points[$j]['x'] - $points[$i]['x'];
            $dy = $points[$j]['y'] - $points[$i]['y'];
            $perimeter += sqrt($dx * $dx + $dy * $dy);
        }

        return $perimeter;
    }

    public function save(): void
    {
        if (! $this->referenceImagePath) {
            $this->dispatch('notify', message: __('mapscalculator.error_image_required'), type: 'error');

            return;
        }

        if (! $this->scaleFactor) {
            $this->dispatch('notify', message: __('mapscalculator.error_calibration_required'), type: 'error');

            return;
        }

        if (empty($this->rooms)) {
            $this->dispatch('notify', message: __('mapscalculator.error_polygon_required'), type: 'error');

            return;
        }

        // Un solo ambiente sin nombre y sin curvas se guarda como anillo
        // simple para seguir siendo compatible con lo ya almacenado y con
        // la exportación GeoJSON; si es curvo necesita el flag `smooth`.
        $keepRoomList = count($this->rooms) > 1
            || ($this->rooms[0]['name'] ?? '') !== ''
            || ! empty($this->rooms[0]['smooth']);

        $payload = $keepRoomList
            ? $this->rooms
            : $this->rooms[0]['points'];

        $data = [
            'project_id' => $this->project->id,
            'type' => 'interior',
            'label' => $this->label ?: __('mapscalculator.interior_measurement'),
            'reference_image_path' => $this->referenceImagePath,
            'scale_factor' => $this->scaleFactor,
            'polygon_points' => $payload,
            'calibration_line' => $this->calibrationLine,
            'area_m2' => $this->totalArea,
            'perimeter_m' => $this->totalPerimeter,
            'sort_order' => $this->measurement?->sort_order ?? $this->project->measurements()->count(),
        ];

        // update() sobre un modelo sin persistir (exists = false) es un no-op
        // silencioso en Eloquent: hay que comprobar `exists`, no la nulidad.
        if ($this->measurement?->exists) {
            $this->measurement->update($data);
            $this->dispatch('notify', message: __('mapscalculator.measurement_updated'), type: 'success');
        } else {
            $this->measurement = Measurement::create($data);
            $this->measurementId = $this->measurement->id;
            $this->isEditing = true;
            $this->dispatch('notify', message: __('mapscalculator.measurement_created'), type: 'success');
        }

        $this->project->update(['status' => 'medido']);

        $this->dispatch('measurement-saved', measurementId: $this->measurement->id);
    }

    /**
     * Guarda la captura del lienzo (PNG en base64 que envía el editor) para
     * incrustarla después en el PDF del presupuesto.
     */
    public function saveCapture(string $dataUrl): void
    {
        if (! $this->measurement?->exists) {
            return;
        }

        Gate::authorize('update', $this->measurement);

        if (! preg_match('#^data:image/(png|jpeg);base64,#', $dataUrl)) {
            return;
        }

        $binary = base64_decode(Str::after($dataUrl, 'base64,'), true);

        if ($binary === false || strlen($binary) < 100) {
            return;
        }

        if ($this->measurement->capture_path) {
            Storage::disk('public')->delete($this->measurement->capture_path);
        }

        // La extensión se deduce del contenido real (el editor manda JPEG).
        $info = @getimagesizefromstring($binary);
        $extension = ($info['mime'] ?? '') === 'image/jpeg' ? 'jpg' : 'png';

        $path = 'mapscalculator/captures/medicion-' . $this->measurement->id . '-' . Str::random(8) . '.' . $extension;
        Storage::disk('public')->put($path, $binary);

        $this->measurement->update(['capture_path' => $path]);
    }

    public function delete(): void
    {
        if ($this->measurement?->exists) {
            Gate::authorize('delete', $this->measurement);

            if ($this->measurement->capture_path) {
                Storage::disk('public')->delete($this->measurement->capture_path);
            }

            if ($this->measurement->reference_image_path) {
                Storage::disk('public')->delete($this->measurement->reference_image_path);
            }
            $this->measurement->delete();
            $this->dispatch('measurementDeleted');
            $this->dispatch('notify', message: __('mapscalculator.measurement_deleted'), type: 'success');
        }
    }

    public function resetMeasurement(): void
    {
        $this->referenceImage = null;
        $this->rooms = [];
        $this->polygonPoints = [];
        $this->calibrationLine = null;
        $this->scaleFactor = 0;
        $this->totalArea = 0;
        $this->totalPerimeter = 0;
        $this->showImage = false;
        $this->isCalibrating = false;
        $this->dispatch('reset-canvas');
    }

    /**
     * Exporta la medición completa (escala, ambientes y totales) en JSON.
     */
    public function exportData(): void
    {
        $data = [
            'label' => $this->label,
            'reference_image_path' => $this->referenceImagePath,
            'scale_factor' => $this->scaleFactor,
            'calibration_line' => $this->calibrationLine,
            'rooms' => $this->rooms,
            'area_m2' => $this->totalArea,
            'perimeter_m' => $this->totalPerimeter,
        ];

        $this->dispatch('downloadFile', [
            'filename' => 'medicion-interior-' . ($this->label ?: 'data') . '-' . date('Ymd-His') . '.json',
            'content' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'mimeType' => 'application/json',
        ]);
    }

    public function render()
    {
        return view('livewire.mapscalculator.interior-measurement');
    }
}
