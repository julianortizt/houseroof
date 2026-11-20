<?php

namespace App\Filament\Resources\MapsCalculator\ClientResource\Pages;

use App\Filament\Resources\MapsCalculator\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label(__('mapscalculator.new_client')),
        ];
    }
}
