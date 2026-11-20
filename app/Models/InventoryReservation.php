<?php

namespace App\Models;

use App\Models\MapsCalculator\WorkOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryReservation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
        'consumed_quantity' => 'decimal:3',
        'released_at' => 'datetime',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'inventory_location_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function releaser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function getRemainingQuantityAttribute(): string
    {
        return number_format((float) $this->quantity - (float) $this->consumed_quantity, 3, '.', '');
    }
}
