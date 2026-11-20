<?php

namespace App\Models\Simulator;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RoomTemplate extends Model
{
    protected $table = 'simulator_room_templates';    protected $fillable = [
        'name', 'slug', 'model_glb_path', 'photo_url', 'preview_image_path',
        'configurable_meshes', 'default_area_m2', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'configurable_meshes' => 'array',
            'default_area_m2' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getModelGlbUrlAttribute(): ?string
    {
        return $this->model_glb_path ? Storage::url($this->model_glb_path) : null;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->attributes['photo_url'] ?? null) {
            return $this->attributes['photo_url'];
        }
        return $this->preview_image_path ? Storage::url($this->preview_image_path) : null;
    }

    public function getPreviewImageUrlAttribute(): ?string
    {
        return $this->preview_image_path ? Storage::url($this->preview_image_path) : null;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
