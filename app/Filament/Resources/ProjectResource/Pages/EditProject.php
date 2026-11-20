<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\Action::make('viewFrontend')
                ->label(__('admin.Ver en el sitio'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn () => route('projects.show', $this->record->slug))
                ->openUrlInNewTab(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $this->mediaPaths = $data['project_images'] ?? null;
        unset($data['project_images']);

        $record->update($data);
        return $record;
    }

    protected function afterSave(): void
    {
        $paths = $this->mediaPaths;
        if ($paths === null) {
            return;
        }

        $this->record->clearMediaCollection('default');
        foreach ((array) $paths as $path) {
            $uploadDisk = \Illuminate\Support\Facades\Storage::disk('public');
            if ($uploadDisk->exists($path)) {
                $this->record->addMediaFromDisk($path, 'public')->toMediaCollection('default');
            }
        }
    }
}
