<?php

namespace App\Filament\Resources\AccountingAccountResource\Pages;

use App\Filament\Resources\AccountingAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccountingAccounts extends ListRecords
{
    protected static string $resource = AccountingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
