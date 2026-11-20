<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'image' => $this->getMainImageUrl(),
            'gallery' => $this->getMedia('gallery')->map(
                fn ($m) => $m->getFullUrl()
            )->values()->all(),
            'price' => $this->price,
            'price_label' => $this->price ? $this->getPriceFormatted() : null,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ]),
            'color_hex' => $this->color_hex ?? null,
            'specs' => $this->specs ?? null,
            'in_stock' => $this->stock > 0,
            'is_featured' => (bool) $this->is_featured,
        ];
    }
}
