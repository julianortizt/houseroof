<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use App\Models\Traits\AutoTranslates;

class Project extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes, AutoTranslates;

    public const CATEGORIES = ['planning', 'delivery', 'service', 'other'];

    public $guarded = [];
    public array $translatable = ['title', 'description', 'location'];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public static function categoryOptions(): array
    {
        $labels = [
            'planning' => ['en' => 'Planning & measurement', 'es' => 'Planificación y medición'],
            'delivery' => ['en' => 'Work execution', 'es' => 'Ejecución del trabajo'],
            'service' => ['en' => 'Service management', 'es' => 'Gestión del servicio'],
            'other' => ['en' => 'Other', 'es' => 'Otro'],
        ];
        $locale = app()->getLocale();

        return collect($labels)
            ->map(fn (array $translations) => $translations[$locale] ?? $translations['en'])
            ->all();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('default') ?: null;
    }
}
