<?php

namespace App\Filament\Resources\SimulatorRoomTemplateResource\Pages;

use App\Filament\Resources\SimulatorRoomTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSimulatorRoomTemplate extends EditRecord
{
    protected static string $resource = SimulatorRoomTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
