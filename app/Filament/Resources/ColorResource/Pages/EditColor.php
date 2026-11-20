<?php

namespace App\Filament\Resources\ColorResource\Pages;

use App\Filament\Resources\ColorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditColor extends EditRecord
{
    protected static string $resource = ColorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->colorImage = $data['color_image'] ?? null;
        unset($data['color_image']);

        $record->update($data);
        return $record;
    }

    protected function afterSave(): void
    {
        $path = $this->colorImage ?? null;
        if ($path) {
            $this->record->clearMediaCollection('default');
            $uploadDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($uploadDisk->exists($path)) {
                $this->record->addMediaFromDisk($path, 'public')->toMediaCollection('default');
            }
        }
    }
}
