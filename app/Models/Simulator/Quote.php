<?php

namespace App\Models\Simulator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quote extends Model
{
    protected $table = 'simulator_quotes';

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'simulator_room_template_id',
        'simulator_finish_variant_id',
        'target_surface',
        'area_m2',
        'price_per_m2_snapshot',
        'total_price',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'area_m2' => 'decimal:2',
            'price_per_m2_snapshot' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function roomTemplate(): BelongsTo
    {
        return $this->belongsTo(RoomTemplate::class, 'simulator_room_template_id');
    }

    public function finishVariant(): BelongsTo
    {
        return $this->belongsTo(FinishVariant::class, 'simulator_finish_variant_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'contacted' => 'Contactado',
            'closed' => 'Cerrado',
            default => $this->status,
        };
    }

    public function getSurfaceLabelAttribute(): string
    {
        return match ($this->target_surface) {
            'floor' => 'Suelo',
            'counter' => 'Cubierta',
            default => $this->target_surface,
        };
    }
}
