<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotePaymentRefund extends Model
{
    protected $guarded = [];

    protected $casts = [
        'amount_cents' => 'integer',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(QuotePayment::class, 'quote_payment_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
