<?php

namespace App\Filament\Resources\BlogPostResource\Pages;

use App\Filament\Resources\BlogPostResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBlogPost extends EditRecord
{
    protected static string $resource = BlogPostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('viewFrontend')
                ->label(__('admin.Ver en el sitio'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => route('blog.show', $this->record->slug))
                ->openUrlInNewTab(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->mediaData = [
            'featured_image' => $data['featured_image'] ?? null,
            'gallery_images' => $data['gallery_images'] ?? null,
        ];
        unset($data['featured_image'], $data['gallery_images']);

        $record->update($data);
        return $record;
    }

    protected function afterSave(): void
    {
        foreach (['featured_image', 'gallery_images'] as $field) {
            $images = $this->mediaData[$field] ?? null;
            if (empty($images)) continue;
            $collection = $field === 'featured_image' ? 'default' : 'gallery';
            $this->record->clearMediaCollection($collection);
            foreach ((array) $images as $path) {
                $uploadDisk = \Illuminate\Support\Facades\Storage::disk('public');
                if ($uploadDisk->exists($path)) {
                    $this->record->addMediaFromDisk($path, 'public')->toMediaCollection($collection);
                }
            }
        }
    }
}
