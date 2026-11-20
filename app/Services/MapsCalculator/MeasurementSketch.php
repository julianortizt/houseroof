<?php

namespace App\Services\MapsCalculator;

use App\Models\MapsCalculator\Measurement;
use App\Models\Setting;
use Illuminate\Support\Str;

/**
 * Genera una "captura" del polígono medido para incrustarla en el PDF del
 * presupuesto (el prompt lo pide: detalle de la superficie medida con imagen
 * del polígono dibujado).
 *
 * Se dibuja en el servidor con GD en lugar de capturar el mapa del navegador:
 * así el PDF no depende de un servicio externo de teselas ni de que el
 * navegador esté abierto.
 */
class MeasurementSketch
{
    /** Paleta de relleno para los distintos ambientes / polígonos. */
    private const COLORS = [
        [37, 99, 235],
        [5, 150, 105],
        [217, 119, 6],
        [124, 58, 237],
        [220, 38, 38],
        [13, 148, 136],
    ];

    /**
     * Devuelve un data URI PNG con el croquis del polígono (o null si no hay
     * geometría que dibujar).
     */
    public static function forMeasurement(Measurement $measurement, int $width = 520, int $height = 320): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $rings = self::rings($measurement);

        if ($rings === []) {
            return null;
        }

        $background = $measurement->type === 'interior'
            ? self::backgroundImage($measurement)
            : null;

