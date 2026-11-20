<?php

namespace App\Filament\Resources\ContentBlockResource\Pages;

use App\Filament\Resources\ContentBlockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditContentBlock extends EditRecord
{
    protected static string $resource = ContentBlockResource::class;

    protected ?string $previousValue = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($record instanceof \App\Models\ContentBlock) {
            $this->previousValue = (string) $record->value;
        }
        return parent::handleRecordUpdate($record, $data);
    }

    protected function afterSave(): void
    {
        if ($this->record instanceof \App\Models\ContentBlock) {
            $this->record->propagateTranslations($this->previousValue);
        }
        $this->previousValue = null;
    }
}
