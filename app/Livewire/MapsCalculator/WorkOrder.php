<?php

namespace App\Livewire\MapsCalculator;

use App\Models\MapsCalculator\WorkLog;
use App\Models\MapsCalculator\WorkOrder as WorkOrderModel;
use App\Models\MapsCalculator\Project;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\InventoryReservation;
use App\Services\InventoryService;
use App\Models\User;
use App\Services\PlatformModules;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WorkOrder extends Component
{
    use WithFileUploads;

    public WorkOrderModel $workOrder;
    public Project $project;

    public string $newLogDescription = '';
    public string $newLogType = 'general';
    public ?string $assignedTo = null;
    public string $laborCost = '0.00';
    public string $otherCost = '0.00';
    public string $costNotes = '';
    public $newLogPhoto;
    public bool $showLogModal = false;

    public array $logs = [];
    public array $inventoryMovements = [];
    public array $inventoryReservations = [];
    public array $statusOptions = [];
    public ?string $materialItemId = null;
    public ?string $materialLocationId = null;
    public string $materialQuantity = '';
    public string $materialNotes = '';
    public string $reservationItemId = '';
    public string $reservationLocationId = '';
    public string $reservationQuantity = '';
    public string $reservationNotes = '';

    protected $listeners = [
        'logSaved' => 'loadLogs',
        'logDeleted' => 'loadLogs',
    ];

    // Must be the model: the unqualified name would resolve to this component.
    public function mount(WorkOrderModel $workOrder, Project $project): void
    {
        $this->requireWorkOrdersModule();
        abort_unless((int) $workOrder->project_id === (int) $project->id, 404);
        Gate::authorize('view', $workOrder);

        $this->workOrder = $workOrder;
        $this->project = $project;
        $this->assignedTo = $workOrder->assigned_to === null ? null : (string) $workOrder->assigned_to;
        $this->laborCost = (string) $workOrder->labor_cost;
        $this->otherCost = (string) $workOrder->other_cost;
        $this->costNotes = (string) ($workOrder->cost_notes ?? '');
        $this->statusOptions = [
            'agendada' => __('mapscalculator.status_agendada'),
            'en_preparacion' => __('mapscalculator.status_en_preparacion'),
            'en_ejecucion' => __('mapscalculator.status_en_ejecucion'),
            'finalizada' => __('mapscalculator.status_finalizada'),
            'entregada' => __('mapscalculator.status_entregada'),
            'cancelada' => __('mapscalculator.status_cancelada'),
        ];
        $this->loadLogs();
        $this->loadInventoryMovements();
        $this->loadInventoryReservations();
    }

    public function loadLogs(): void
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('view', $this->workOrder);
        $this->logs = $this->workOrder->logs()->with('creator')->get()->toArray();
    }

    public function inventoryEnabled(): bool
    {
        $modules = app(PlatformModules::class);

        return $modules->enabled('inventory') && $modules->enabled('work_orders');
    }

    public function availableInventoryItems(): array
    {
        if (! $this->inventoryEnabled()) {
            return [];
        }

        return InventoryItem::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'sku', 'name', 'unit'])
            ->toArray();
    }

    public function availableInventoryLocations(): array
    {
        if (! $this->inventoryEnabled()) {
            return [];
        }

        return InventoryLocation::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code'])
            ->toArray();
    }

    public function loadInventoryMovements(): void
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('view', $this->workOrder);

        if (! $this->inventoryEnabled()) {
            $this->inventoryMovements = [];

            return;
        }

        $this->inventoryMovements = $this->workOrder->inventoryMovements()
            ->with(['item', 'location', 'creator'])
            ->get()
            ->toArray();
    }

    public function loadInventoryReservations(): void
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('view', $this->workOrder);

        if (! $this->inventoryEnabled()) {
            $this->inventoryReservations = [];

            return;
        }

        $this->inventoryReservations = $this->workOrder->inventoryReservations()
            ->with(['item', 'location', 'creator', 'releaser'])
            ->get()
            ->toArray();
    }

    public function reserveMaterial(): void
    {
        $this->requireWorkOrdersModule();
        abort_unless($this->inventoryEnabled(), 404, 'Inventory reservations are unavailable.');
        Gate::authorize('update', $this->workOrder);

        app(InventoryService::class)->reserveForWorkOrder($this->workOrder, [
            'inventory_item_id' => $this->reservationItemId,
            'inventory_location_id' => $this->reservationLocationId,
            'quantity' => $this->reservationQuantity,
            'notes' => $this->reservationNotes,
        ]);

        $this->reservationQuantity = '';
        $this->reservationNotes = '';
        $this->loadInventoryReservations();
        $this->dispatch('notify', message: __('mapscalculator.material_reservation_created'), type: 'success');
    }

    public function releaseMaterialReservation(int $reservationId): void
    {
        $this->requireWorkOrdersModule();
        abort_unless($this->inventoryEnabled(), 404, 'Inventory reservations are unavailable.');
        Gate::authorize('update', $this->workOrder);

        $reservation = $this->workOrder->inventoryReservations()->findOrFail($reservationId);
        app(InventoryService::class)->releaseReservation($reservation);
        $this->loadInventoryReservations();
        $this->dispatch('notify', message: __('mapscalculator.material_reservation_released'), type: 'success');
    }

    public function consumeMaterial(): void
    {
        $this->requireWorkOrdersModule();
        abort_unless($this->inventoryEnabled(), 404, 'Inventory consumption is unavailable.');
        Gate::authorize('update', $this->workOrder);

        app(InventoryService::class)->consumeForWorkOrder($this->workOrder, [
            'inventory_item_id' => $this->materialItemId,
            'inventory_location_id' => $this->materialLocationId,
            'quantity' => $this->materialQuantity,
            'notes' => $this->materialNotes,
        ]);

        $this->materialQuantity = '';
        $this->materialNotes = '';
        $this->loadInventoryMovements();
        $this->loadInventoryReservations();
        $this->dispatch('notify', message: __('mapscalculator.material_consumption_recorded'), type: 'success');
    }

    public function updateStatus(string $status): void
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('update', $this->workOrder);

        DB::transaction(function () use ($status): void {
            $workOrder = WorkOrderModel::query()->lockForUpdate()->findOrFail($this->workOrder->id);
            Validator::make(['status' => $status], [
                'status' => ['required', Rule::in(array_keys($this->statusOptions))],
            ])->validate();

            if (! $workOrder->transitionTo($status)) {
                throw ValidationException::withMessages([
                    'status' => __('mapscalculator.invalid_work_order_transition'),
                ]);
            }

            $workOrder->project()->update(['status' => $status]);
            $this->workOrder = $workOrder->fresh();
            $this->project = $workOrder->project;
        });

        $this->dispatch('notify', message: __('mapscalculator.status_updated', ['status' => $this->statusOptions[$status]]), type: 'success');
    }

    public function updateAssignment(): void
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('create', WorkOrderModel::class);

        Validator::make(['assignedTo' => $this->assignedTo], [
            'assignedTo' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->whereIn('mapscalculator_role', ['admin', 'comercial', 'tecnico']),
            ],
        ])->validate();

        $this->workOrder->update(['assigned_to' => $this->assignedTo]);
        $this->project->update(['assigned_to' => $this->assignedTo]);
        $this->dispatch('notify', message: __('mapscalculator.assignment_updated'), type: 'success');
    }

    public function updateDates(): void
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('update', $this->workOrder);

        $this->validate([
            'workOrder.scheduled_start' => 'nullable|date',
            'workOrder.scheduled_end' => 'nullable|date|after_or_equal:workOrder.scheduled_start',
            'workOrder.actual_start' => 'nullable|date',
            'workOrder.actual_end' => 'nullable|date|after_or_equal:workOrder.actual_start',
        ]);
        $this->workOrder->save();
        $this->dispatch('notify', message: __('mapscalculator.dates_updated'), type: 'success');
    }

    public function updateCosts(): void
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('update', $this->workOrder);
        abort_unless(
            auth()->user()->isMapscalculatorAdmin() || auth()->user()->isComercial(),
            403
        );

        $validated = $this->validate([
            'laborCost' => 'required|decimal:0,2|min:0|max:9999999999.99',
            'otherCost' => 'required|decimal:0,2|min:0|max:9999999999.99',
            'costNotes' => 'nullable|string|max:5000',
        ]);

        $this->workOrder->update([
            'labor_cost' => $validated['laborCost'],
            'other_cost' => $validated['otherCost'],
            'cost_notes' => $validated['costNotes'] ?: null,
        ]);
        $this->workOrder = $this->workOrder->fresh();
        $this->dispatch('notify', message: __('mapscalculator.costs_updated'), type: 'success');
    }

    public function addLog(): void
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('update', $this->workOrder);

        $this->validate([
            'newLogDescription' => 'required|string|max:5000',
            'newLogType' => 'required|in:antes,durante,despues,general',
            'newLogPhoto' => 'nullable|image|max:5120',
        ]);

        $photoPath = null;
        if ($this->newLogPhoto) {
            $photoPath = $this->newLogPhoto->store('mapscalculator/work-logs', 'public');
        }

        WorkLog::create([
            'work_order_id' => $this->workOrder->id,
            'created_by' => auth()->id(),
            'date' => now()->toDateString(),
            'description' => $this->newLogDescription,
            'photo_path' => $photoPath,
            'type' => $this->newLogType,
        ]);

        $this->newLogDescription = '';
        $this->newLogPhoto = null;
        $this->newLogType = 'general';
        $this->showLogModal = false;
        $this->loadLogs();
        $this->dispatch('notify', message: __('mapscalculator.log_added'), type: 'success');
    }

    public function deleteLog(int $logId): void
    {
        $this->requireWorkOrdersModule();

        $log = WorkLog::find($logId);
        if ($log) {
            Gate::authorize('deleteLog', $log);
            if ($log->photo_path) {
                Storage::disk('public')->delete($log->photo_path);
            }
            $log->delete();
            $this->loadLogs();
            $this->dispatch('notify', message: __('mapscalculator.log_deleted'), type: 'success');
        }
    }

    public function getStatusColor(): string
    {
        return match ($this->workOrder->status) {
            'agendada' => 'info',
            'en_preparacion' => 'warning',
            'en_ejecucion' => 'primary',
            'finalizada' => 'success',
            'entregada' => 'gray',
            'cancelada' => 'danger',
            default => 'gray',
        };
    }

    public function getProgress(): int
    {
        return $this->workOrder->progress;
    }

    public function availableStatuses(): array
    {
        return array_filter(
            $this->statusOptions,
            fn (string $label, string $status): bool => $this->workOrder->canTransitionTo($status),
            ARRAY_FILTER_USE_BOTH,
        );
    }

    private function requireWorkOrdersModule(): void
    {
        abort_unless(app(PlatformModules::class)->enabled('work_orders'), 404);
    }

    public function render()
    {
        $this->requireWorkOrdersModule();
        Gate::authorize('view', $this->workOrder);

        return view('livewire.mapscalculator.work-order');
    }
}