<?php

namespace App\Models\Simulator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class FinishVariant extends Model
{
    protected $table = 'simulator_finish_variants';

    protected $fillable = [
        'simulator_finish_id',
        'name',
        'base_color_hex',
        'texture_color_path',
        'texture_normal_path',
        'texture_roughness_path',
        'preview_image_path',
        'chip_density',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'chip_density' => 'integer',
        ];
    }

    public function finish(): BelongsTo
    {
        return $this->belongsTo(Finish::class, 'simulator_finish_id');
    }

    private function versionedUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        $disk = Storage::disk('public');
        $url = $disk->url($path);
        if ($disk->exists($path)) {
            $url .= (str_contains($url, '?') ? '&' : '?').'v='.$disk->lastModified($path);
        }

        return $url;
    }

    public function getTextureColorUrlAttribute(): ?string
    {
        return $this->versionedUrl($this->texture_color_path);
    }

    public function getTextureNormalUrlAttribute(): ?string
    {
        return $this->versionedUrl($this->texture_normal_path);
    }

    public function getTextureRoughnessUrlAttribute(): ?string
    {
        return $this->versionedUrl($this->texture_roughness_path);
    }

    public function getPreviewImageUrlAttribute(): ?string
    {
        return $this->versionedUrl($this->preview_image_path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
