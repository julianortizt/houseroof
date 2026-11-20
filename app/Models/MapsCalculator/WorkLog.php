<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WorkLog extends Model
{
    protected $table = 'mapscalculator_work_logs';

    protected $guarded = [];

    protected $casts = [
        'date' => 'date',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'antes' => __('mapscalculator.log_before'),
            'durante' => __('mapscalculator.log_during'),
            'despues' => __('mapscalculator.log_after'),
            'general' => __('mapscalculator.log_general'),
            default => $this->type,
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'antes' => 'heroicon-o-camera',
            'durante' => 'heroicon-o-wrench-screwdriver',
            'despues' => 'heroicon-o-check-circle',
            'general' => 'heroicon-o-document-text',
            default => 'heroicon-o-document-text',
        };
    }
}