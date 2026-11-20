<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Measurement extends Model
{
    protected $table = 'mapscalculator_measurements';

    protected $guarded = [];

    protected $casts = [
        'geojson' => 'array',
        'polygon_points' => 'array',
        'calibration_line' => 'array',
        'circles' => 'array',
        'lines' => 'array',
        'compasses' => 'array',
        'dragpins' => 'array',
        'geojson_features' => 'array',
        'export_data' => 'array',
        'map_bounds' => 'array',
        'map_center' => 'array',
        'map_bounds' => 'array',
        'sync_conflicts' => 'array',
        'export_data' => 'array',
        'area_m2' => 'decimal:2',
        'perimeter_m' => 'decimal:2',
        'scale_factor' => 'decimal:10',
        'map_zoom' => 'decimal:4',
        'sort_order' => 'integer',
        'synced' => 'boolean',
        'is_premium' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function layers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DrawingLayer::class)->orderBy('sort_order');
    }

    public function syncLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(SyncLog::class, 'syncable');
    }

    public function getReferenceImageUrlAttribute(): ?string
    {
        return $this->reference_image_path
            ? Storage::url($this->reference_image_path)
            : null;
    }

    public function getGeojsonFeatureCollectionAttribute(): array
    {
        $features = [];

        // Polygons from geojson (exterior)
        if ($this->type === 'exterior' && $this->geojson) {
            $features = array_merge($features, $this->geojson['features'] ?? []);
        }

        // Polygons from polygon_points (interior)
        if ($this->type === 'interior' && $this->polygon_points) {
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'label' => $this->label,
                    'area_m2' => $this->area_m2,
                    'perimeter_m' => $this->perimeter_m,
                    'type' => 'polygon',
                ],
                'geometry' => [
                    'type' => 'Polygon',
                    'coordinates' => [$this->polygon_points],
                ],
            ];
        }

        // Circles
        if ($this->circles) {
            foreach ($this->circles as $circle) {
                $features[] = [
                    'type' => 'Feature',
                    'properties' => array_merge($circle['properties'] ?? [], ['type' => 'circle']),
                    'geometry' => $circle['geometry'] ?? null,
                ];
            }
        }

        // Lines
        if ($this->lines) {
            foreach ($this->lines as $line) {
                $features[] = [
                    'type' => 'Feature',
                    'properties' => array_merge($line['properties'] ?? [], ['type' => 'line']),
                    'geometry' => $line['geometry'] ?? null,
                ];
            }
        }

        // Compasses
        if ($this->compasses) {
            foreach ($this->compasses as $compass) {
                $features[] = [
                    'type' => 'Feature',
                    'properties' => array_merge($compass['properties'] ?? [], ['type' => 'compass']),
                    'geometry' => $compass['geometry'] ?? null,
                ];
            }
        }

        // Drag pins
        if ($this->dragpins) {
            foreach ($this->dragpins as $pin) {
                $features[] = [
                    'type' => 'Feature',
                    'properties' => array_merge($pin['properties'] ?? [], ['type' => 'pin']),
                    'geometry' => $pin['geometry'] ?? null,
                ];
            }
        }

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    public function getFullExportDataAttribute(): array
    {
        return array_merge($this->export_data ?? [], [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'type' => $this->type,
            'label' => $this->label,
            'area_m2' => $this->area_m2,
            'perimeter_m' => $this->perimeter_m,
            'map_layer' => $this->map_layer,
            'map_bounds' => $this->map_bounds,
            'map_zoom' => $this->map_zoom,
            'map_center' => $this->map_center,
            'drawing_mode' => $this->drawing_mode,
            'geojson' => $this->geojson,
            'polygon_points' => $this->polygon_points,
            'calibration_line' => $this->calibration_line,
            'reference_image_path' => $this->reference_image_path,
            'scale_factor' => $this->scale_factor,
            'area_m2' => $this->area_m2,
            'perimeter_m' => $this->perimeter_m,
            'circles' => $this->circles,
            'lines' => $this->lines,
            'compasses' => $this->compasses,
            'dragpins' => $this->dragpins,
            'geojson_features' => $this->geojson_features,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ]);
    }

    public function calculateAreaFromPoints(array $points, ?float $scaleFactor = null): float
    {
        if (count($points) < 3) return 0;

        $area = 0;
        $count = count($points);

        for ($i = 0; $i < $count; $i++) {
            $j = ($i + 1) % $count;
            $area += $points[$i]['x'] * $points[$j]['y'];
            $area -= $points[$j]['x'] * $points[$i]['y'];
        }

        $areaPx = abs($area) / 2;

        if ($this->type === 'interior' && $scaleFactor) {
            return round($areaPx * ($scaleFactor ** 2), 2);
        }

        return round($areaPx, 2);
    }

    public function calculatePerimeterFromPoints(array $points, ?float $scaleFactor = null): float
    {
        if (count($points) < 2) return 0;

        $perimeter = 0;
        $count = count($points);

        for ($i = 0; $i < $count; $i++) {
            $j = ($i + 1) % $count;
            $dx = $points[$j]['x'] - $points[$i]['x'];
            $dy = $points[$j]['y'] - $points[$i]['y'];
            $perimeter += sqrt($dx * $dx + $dy * $dy);
        }

        if ($this->type === 'interior' && $scaleFactor) {
            return round($perimeter * $scaleFactor, 2);
        }

        return round($perimeter, 2);
    }

    public function updateCalculatedValues(): void
    {
        if ($this->type === 'exterior' && $this->geojson) {
            $this->calculateFromGeojson();
        } elseif ($this->type === 'interior' && $this->polygon_points) {
            $this->calculateFromPoints();
        }
    }

    private function calculateFromGeojson(): void
    {
        $features = $this->geojson['features'] ?? [];
        $totalArea = 0;
        $totalPerimeter = 0;

        foreach ($features as $feature) {
            $geometry = $feature['geometry'] ?? null;
            if (!$geometry) continue;

            if ($geometry['type'] === 'Polygon') {
                $coords = $geometry['coordinates'][0] ?? [];
                $totalArea += $this->calculatePolygonArea($coords);
                $totalPerimeter += $this->calculatePolygonPerimeter($coords);
            } elseif ($geometry['type'] === 'MultiPolygon') {
                foreach ($geometry['coordinates'] as $polygon) {
                    $coords = $polygon[0] ?? [];
                    $totalArea += $this->calculatePolygonArea($coords);
                    $totalPerimeter += $this->calculatePolygonPerimeter($coords);
                }
            }
        }

        $this->updateQuietly([
            'area_m2' => round($totalArea, 2),
            'perimeter_m' => round($totalPerimeter, 2),
        ]);
    }

    private function calculateFromPoints(): void
    {
        $scale = $this->scale_factor ?? 1;
        $this->updateQuietly([
            'area_m2' => $this->calculateAreaFromPoints($this->polygon_points, $scale),
            'perimeter_m' => $this->calculatePerimeterFromPoints($this->polygon_points, $scale),
        ]);
    }

    /**
     * Área geodésica en m² de un anillo GeoJSON ([lng, lat]).
     *
     * Antes se aplicaba el shoelace directamente sobre grados, así que el
     * resultado (guardado como area_m2) era una fracción de grado².
     */
    private function calculatePolygonArea(array $coords): float
    {
        $coords = $this->openRing($coords);
        $count = count($coords);
        if ($count < 3) return 0;

        $latSum = 0.0;
        foreach ($coords as $point) {
            $latSum += deg2rad((float) $point[1]);
        }
        $lat0 = $latSum / $count;

        $earthRadius = 6378137.0; // WGS84, metros
        $cosLat = cos($lat0);

        $area = 0.0;
        for ($i = 0; $i < $count; $i++) {
            $j = ($i + 1) % $count;

            $x1 = deg2rad((float) $coords[$i][0]) * $earthRadius * $cosLat;
            $y1 = deg2rad((float) $coords[$i][1]) * $earthRadius;
            $x2 = deg2rad((float) $coords[$j][0]) * $earthRadius * $cosLat;
            $y2 = deg2rad((float) $coords[$j][1]) * $earthRadius;

            $area += $x1 * $y2 - $x2 * $y1;
        }

        return abs($area) / 2;
    }

    private function calculatePolygonPerimeter(array $coords): float
    {
        $coords = $this->openRing($coords);
        $count = count($coords);
        if ($count < 2) return 0;

        $perimeter = 0.0;
        for ($i = 0; $i < $count; $i++) {
            $j = ($i + 1) % $count;
            $perimeter += $this->haversineDistance(
                (float) $coords[$i][1], (float) $coords[$i][0],
                (float) $coords[$j][1], (float) $coords[$j][0]
            );
        }

        return $perimeter;
    }

    private function openRing(array $coords): array
    {
        $count = count($coords);
        if ($count > 1 && $coords[0] === $coords[$count - 1]) {
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
}