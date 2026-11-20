<?php

namespace App\Filament\Resources\InventoryPurchaseOrderResource\Pages;

use App\Filament\Resources\InventoryPurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryPurchaseOrders extends ListRecords
{
    protected static string $resource = InventoryPurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
