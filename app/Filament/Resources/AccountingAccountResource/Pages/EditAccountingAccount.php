<?php

namespace App\Filament\Resources\AccountingAccountResource\Pages;

use App\Filament\Resources\AccountingAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccountingAccount extends EditRecord
{
    protected static string $resource = AccountingAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->visible(fn (): bool => ! $this->record->lines()->exists())];
    }
}