        return self::render($rings, $measurement->type === 'interior', $background, $width, $height);
    }

    /**
     * Logo de la empresa como data URI (dompdf no siempre resuelve rutas
     * relativas desde el HTML que se le pasa).
     */
    public static function logoDataUri(): ?string
    {
        $path = (string) Setting::get('logo_path', 'images/logo.png');

        if ($path === '') {
            return null;
        }

        $absolute = self::resolveLocalPath($path);

        if (! $absolute) {
            return null;
        }

        $mime = mime_content_type($absolute) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absolute));
    }

    // ------------------------------------------------------------------ rings

    /**
     * Devuelve los anillos a dibujar, ya normalizados a un sistema de
     * coordenadas comparable (grados para exterior, píxeles para interior).
     *
     * @return array<int, array<int, array{0: float, 1: float}>>
     */
    private static function rings(Measurement $measurement): array
    {
        if ($measurement->type === 'exterior') {
            return self::exteriorRings($measurement->geojson ?? []);
        }

        return self::interiorRings($measurement->polygon_points ?? []);
    }

    private static function exteriorRings(array $geojson): array
    {
        $rings = [];

        foreach ($geojson['features'] ?? [] as $feature) {
            $geometry = $feature['geometry'] ?? null;

            if (! is_array($geometry)) {
                continue;
            }

            if (($geometry['type'] ?? null) === 'Polygon') {
                $ring = $geometry['coordinates'][0] ?? [];
                if (self::ringIsUsable($ring)) {
                    $rings[] = self::asPoints($ring, 'lnglat');
                }
            } elseif (($geometry['type'] ?? null) === 'MultiPolygon') {
                foreach ($geometry['coordinates'] as $polygon) {
                    $ring = $polygon[0] ?? [];
                    if (self::ringIsUsable($ring)) {
                        $rings[] = self::asPoints($ring, 'lnglat');
                    }
                }
            }
        }

        return $rings;
    }

    /**
     * El interior guarda o bien una lista de ambientes ([{name, points}]) o
     * bien un único anillo ([{x, y}, ...]).
     */
    private static function interiorRings(array $points): array
    {
        if ($points === []) {
            return [];
        }

        $first = $points[array_key_first($points)] ?? null;

        $lists = [];

        if (is_array($first) && array_key_exists('points', $first)) {
            foreach ($points as $room) {
                if (is_array($room) && is_array($room['points'] ?? null)) {
                    $list = $room['points'];
                    if (! empty($room['smooth'])) {
                        $list = self::densifyClosedXy($list);
                    }
                    $lists[] = $list;
                }
            }
        } else {
            $lists[] = $points;
        }

        $rings = [];

        foreach ($lists as $list) {
            $ring = self::asPoints($list, 'xy');
            if (count($ring) >= 3) {
                $rings[] = $ring;
            }
        }

        return $rings;
    }

    private static function ringIsUsable(array $ring): bool
    {
        return is_array($ring) && count($ring) >= 3;
    }

    /**
     * @return array<int, array{0: float, 1: float}>
     */
    private static function asPoints(array $raw, string $mode): array
    {
        $points = [];

        foreach ($raw as $point) {
            if (! is_array($point)) {
                continue;
            }

            if ($mode === 'lnglat') {
                if (! isset($point[0], $point[1])) {
                    continue;
                }
                $points[] = [(float) $point[0], (float) $point[1]];
            } else {
                if (! isset($point['x'], $point['y'])) {
                    continue;
                }
                $points[] = [(float) $point['x'], (float) $point['y']];
            }
        }

        // GeoJSON cierra el anillo repitiendo el primer punto.
        $last = $points === [] ? null : $points[count($points) - 1];
        if ($last !== null && isset($points[0]) && $last === $points[0]) {
            array_pop($points);
        }

        return $points;
    }

    /**
     * Catmull-Rom cerrado sobre anillos {x,y} de interior (para croquis PDF
     * de ambientes con contorno curvo).
     *
     * @param  array<int, array{x: float|string, y: float|string}>  $points
     * @return array<int, array{x: float, y: float}>
     */
    private static function densifyClosedXy(array $points, float $tension = 0.3, int $steps = 12): array
    {
        $pts = [];

        foreach ($points as $p) {
            if (is_array($p) && isset($p['x'], $p['y'])) {
                $pts[] = ['x' => (float) $p['x'], 'y' => (float) $p['y']];
            }
        }

        $n = count($pts);

        if ($n < 3) {
            return self::asPoints($points, 'xy');
        }

        $out = [];

        for ($i = 0; $i < $n; $i++) {
            $p0 = $pts[($i - 1 + $n) % $n];
            $p1 = $pts[$i];
            $p2 = $pts[($i + 1) % $n];
            $p3 = $pts[($i + 2) % $n];

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

    // --------------------------------------------------------------- render

    private static function render(array $rings, bool $flipY, ?string $background, int $width, int $height): ?string
    {
        // Para el exterior se proyecta la longitud por el coseno de la latitud
        // media, de forma que el croquis mantenga las proporciones reales.
        if (! $flipY) {
            $latSum = 0.0;
            $count = 0;
            foreach ($rings as $ring) {
                foreach ($ring as $point) {
                    $latSum += $point[1];
                    $count++;
                }
            }
            $cosLat = $count > 0 ? cos(deg2rad($latSum / $count)) : 1.0;
            $cosLat = abs($cosLat) < 0.01 ? 1.0 : $cosLat;

            foreach ($rings as $i => $ring) {
                foreach ($ring as $j => $point) {
                    $rings[$i][$j] = [$point[0] * $cosLat, $point[1]];
                }
            }
        }

        $minX = $maxX = $rings[0][0][0];
        $minY = $maxY = $rings[0][0][1];

        foreach ($rings as $ring) {
            foreach ($ring as [$x, $y]) {
                $minX = min($minX, $x);
                $maxX = max($maxX, $x);
                $minY = min($minY, $y);
                $maxY = max($maxY, $y);
            }
        }

        $spanX = $maxX - $minX;
        $spanY = $maxY - $minY;

        // Un único punto o una línea degenerada: se le da un lienzo mínimo.
        if ($spanX <= 0 && $spanY <= 0) {
            return null;
        }

        $padding = 14;
        $scale = min(
            ($width - $padding * 2) / max($spanX, 1e-9),
            ($height - $padding * 2) / max($spanY, 1e-9)
        );

        $offsetX = ($width - $spanX * $scale) / 2;
        $offsetY = ($height - $spanY * $scale) / 2;

        $canvas = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $white);

        if ($background) {
            self::drawBackground($canvas, $background, $width, $height);
        }

        foreach ($rings as $index => $ring) {
            $rgb = self::COLORS[$index % count(self::COLORS)];
            $fill = imagecolorallocatealpha($canvas, $rgb[0], $rgb[1], $rgb[2], 70);
            $stroke = imagecolorallocate($canvas, $rgb[0], $rgb[1], $rgb[2]);

            $polygon = [];
            foreach ($ring as [$x, $y]) {
                $px = (int) round($offsetX + ($x - $minX) * $scale);
                $py = $flipY
                    ? (int) round($offsetY + ($y - $minY) * $scale)
                    : (int) round($offsetY + ($maxY - $y) * $scale);

                $polygon[] = $px;
                $polygon[] = $py;
            }

            if (count($polygon) >= 6) {
                imagefilledpolygon($canvas, $polygon, $fill);
                imagepolygon($canvas, $polygon, $stroke);
            }
        }

        ob_start();
        imagepng($canvas);
        $png = (string) ob_get_clean();
        imagedestroy($canvas);

        return 'data:image/png;base64,' . base64_encode($png);
    }

    private static function drawBackground($canvas, string $path, int $width, int $height): void
    {
        $image = @imagecreatefromstring((string) @file_get_contents($path));

        if (! $image) {
            return;
        }

        $sourceW = imagesx($image);
        $sourceH = imagesy($image);
        $ratio = min($width / max($sourceW, 1), $height / max($sourceH, 1));
        $targetW = (int) max(1, round($sourceW * $ratio));
        $targetH = (int) max(1, round($sourceH * $ratio));

        imagecopyresampled(
            $canvas,
            $image,
            (int) round(($width - $targetW) / 2),
            (int) round(($height - $targetH) / 2),
            0,
            0,
            $targetW,
            $targetH,
            $sourceW,
            $sourceH
        );

        imagedestroy($image);
    }

    // ------------------------------------------------------------- utilidades

    /** Imagen de fondo del interior: la captura guardada o el plano original. */
    private static function backgroundImage(Measurement $measurement): ?string
    {
        foreach ([$measurement->capture_path ?? null, $measurement->reference_image_path ?? null] as $path) {
            if (! $path) {
                continue;
            }

            $absolute = self::resolveLocalPath($path);

            if ($absolute && is_file($absolute)) {
                return $absolute;
            }
        }

        return null;
    }

    /**
     * Resuelve una ruta guardada en ajustes/mediciones (relativa al disco
     * `public`, a /storage o a public/) a un fichero del sistema.
     */
    public static function resolveLocalPath(string $path): ?string
    {
        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, 'http')) {
            $parsed = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsed) ? ltrim($parsed, '/') : '';
            $path = Str::after($path, 'storage/');
        }

        $path = ltrim($path, '/');

        $candidates = array_filter([
            upload_local_path($path),
            public_path($path),
            base_path($path),
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
