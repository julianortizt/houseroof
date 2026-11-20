<?php

namespace App\Models\MapsCalculator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    // La tabla tiene deleted_at: sin el trait, cancelar/borrar la fila la
    // eliminaba de verdad.
    use SoftDeletes;

    protected $table = 'mapscalculator_subscriptions';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' 
            && ($this->ends_at === null || $this->ends_at->isFuture())
            && ($this->starts_at === null || $this->starts_at->isPast());
    }

    public function isTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if ($this->ends_at) {
            return now()->diffInDays($this->ends_at, false);
        }
        return null;
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}