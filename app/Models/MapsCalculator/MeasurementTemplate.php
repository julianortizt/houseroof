<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeasurementTemplate extends Model
{
    protected $table = 'mapscalculator_measurement_templates';

    protected $guarded = [];

    protected $casts = [
        'default_settings' => 'array',
        'default_layers' => 'array',
        'is_system' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}