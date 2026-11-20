<?php

namespace App\Filament\Resources\MapsCalculator\ClientResource\Pages;

use App\Filament\Resources\MapsCalculator\ClientResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClient extends EditRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ficha')
                ->label(__('mapscalculator.client_sheet'))
                ->icon('heroicon-o-document-magnifying-glass')
                ->url(fn () => route('mapscalculator.client.show', $this->getRecord())),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
