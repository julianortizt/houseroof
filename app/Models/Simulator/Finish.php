<?php

namespace App\Models\Simulator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finish extends Model
{
    protected $table = 'simulator_finishes';

    protected $fillable = [
        'name',
        'type',
        'price_per_m2',
        'currency',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_per_m2' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function variants(): HasMany
    {
        return $this->hasMany(FinishVariant::class, 'simulator_finish_id');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
