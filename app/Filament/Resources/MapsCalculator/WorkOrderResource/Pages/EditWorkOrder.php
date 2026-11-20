<?php

namespace App\Filament\Resources\MapsCalculator\WorkOrderResource\Pages;

use App\Filament\Resources\MapsCalculator\WorkOrderResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function afterSave(): void
    {
        DB::transaction(function (): void {
            $this->record->project()->update([
                'assigned_to' => $this->record->assigned_to,
            ]);
        });
    }
}