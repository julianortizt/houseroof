<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use App\Models\Traits\AutoTranslates;

class Category extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, AutoTranslates;

    public $guarded = [];
    public array $translatable = ['name', 'description'];

    /**
     * Override getAttribute to return translation for current locale.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->translatable) && is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $locale = app()->getLocale();
                $candidate = $decoded[$locale] ?? null;
                if (!is_string($candidate) || trim($candidate) === '') {
                    $candidate = $decoded['en'] ?? null;
                }
                if (!is_string($candidate) || trim($candidate) === '') {
                    $candidate = $decoded['es'] ?? null;
                }
                if (!is_string($candidate) || trim($candidate) === '') {
                    foreach ($decoded as $fallback) {
                        if (is_string($fallback) && trim($fallback) !== '') {
                            $candidate = $fallback;
                            break;
                        }
                    }
                }
                return is_string($candidate) ? $candidate : '';
            }
        }

        return $value;
    }

    public function getTranslation(string $key, ?string $locale = null, bool $useFallbackLocale = true): mixed
    {
        $locale = $locale ?: app()->getLocale();
        $translations = $this->getTranslations($key);

        if (isset($translations[$locale]) && is_string($translations[$locale]) && trim($translations[$locale]) !== '') {
            return $translations[$locale];
        }

        if ($useFallbackLocale) {
            foreach (array_unique(array_filter([config('app.fallback_locale', 'en'), 'en', 'es'])) as $fallback) {
                if (isset($translations[$fallback]) && is_string($translations[$fallback]) && trim($translations[$fallback]) !== '') {
                    return $translations[$fallback];
                }
            }
        }

        foreach ($translations as $value) {
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return '';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function blogPosts(): HasMany
    {
        return $this->hasMany(BlogPost::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
