<?php

namespace App\Filament\Resources\MapsCalculator\WorkOrderResource\Pages;

use App\Filament\Resources\MapsCalculator\WorkOrderResource;
use App\Models\MapsCalculator\Project;
use App\Models\MapsCalculator\WorkOrder;
use App\Models\Quote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data): WorkOrder {
            $project = Project::query()->lockForUpdate()->findOrFail($data['project_id']);
            $quote = Quote::query()
                ->whereKey($data['quote_id'])
                ->where('project_id', $project->id)
                ->where('status', 'aprobado')
                ->first();

            if (! $quote || $project->workOrder()->exists()) {
                throw ValidationException::withMessages([
                    'quote_id' => __('mapscalculator.work_order_requires_approved_quote'),
                ]);
            }

            $workOrder = WorkOrder::create([...$data, 'quote_id' => $quote->id]);
            $project->update([
                'status' => 'agendada',
                'assigned_to' => $data['assigned_to'] ?? null,
            ]);

            return $workOrder;
        });
    }
}