<?php

namespace App\Services;

use App\Models\InventoryPurchaseOrder;
use App\Models\InventoryPurchaseOrderItem;
use App\Models\InventoryReceipt;
use App\Models\InventoryReceiptItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InventoryProcurementService
{
    public function markOrdered(InventoryPurchaseOrder $purchaseOrder): void
    {
        $this->authorizeInventoryAdministration();

        DB::transaction(function () use ($purchaseOrder): void {
            $order = InventoryPurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);

            if ($order->status !== 'draft' || ! $order->items()->exists()) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'Solo se pueden enviar pedidos en borrador con al menos una línea.',
                ]);
            }

            $order->update([
                'status' => 'ordered',
                'ordered_at' => now()->toDateString(),
            ]);
        });
    }

    public function cancel(InventoryPurchaseOrder $purchaseOrder): void
    {
        $this->authorizeInventoryAdministration();

        DB::transaction(function () use ($purchaseOrder): void {
            $order = InventoryPurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);

            if (! in_array($order->status, ['draft', 'ordered'], true) || $order->receipts()->exists()) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'No se puede cancelar un pedido que ya tiene recepciones o está cerrado.',
                ]);
            }

            $order->update(['status' => 'cancelled']);
        });
    }

    public function receive(InventoryPurchaseOrder $purchaseOrder, array $data): InventoryReceiptItem
    {
        $this->authorizeInventoryAdministration();

        abort_unless(app(PlatformModules::class)->enabled('inventory'), 404, 'Inventory module is disabled.');

        $validated = Validator::make($data, [
            'purchase_order_item_id' => ['required', 'integer', 'exists:inventory_purchase_order_items,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0', 'max:999999999.9999'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        return DB::transaction(function () use ($purchaseOrder, $validated): InventoryReceiptItem {
            $order = InventoryPurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->id);

            if (! in_array($order->status, ['ordered', 'partially_received'], true)) {
                throw ValidationException::withMessages([
                    'purchase_order' => 'El pedido debe estar enviado y no totalmente recibido ni cancelado.',
                ]);
            }

            $line = InventoryPurchaseOrderItem::query()
                ->where('purchase_order_id', $order->id)
                ->lockForUpdate()
                ->findOrFail($validated['purchase_order_item_id']);
            $outstanding = (float) $line->outstanding_quantity;
            $quantity = round((float) $validated['quantity'], 3);

            if ($quantity <= 0 || abs((float) $validated['quantity'] - $quantity) > 0.000001 || $quantity > $outstanding) {
                throw ValidationException::withMessages([
                    'quantity' => "La cantidad debe ser positiva, admitir hasta tres decimales y no superar el pendiente ({$line->outstanding_quantity}).",
                ]);
            }

            $unitCost = $validated['unit_cost'] ?? $line->unit_cost;
            $receipt = InventoryReceipt::create([
                'purchase_order_id' => $order->id,
                'reference' => $validated['reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);

            $movement = app(InventoryService::class)->recordMovement([
                'inventory_item_id' => $line->inventory_item_id,
                'inventory_location_id' => $line->inventory_location_id,
                'movement_type' => 'receipt',
                'quantity' => number_format($quantity, 3, '.', ''),
                'unit_cost' => $unitCost === null ? null : number_format((float) $unitCost, 4, '.', ''),
                'reference' => ($validated['reference'] ?? null) ?: $order->number,
                'notes' => trim(implode("\n", array_filter([
                    'Recepción de pedido de compra ' . $order->number,
                    $validated['notes'] ?? null,
                ]))),
            ]);

            $receiptItem = InventoryReceiptItem::create([
                'inventory_receipt_id' => $receipt->id,
                'purchase_order_item_id' => $line->id,
                'inventory_movement_id' => $movement->id,
                'quantity' => number_format($quantity, 3, '.', ''),
                'unit_cost' => $unitCost,
            ]);

            $allReceived = $order->items()->get()->every(
                fn (InventoryPurchaseOrderItem $orderLine): bool => (int) round((float) $orderLine->outstanding_quantity * 1000) === 0,
            );
            $order->update(['status' => $allReceived ? 'received' : 'partially_received']);

            return $receiptItem;
        });
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
