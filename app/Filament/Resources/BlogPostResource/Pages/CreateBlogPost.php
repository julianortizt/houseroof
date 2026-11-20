<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBlogPost extends CreateRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $this->mediaData = [
            'featured_image' => $data['featured_image'] ?? null,
            'gallery_images' => $data['gallery_images'] ?? null,
        ];
        unset($data['featured_image'], $data['gallery_images']);

        return static::getModel()::create($data);
    }

    protected function afterCreate(): void
    {
        foreach (['featured_image', 'gallery_images'] as $field) {
            $images = $this->mediaData[$field] ?? null;
            if (empty($images)) continue;
            $collection = $field === 'featured_image' ? 'default' : 'gallery';
            foreach ((array) $images as $path) {
                $uploadDisk = \Illuminate\Support\Facades\Storage::disk('public');
                if ($uploadDisk->exists($path)) {
                    $this->record->addMediaFromDisk($path, 'public')->toMediaCollection($collection);
                }
            }
        }
    }
}
