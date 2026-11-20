<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\InventoryReservation;
use App\Models\MapsCalculator\WorkOrder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function recordMovement(array $data): InventoryMovement
    {
        abort_unless(app(PlatformModules::class)->enabled('inventory'), 404);

        $actor = auth()->user();
        $isAdministrator = $actor !== null
            && ($actor->hasRole(['super_admin', 'admin', 'editor', 'manager', 'site_admin'])
                || (filled(config('platform.developer_email')) && $actor->email === config('platform.developer_email')));
        abort_unless($isAdministrator, 403);

        $validated = Validator::make($data, [
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'inventory_location_id' => ['required', 'integer', 'exists:inventory_locations,id'],
            'movement_type' => ['required', 'in:receipt,issue,adjustment'],
            'quantity' => ['required', 'numeric'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'unit_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.9999', 'decimal:0,4'],
        ])->validate();

        $type = $validated['movement_type'];
        $this->validateMovementQuantity($validated['quantity'], $type, $validated['notes'] ?? null);

        return DB::transaction(fn (): InventoryMovement => $this->createMovement($validated, $type));
    }

    public function consumeForWorkOrder(WorkOrder $workOrder, array $data): InventoryMovement
    {
        abort_unless(app(PlatformModules::class)->enabled('inventory'), 404, 'Inventory module is disabled.');
        abort_unless(app(PlatformModules::class)->enabled('work_orders'), 404, 'Work orders module is disabled.');
        Gate::authorize('update', $workOrder);

        $validated = Validator::make($data, [
            'inventory_item_id' => ['required', 'integer', Rule::exists('inventory_items', 'id')->where('is_active', true)],
            'inventory_location_id' => ['required', 'integer', Rule::exists('inventory_locations', 'id')->where('is_active', true)],
            'quantity' => ['required', 'numeric'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();
        $this->validateMovementQuantity($validated['quantity'], 'issue');

        return DB::transaction(function () use ($workOrder, $validated): InventoryMovement {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            Gate::authorize('update', $lockedWorkOrder);

            if (! in_array($lockedWorkOrder->status, ['en_preparacion', 'en_ejecucion'], true)) {
                throw ValidationException::withMessages([
                    'work_order' => __('mapscalculator.materials_require_active_work'),
                ]);
            }

            $movement = $this->createMovement(
                [
                    ...$validated,
                    'reference' => 'WO-' . $lockedWorkOrder->id,
                    'notes' => $validated['notes'] ?? null,
                ],
                'issue',
                $lockedWorkOrder->id,
            );

            $this->consumeReservations(
                $lockedWorkOrder->id,
                (int) $validated['inventory_item_id'],
                (int) $validated['inventory_location_id'],
                $this->toMilliUnits($validated['quantity']),
            );

            return $movement;
        });
    }

    public function reserveForWorkOrder(WorkOrder $workOrder, array $data): InventoryReservation
    {
        abort_unless(app(PlatformModules::class)->enabled('inventory'), 404, 'Inventory module is disabled.');
        abort_unless(app(PlatformModules::class)->enabled('work_orders'), 404, 'Work orders module is disabled.');
        Gate::authorize('update', $workOrder);

        $validated = Validator::make($data, [
            'inventory_item_id' => ['required', 'integer', Rule::exists('inventory_items', 'id')->where('is_active', true)],
            'inventory_location_id' => ['required', 'integer', Rule::exists('inventory_locations', 'id')->where('is_active', true)],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();
        $requestedMilliUnits = $this->toMilliUnits($validated['quantity']);
        if ($requestedMilliUnits <= 0) {
            throw ValidationException::withMessages([
                'quantity' => __('admin.La cantidad debe ser mayor que cero.'),
            ]);
        }

        return DB::transaction(function () use ($workOrder, $validated, $requestedMilliUnits): InventoryReservation {
            $lockedWorkOrder = WorkOrder::query()->lockForUpdate()->findOrFail($workOrder->id);
            Gate::authorize('update', $lockedWorkOrder);
            if (! in_array($lockedWorkOrder->status, ['en_preparacion', 'en_ejecucion'], true)) {
                throw ValidationException::withMessages([
                    'work_order' => __('mapscalculator.materials_require_active_work'),
                ]);
            }

            $item = InventoryItem::query()->lockForUpdate()->findOrFail($validated['inventory_item_id']);
            $location = InventoryLocation::query()->lockForUpdate()->findOrFail($validated['inventory_location_id']);
            if (! $item->is_active || ! $location->is_active) {
                throw ValidationException::withMessages([
                    'inventory_item_id' => __('admin.Se requieren un artículo y una ubicación activos para registrar movimientos.'),
                ]);
            }

            $onHand = $this->toMilliUnits($item->movements()
                ->where('inventory_location_id', $location->id)
                ->sum('quantity_change') ?: 0);
            $reserved = $this->toMilliUnits(InventoryReservation::query()
                ->where('inventory_item_id', $item->id)
                ->where('inventory_location_id', $location->id)
                ->where('status', 'active')
                ->selectRaw('COALESCE(SUM(quantity - consumed_quantity), 0) as remaining')
                ->value('remaining') ?: 0);

            if ($requestedMilliUnits > $onHand - $reserved) {
                throw ValidationException::withMessages([
                    'quantity' => __('admin.La cantidad supera las existencias disponibles después de reservas.'),
                ]);
            }

            return InventoryReservation::create([
                'work_order_id' => $lockedWorkOrder->id,
                'inventory_item_id' => $item->id,
                'inventory_location_id' => $location->id,
                'quantity' => $this->fromMilliUnits($requestedMilliUnits),
                'consumed_quantity' => '0.000',
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });
    }

    public function releaseReservation(InventoryReservation $reservation): void
    {
        abort_unless(app(PlatformModules::class)->enabled('inventory'), 404, 'Inventory module is disabled.');
        abort_unless(app(PlatformModules::class)->enabled('work_orders'), 404, 'Work orders module is disabled.');
        Gate::authorize('update', $reservation->workOrder);

        DB::transaction(function () use ($reservation): void {
            $locked = InventoryReservation::query()->lockForUpdate()->findOrFail($reservation->id);
            Gate::authorize('update', $locked->workOrder);

            if ($locked->status !== 'active') {
                throw ValidationException::withMessages([
                    'reservation' => __('mapscalculator.reservation_not_active'),
                ]);
            }

            $locked->update([
                'status' => 'released',
                'released_by' => auth()->id(),
                'released_at' => now(),
            ]);
        });
    }

    private function validateMovementQuantity(mixed $quantity, string $type, ?string $notes = null): void
    {
        $quantityMilliUnits = $this->toMilliUnits($quantity);

        if ($quantityMilliUnits === 0 || ($type !== 'adjustment' && $quantityMilliUnits < 0)) {
            throw ValidationException::withMessages([
                'quantity' => __('admin.Las entradas y salidas requieren una cantidad positiva; los ajustes requieren una cantidad distinta de cero con signo.'),
            ]);
        }

        if ($type === 'adjustment' && blank($notes)) {
            throw ValidationException::withMessages([
                'notes' => __('admin.Se requiere un motivo para cada ajuste de existencias.'),
            ]);
        }
    }

    private function createMovement(array $validated, string $type, ?int $workOrderId = null): InventoryMovement
    {
        $quantityMilliUnits = $this->toMilliUnits($validated['quantity']);
        $deltaMilliUnits = match ($type) {
            'receipt' => $quantityMilliUnits,
            'issue' => -$quantityMilliUnits,
            'adjustment' => $quantityMilliUnits,
        };
        $item = InventoryItem::query()->lockForUpdate()->findOrFail($validated['inventory_item_id']);
        $location = InventoryLocation::query()->lockForUpdate()->findOrFail($validated['inventory_location_id']);

        if (! $item->is_active || ! $location->is_active) {
            throw ValidationException::withMessages([
                'inventory_item_id' => __('admin.Se requieren un artículo y una ubicación activos para registrar movimientos.'),
            ]);
        }

        $currentQuantity = $item->movements()
            ->where('inventory_location_id', $location->id)
            ->sum('quantity_change');
        $currentMilliUnits = $this->toMilliUnits($currentQuantity ?: 0);
        $balanceMilliUnits = $currentMilliUnits + $deltaMilliUnits;

        if ($balanceMilliUnits < 0) {
            throw ValidationException::withMessages([
                'quantity' => __('admin.Este movimiento dejaría las existencias en negativo.'),
            ]);
        }

        if ($deltaMilliUnits < 0) {
            $reservations = InventoryReservation::query()
                ->where('inventory_item_id', $item->id)
                ->where('inventory_location_id', $location->id)
                ->where('status', 'active')
                ->when($workOrderId, fn ($query) => $query->where('work_order_id', '!=', $workOrderId))
                ->selectRaw('COALESCE(SUM(quantity - consumed_quantity), 0) as remaining')
                ->value('remaining');
            $reservedMilliUnits = $this->toMilliUnits($reservations ?: 0);

            if (-$deltaMilliUnits > $currentMilliUnits - $reservedMilliUnits) {
                throw ValidationException::withMessages([
                    'quantity' => __('admin.Este movimiento consumiría existencias reservadas para órdenes de trabajo.'),
                ]);
            }
        }

        $cost = app(InventoryValuationService::class)->applyMovementCost(
            $item,
            $location,
            $deltaMilliUnits,
            $currentMilliUnits,
            $validated['unit_cost'] ?? null,
        );

        return InventoryMovement::create([
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'work_order_id' => $workOrderId,
            'movement_type' => $type,
            'quantity_change' => $this->fromMilliUnits($deltaMilliUnits),
            'balance_after' => $this->fromMilliUnits($balanceMilliUnits),
            ...$cost,
            'reference' => $validated['reference'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
            'created_at' => now(),
        ]);
    }

    private function consumeReservations(int $workOrderId, int $itemId, int $locationId, int $quantityMilliUnits): void
    {
        $remainingToConsume = $quantityMilliUnits;
        $reservations = InventoryReservation::query()
            ->where('work_order_id', $workOrderId)
            ->where('inventory_item_id', $itemId)
            ->where('inventory_location_id', $locationId)
            ->where('status', 'active')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($reservations as $reservation) {
            if ($remainingToConsume <= 0) {
                break;
            }

            $remainingReserved = $this->toMilliUnits($reservation->remaining_quantity);
            $consumedNow = min($remainingReserved, $remainingToConsume);
            $totalConsumed = $this->toMilliUnits($reservation->consumed_quantity) + $consumedNow;
            $reservation->update([
                'consumed_quantity' => $this->fromMilliUnits($totalConsumed),
                'status' => $totalConsumed >= $this->toMilliUnits($reservation->quantity) ? 'consumed' : 'active',
            ]);
            $remainingToConsume -= $consumedNow;
        }
    }

    private function toMilliUnits(mixed $quantity): int
    {
        if (! is_numeric($quantity) || ! is_finite((float) $quantity) || abs((float) $quantity) > 99_999_999_999.999) {
            throw ValidationException::withMessages([
                'quantity' => __('admin.La cantidad debe ser un número finito dentro del rango admitido.'),
            ]);
        }

        $scaled = (float) $quantity * 1000;
        $rounded = round($scaled);

        if (abs($scaled - $rounded) > 0.000001) {
            throw ValidationException::withMessages([
                'quantity' => __('admin.La cantidad admite hasta tres decimales.'),
            ]);
        }

        return (int) $rounded;
    }

    private function fromMilliUnits(int $quantity): string
    {
        return number_format($quantity / 1000, 3, '.', '');
    }
}
