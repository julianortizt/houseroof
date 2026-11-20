<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SyncLog extends Model
{
    protected $table = 'mapscalculator_sync_logs';

    protected $guarded = [];

    protected $casts = [
        'local_data' => 'array',
        'remote_data' => 'array',
        'conflict_resolution' => 'array',
    ];

    public function syncable(): MorphTo
    {
        return $this->morphTo();
    }
}