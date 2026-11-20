<?php

namespace App\Filament\Resources\MapsCalculator\QuoteResource\Pages;

use App\Filament\Resources\MapsCalculator\QuoteResource;
use Filament\Resources\Pages\EditRecord;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Tables\Actions\ViewAction::make(),
            \Filament\Tables\Actions\DeleteAction::make(),
        ];
    }
}