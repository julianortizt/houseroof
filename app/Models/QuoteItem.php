<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class QuoteItem extends Model
{
    protected $table = 'quote_items';

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total_price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getDiscountAmountAttribute(): float
    {
        return round($this->unit_price * $this->quantity * ($this->discount_percent / 100), 2);
    }

    public function getSubtotalAttribute(): float
    {
        return round($this->unit_price * $this->quantity, 2);
    }

    public function getTotalWithDiscountAttribute(): float
    {
        return round($this->subtotal - $this->discount_amount, 2);
    }

    public function calculateTotal(): float
    {
        $subtotal = $this->unit_price * $this->quantity;
        $discount = $subtotal * ($this->discount_percent / 100);
        return round($subtotal - $discount, 2);
    }

    public static function booted(): void
    {
        static::saving(fn (self $item) => $item->ensureQuoteHasNoActivePayments());
        static::deleting(fn (self $item) => $item->ensureQuoteHasNoActivePayments());

        static::saving(function (self $item) {
            $item->total_price = $item->calculateTotal();
        });
    }

    private function ensureQuoteHasNoActivePayments(): void
    {
        if ($this->quote_id && Quote::query()
            ->whereKey($this->quote_id)
            ->whereHas('payments', fn ($query) => $query->whereIn('status', ['pending', 'paid']))
            ->exists()) {
            throw ValidationException::withMessages([
                'items' => 'No se pueden modificar líneas cuando la cotización tiene pagos iniciados o confirmados.',
            ]);
        }
    }
}