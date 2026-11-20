<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryValuationBalance;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InventoryValuationService
{
    public function isCostingEnabled(): bool
    {
        return (bool) Setting::get('inventory_costing_enabled', false);
    }

    public function setOpeningBalance(InventoryItem $item, array $data): InventoryValuationBalance
    {
        $this->authorizeInventoryAdministration();
        abort_unless(! $this->isCostingEnabled(), 409, 'Opening valuations are locked after costing is activated.');

        $validated = Validator::make($data, [
            'inventory_location_id' => ['required', 'integer', 'exists:inventory_locations,id'],
            'quantity' => ['required', 'numeric', 'gt:0', 'decimal:0,3'],
            'unit_cost' => ['required', 'numeric', 'min:0', 'max:999999999.9999', 'decimal:0,4'],
        ])->validate();

        return DB::transaction(function () use ($item, $validated): InventoryValuationBalance {
            $lockedItem = InventoryItem::query()->lockForUpdate()->findOrFail($item->id);
            $location = InventoryLocation::query()->lockForUpdate()->findOrFail($validated['inventory_location_id']);
            if (! $lockedItem->is_active || ! $location->is_active) {
                throw ValidationException::withMessages([
                    'inventory_location_id' => 'Selecciona un artículo y una ubicación activos.',
                ]);
            }

            if (InventoryValuationBalance::query()
                ->where('inventory_item_id', $lockedItem->id)
                ->where('inventory_location_id', $location->id)
                ->exists()) {
                throw ValidationException::withMessages([
                    'inventory_location_id' => 'Ya existe un saldo inicial valorado para este artículo y ubicación.',
                ]);
            }

            $actualQuantity = (float) $lockedItem->movements()
                ->where('inventory_location_id', $location->id)
                ->sum('quantity_change');
            $snapshotQuantity = (float) $validated['quantity'];
            if ((int) round($actualQuantity * 1000) !== (int) round($snapshotQuantity * 1000)) {
                throw ValidationException::withMessages([
                    'quantity' => 'La cantidad debe coincidir con el saldo actual del libro: ' . number_format($actualQuantity, 3, '.', '') . '.',
                ]);
            }

            $value = round($snapshotQuantity * (float) $validated['unit_cost'], 4);

            return InventoryValuationBalance::create([
                'inventory_item_id' => $lockedItem->id,
                'inventory_location_id' => $location->id,
                'quantity' => number_format($snapshotQuantity, 3, '.', ''),
                'inventory_value' => number_format($value, 4, '.', ''),
                'average_unit_cost' => number_format((float) $validated['unit_cost'], 4, '.', ''),
                'created_by' => auth('web')->id(),
            ]);
        });
    }

    public function enableCosting(): void
    {
        $this->authorizeInventoryAdministration();

        DB::transaction(function (): void {
            if ($this->isCostingEnabled()) {
                return;
            }

            $stockBalances = DB::table('inventory_movements')
                ->select('inventory_item_id', 'inventory_location_id')
                ->selectRaw('SUM(quantity_change) as quantity')
                ->groupBy('inventory_item_id', 'inventory_location_id')
                ->havingRaw('SUM(quantity_change) > 0')
                ->get();

            $unvalued = $stockBalances->filter(function ($row): bool {
                $valuation = InventoryValuationBalance::query()
                    ->where('inventory_item_id', $row->inventory_item_id)
                    ->where('inventory_location_id', $row->inventory_location_id)
                    ->first();

                return $valuation === null
                    || (int) round((float) $valuation->quantity * 1000) !== (int) round((float) $row->quantity * 1000);
            });

            if ($unvalued->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'inventory_costing' => 'Valora el saldo inicial de cada artículo/ubicación con existencias antes de activar el coste medio ponderado. Pendientes: ' . $unvalued->count() . '.',
                ]);
            }

            Setting::set('inventory_costing_enabled', '1', 'boolean', 'inventory');
            app(AuditLogger::class)->recordInventoryCostingEnabled();
        });
    }

    public function applyMovementCost(
        InventoryItem $item,
        InventoryLocation $location,
        int $deltaMilliUnits,
        int $stockBeforeMilliUnits,
        ?string $inputUnitCost,
    ): array {
        $balance = InventoryValuationBalance::query()
            ->where('inventory_item_id', $item->id)
            ->where('inventory_location_id', $location->id)
            ->lockForUpdate()
            ->first();

        if (! $this->isCostingEnabled() && $balance === null) {
            $cost = $inputUnitCost === null ? null : round((float) $inputUnitCost, 4);

            return [
                'unit_cost' => $cost === null ? null : number_format($cost, 4, '.', ''),
                'total_cost' => $cost === null
                    ? null
                    : number_format(abs($deltaMilliUnits / 1000) * $cost, 4, '.', ''),
            ];
        }

        if ($balance === null && $stockBeforeMilliUnits > 0) {
            throw ValidationException::withMessages([
                'inventory_costing' => 'Falta cargar el coste inicial de este artículo y ubicación antes de mover existencias.',
            ]);
        }

        $currentQuantity = $balance ? (int) round((float) $balance->quantity * 1000) : 0;
        if ($currentQuantity !== $stockBeforeMilliUnits) {
            throw ValidationException::withMessages([
                'inventory_costing' => 'El saldo valorado no coincide con el libro de existencias; requiere conciliación antes de continuar.',
            ]);
        }

        $quantityDelta = $deltaMilliUnits / 1000;
        $oldValue = $balance ? (float) $balance->inventory_value : 0.0;
        $unitCost = null;
        $movementCost = null;

        if ($deltaMilliUnits > 0) {
            if ($inputUnitCost === null) {
                throw ValidationException::withMessages([
                    'unit_cost' => 'El coste unitario es obligatorio para entradas mientras esté activa la valoración.',
                ]);
            }

            $unitCost = round((float) $inputUnitCost, 4);
            $movementCost = round($quantityDelta * $unitCost, 4);
        } else {
            if ($currentQuantity <= 0) {
                throw ValidationException::withMessages([
                    'inventory_costing' => 'No existe un coste medio disponible para valorar esta salida.',
                ]);
            }

            $unitCost = round($oldValue / ($currentQuantity / 1000), 4);
            $movementCost = round(abs($quantityDelta) * $unitCost, 4);
        }

        $newQuantity = $currentQuantity + $deltaMilliUnits;
        $newValue = $deltaMilliUnits > 0 ? $oldValue + $movementCost : $oldValue - $movementCost;
        if ($newQuantity < 0 || $newValue < -0.0001) {
            throw ValidationException::withMessages([
                'inventory_costing' => 'El movimiento dejaría el saldo valorado en negativo.',
            ]);
        }
        if ($newQuantity === 0) {
            $newValue = 0;
        }

        $balance ??= new InventoryValuationBalance([
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'created_by' => auth('web')->id(),
        ]);
        $balance->fill([
            'quantity' => number_format($newQuantity / 1000, 3, '.', ''),
            'inventory_value' => number_format(max(0, $newValue), 4, '.', ''),
            'average_unit_cost' => $newQuantity === 0
                ? '0.0000'
                : number_format(max(0, $newValue) / ($newQuantity / 1000), 4, '.', ''),
        ]);
        $balance->save();

        return [
            'unit_cost' => number_format($unitCost, 4, '.', ''),
            'total_cost' => number_format($movementCost, 4, '.', ''),
        ];
    }

    private function authorizeInventoryAdministration(): void
    {
        abort_unless(app(PlatformModules::class)->enabled('inventory'), 404, 'Inventory module is disabled.');

        $actor = auth()->user();
        $isAdministrator = $actor !== null
            && ($actor->hasRole(['super_admin', 'admin', 'editor', 'manager', 'site_admin'])
                || (filled(config('platform.developer_email')) && $actor->email === config('platform.developer_email')));

        abort_unless($isAdministrator, 403);
    }
}
