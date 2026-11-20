<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotePayment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount_cents' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(QuotePaymentRefund::class);
    }

    public function getRefundedAmountCentsAttribute(): int
    {
        return (int) $this->refunds()
            ->whereIn('status', ['pending', 'succeeded'])
            ->sum('amount_cents');
    }

    public function getRefundableAmountCentsAttribute(): int
    {
        return $this->status === 'paid'
            ? max(0, $this->amount_cents - $this->refunded_amount_cents)
            : 0;
    }

    public function getPaymentTypeLabelAttribute(): string
    {
        return match ($this->payment_type) {
            'deposit' => 'Anticipo',
            'balance' => 'Saldo final',
            default => $this->payment_type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'failed' => 'Fallido',
            'expired' => 'Expirado',
            default => $this->status,
        };
    }
}
