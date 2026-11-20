<?php

namespace App\Services;

use App\Models\Quote;
use App\Services\MapsCalculator\MeasurementSketch;
use Barryvdh\DomPDF\Facade\Pdf;

class QuotePdfService
{
    public function make(Quote $quote)
    {
        $quote->load(['items', 'project.client', 'project.measurements']);
        $measurements = $quote->project?->measurements ?? collect();

        $sketches = [];
        foreach ($measurements as $measurement) {
            $sketch = MeasurementSketch::forMeasurement($measurement);
            if ($sketch) {
                $sketches[$measurement->id] = $sketch;
            }
        }

        $company = [
            'name' => \App\Models\Setting::get('site_name', config('app.name', 'Costa Resin & Construction')),
            'email' => \App\Models\Setting::get('site_email', ''),
            'phone' => \App\Models\Setting::get('site_phone', ''),
            'address' => \App\Models\Setting::get('site_address', ''),
            'nif' => \App\Models\Setting::get('site_nif', ''),
            'logo' => MeasurementSketch::logoDataUri(),
        ];

        return Pdf::loadView('mapscalculator.quote-pdf', [
            'quote' => $quote,
            'measurements' => $measurements,
            'sketches' => $sketches,
            'company' => $company,
        ])->setPaper('A4', 'portrait');
    }
}
