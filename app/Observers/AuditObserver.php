<?php

namespace App\Observers;

use App\Models\AccountingAccount;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\MapsCalculator\Project;
use App\Models\MapsCalculator\WorkLog;
use App\Models\MapsCalculator\WorkOrder;
use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryMovement;
use App\Models\InventoryPurchaseOrder;
use App\Models\InventoryPurchaseOrderItem;
use App\Models\InventoryReceipt;
use App\Models\InventoryReceiptItem;
use App\Models\InventoryReservation;
use App\Models\InventorySupplier;
use App\Models\InventoryValuationBalance;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Order;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    private const TRACKED_FIELDS = [
        User::class => ['name', 'email', 'mapscalculator_role'],
        Quote::class => ['quote_number', 'status', 'project_id', 'subtotal', 'tax_percent', 'total', 'deposit_percent'],
        Project::class => ['name', 'status', 'assigned_to'],
        WorkOrder::class => ['project_id', 'status', 'assigned_to', 'scheduled_start', 'scheduled_end', 'actual_start', 'actual_end'],
        WorkLog::class => ['work_order_id', 'created_by', 'date', 'type'],
        InventoryItem::class => ['sku', 'name', 'unit', 'reorder_level', 'is_active'],
        InventoryLocation::class => ['code', 'name', 'is_active'],
        InventoryMovement::class => ['inventory_item_id', 'inventory_location_id', 'work_order_id', 'movement_type', 'quantity_change', 'balance_after', 'reference'],
        InventoryReservation::class => ['work_order_id', 'inventory_item_id', 'inventory_location_id', 'quantity', 'consumed_quantity', 'status', 'released_by', 'released_at'],
        InventorySupplier::class => ['name', 'tax_id', 'email', 'phone', 'is_active'],
        InventoryPurchaseOrder::class => ['number', 'supplier_id', 'status', 'ordered_at'],
        InventoryPurchaseOrderItem::class => ['purchase_order_id', 'inventory_item_id', 'inventory_location_id', 'quantity', 'unit_cost'],
        InventoryReceipt::class => ['purchase_order_id', 'reference', 'received_by', 'received_at'],
        InventoryReceiptItem::class => ['purchase_order_item_id', 'inventory_movement_id', 'quantity', 'unit_cost'],
        InventoryValuationBalance::class => ['inventory_item_id', 'inventory_location_id', 'quantity', 'inventory_value', 'average_unit_cost'],
        Invoice::class => ['quote_id', 'status', 'subtotal', 'tax_percent', 'tax_amount', 'total'],
        AccountingAccount::class => ['code', 'name', 'account_type', 'is_active'],
        JournalEntry::class => ['entry_number', 'entry_date', 'description', 'status', 'reversal_of_id', 'reversed_by_entry_id', 'posted_at'],
        Order::class => ['status', 'payment_status', 'total', 'stock_review_refund_status'],
    ];

    public function created(Model $model): void
    {
        $this->record($model, 'created', [], $this->onlyTracked($model->getAttributes(), $model));
    }

    public function updated(Model $model): void
    {
        $changes = $this->onlyTracked($model->getChanges(), $model);
        if ($changes === []) {
            return;
        }

        $oldValues = [];
        foreach (array_keys($changes) as $field) {
            $oldValues[$field] = $model->getRawOriginal($field);
        }

        $this->record($model, 'updated', $oldValues, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $this->onlyTracked($model->getRawOriginal(), $model), []);
    }

    public function restored(Model $model): void
    {
        $this->record($model, 'restored', [], $this->onlyTracked($model->getAttributes(), $model));
    }

    private function onlyTracked(array $values, Model $model): array
    {
        $fields = self::TRACKED_FIELDS[$model::class] ?? [];

        return array_intersect_key($values, array_flip($fields));
    }

    private function record(Model $model, string $action, array $oldValues, array $newValues): void
    {
        if ($oldValues === [] && $newValues === []) {
            return;
        }

        $request = app()->bound('request') ? request() : null;
        $customer = auth('customer')->user();

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'actor_label' => $customer instanceof Customer ? 'Cliente: ' . $customer->email : null,
            'event' => $model::class . '.' . $action,
            'subject_type' => $model::class,
            'subject_id' => (string) $model->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }
}
