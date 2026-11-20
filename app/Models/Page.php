<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;
use App\Models\Traits\AutoTranslates;

class Page extends Model implements HasMedia
{
    use HasTranslations, InteractsWithMedia, SoftDeletes, AutoTranslates;

    public $guarded = [];
    public array $translatable = ['title', 'content'];

    /**
     * Return title as string for Filament forms.
     */
    public function getTitleAttribute(): string
    {
        $val = $this->attributes['title'] ?? '';
        if (is_array($val)) {
            return $val['en'] ?? $val['es'] ?? reset($val) ?? '';
        }
        $decoded = json_decode((string) $val, true);
        if (is_array($decoded)) {
            return $decoded['en'] ?? $decoded['es'] ?? reset($decoded) ?? '';
        }
        return (string) $val;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
