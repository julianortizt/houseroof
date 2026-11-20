<?php

namespace App\Filament\Resources\InventoryPurchaseOrderResource\Pages;

use App\Filament\Resources\InventoryPurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryPurchaseOrder extends CreateRecord
{
    protected static string $resource = InventoryPurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['number'] = InventoryPurchaseOrderResource::generateNumber();
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();

        return $data;
    }
}
