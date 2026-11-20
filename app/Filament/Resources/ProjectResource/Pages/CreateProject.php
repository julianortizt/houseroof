<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $this->mediaPaths = $data['project_images'] ?? [];
        unset($data['project_images']);

        return static::getModel()::create($data);
    }

    protected function afterCreate(): void
    {
        foreach ((array) ($this->mediaPaths ?? []) as $path) {
            $uploadDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($uploadDisk->exists($path)) {
                $this->record->addMediaFromDisk($path, 'public')->toMediaCollection('default');
            }
        }
    }
}
