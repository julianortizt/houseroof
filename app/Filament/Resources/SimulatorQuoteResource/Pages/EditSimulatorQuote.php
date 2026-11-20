<?php

namespace App\Filament\Resources\SimulatorQuoteResource\Pages;

use App\Filament\Resources\SimulatorQuoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSimulatorQuote extends EditRecord
{
    protected static string $resource = SimulatorQuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
