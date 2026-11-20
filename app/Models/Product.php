<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use App\Models\Traits\AutoTranslates;

class Product extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia, SoftDeletes, AutoTranslates;

    public $guarded = [];
    public array $translatable = ['name', 'description', 'short_description'];

    /**
     * Override getAttribute to return translation for current locale.
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->translatable) && is_string($value)) {
            // Try to decode as JSON and return current locale
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $locale = app()->getLocale();
                // Prefer a non-empty value for the active locale, then en/es, then any non-empty.
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
            $fallbacks = [];
            if (method_exists($this, 'getFallbackLocale')) {
                $fb = $this->getFallbackLocale();
                if ($fb) {
                    $fallbacks[] = $fb;
                }
            }
            $fallbacks[] = config('app.fallback_locale', 'en');
            $fallbacks[] = 'en';
            $fallbacks[] = 'es';
            foreach (array_unique($fallbacks) as $fallback) {
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

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_coming_soon' => 'boolean',
    ];

    /**
     * Check if the product is in 'Coming Soon' mode.
     * When coming_soon is true, the product is visible but cannot be purchased.
     */
    public function isComingSoon(): bool
    {
        return $this->is_coming_soon;
    }

    /**
     * Check if the product is available for purchase.
     * A product is purchasable when it's active, in stock, and NOT coming soon.
     */
    public function isPurchasable(): bool
    {
        return $this->is_active && $this->stock > 0 && !$this->is_coming_soon;
    }

    /**
     * Get the localized 'Coming Soon' badge text.
     */
    public function getComingSoonLabel(): string
    {
        return app()->getLocale() === 'en' ? 'Coming Soon' : 'Próximamente';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function getEffectivePrice(): float
    {
        return $this->price;
    }

    public function getCompareAtPriceFormatted(): ?string
    {
        return $this->compare_at_price ? number_format($this->compare_at_price, 2, ',', '.') . ' €' : null;
    }

    public function getPriceFormatted(): string
    {
        return number_format($this->price, 2, ',', '.') . ' €';
    }

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

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Get the main image URL for this product.
     * Checks Spatie Media Library first, then falls back to image_path column.
     */
    public function getMainImageUrl(): ?string
    {
        // 1. Try Spatie Media Library
        $mediaUrl = $this->getFirstMediaUrl('default');
        if ($mediaUrl) {
            return $mediaUrl;
        }

        // 2. Fallback to image_path column (used by Filament admin)
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        return null;
    }

    /**
     * Get a specific conversion URL for this product.
     * Falls back to the main image if conversion doesn't exist.
     */
    public function getMediaUrl(string $collection = 'default', string $conversion = ''): ?string
    {
        if ($conversion) {
            $url = $this->getFirstMediaUrl($collection, $conversion);
            if ($url) {
                return $url;
            }
        }

        return $this->getMainImageUrl();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getMainImageUrl();
    }

    // Virtual toggle: is compare_at_price being used?
    public function getHasComparePriceAttribute(): bool
    {
        return !is_null($this->compare_at_price) && $this->compare_at_price > 0;
    }

    // Virtual toggle: are dimensions being used?
    public function getHasDimensionsAttribute(): bool
    {
        return !is_null($this->weight_kg) || !is_null($this->width_cm) || !is_null($this->height_cm) || !is_null($this->depth_cm);
    }
}
