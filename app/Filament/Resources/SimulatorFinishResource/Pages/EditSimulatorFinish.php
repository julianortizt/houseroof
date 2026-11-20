<?php

namespace App\Filament\Resources\SimulatorFinishResource\Pages;

use App\Filament\Resources\SimulatorFinishResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSimulatorFinish extends EditRecord
{
    protected static string $resource = SimulatorFinishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
