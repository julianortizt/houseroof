<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class ProductVariant extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    public $guarded = [];
    public array $translatable = ['name', 'description'];

    protected $casts = [
        'attributes' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getPriceFormatted(): string
    {
        $price = $this->price ?? $this->product->price;
        return number_format($price, 2, ',', '.') . ' €';
    }

    public function getEffectivePrice(): float
    {
        return $this->price ?? $this->product->price;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
