<?php

namespace App\Filament\Resources\SimulatorFinishResource\Pages;

use App\Filament\Resources\SimulatorFinishResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSimulatorFinishes extends ListRecords
{
    protected static string $resource = SimulatorFinishResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
