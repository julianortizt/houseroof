<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->categoryImage = $data['category_image'] ?? null;
        unset($data['category_image']);

        $record->update($data);
        return $record;
    }

    protected function afterSave(): void
    {
        $path = $this->categoryImage ?? null;
        if ($path) {
            $this->record->clearMediaCollection('default');
            $uploadDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($uploadDisk->exists($path)) {
                $this->record->addMediaFromDisk($path, 'public')->toMediaCollection('default');
            }
        }
    }
}
