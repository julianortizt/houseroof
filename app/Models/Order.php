<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    public $guarded = [];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'stock_review_refund_amount_cents' => 'integer',
        'stock_review_refund_requested_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'processing' => 'En proceso',
            'shipped' => 'Enviado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            'stock_review' => 'Revisión de existencias',
            default => $this->status,
        };
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'No pagado',
            'paid' => 'Pagado',
            'refunded' => 'Reembolsado',
            'failed' => 'Pago fallido',
            default => $this->payment_status,
        };
    }

    public function getTotalFormatted(): string
    {
        return number_format($this->total, 2, ',', '.') . ' €';
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'IE-' . date('Y') . '-';
        $lastOrder = static::withTrashed()
            ->where('order_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->order_number, -5);
            return $prefix . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        }

        return $prefix . '00001';
    }
}
