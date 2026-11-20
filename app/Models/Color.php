<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Color extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia;

    public $guarded = [];
    public array $translatable = ['name', 'description'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Virtual accessor for Filament ImageColumn.
     * Returns the first media URL or null.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('default') ?: null;
    }
}
