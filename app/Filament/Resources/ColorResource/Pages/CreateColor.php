<?php

namespace App\Filament\Resources\ColorResource\Pages;

use App\Filament\Resources\ColorResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateColor extends CreateRecord
{
    protected static string $resource = ColorResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $this->colorImage = $data['color_image'] ?? null;
        unset($data['color_image']);

        return static::getModel()::create($data);
    }

    protected function afterCreate(): void
    {
        $path = $this->colorImage ?? null;
        if ($path) {
            $uploadDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($uploadDisk->exists($path)) {
                $this->record->addMediaFromDisk($path, 'public')->toMediaCollection('default');
            }
        }
    }
}
