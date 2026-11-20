<?php

namespace App\Filament\Resources\InventoryValuationResource\Pages;

use App\Filament\Resources\InventoryValuationResource;
use App\Services\InventoryValuationService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListInventoryValuations extends ListRecords
{
    protected static string $resource = InventoryValuationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('enable_costing')
                ->label('Activar coste medio ponderado')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => ! app(InventoryValuationService::class)->isCostingEnabled())
                ->action(function (): void {
                    app(InventoryValuationService::class)->enableCosting();
                    Notification::make()->title('Valoración por coste medio ponderado activada')->success()->send();
                }),
        ];
    }
}
