<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawingLayer extends Model
{
    protected $table = 'mapscalculator_drawing_layers';

    protected $guarded = [];

    protected $casts = [
        'data' => 'array',
        'style' => 'array',
        'visible' => 'boolean',
        'locked' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(Measurement::class);
    }
}