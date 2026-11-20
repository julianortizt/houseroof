<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryPurchaseOrderItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:4',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(InventoryPurchaseOrder::class, 'purchase_order_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'inventory_location_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(InventoryReceiptItem::class, 'purchase_order_item_id');
    }

    public function getReceivedQuantityAttribute(): string
    {
        return number_format((float) $this->receipts()->sum('quantity'), 3, '.', '');
    }

    public function getOutstandingQuantityAttribute(): string
    {
        return number_format(max(0, (float) $this->quantity - (float) $this->received_quantity), 3, '.', '');
    }
}
