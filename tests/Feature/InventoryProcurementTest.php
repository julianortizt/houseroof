<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryPurchaseOrder;
use App\Models\InventoryPurchaseOrderItem;
use App\Models\InventorySupplier;
use App\Models\InventoryMovement;
use App\Models\User;
use App\Services\InventoryProcurementService;
use App\Services\PlatformModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryProcurementTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_receipts_update_stock_and_preserve_cost_and_audit_links(): void
    {
        $administrator = $this->createAdministrator();
        $this->actingAs($administrator);
        app(PlatformModules::class)->setEnabled('inventory', true);

        [$order, $line, $item, $location] = $this->createPurchaseOrder();
        $procurement = app(InventoryProcurementService::class);
        $procurement->markOrdered($order);

        $firstReceipt = $procurement->receive($order, [
            'purchase_order_item_id' => $line->id,
            'quantity' => '2.500',
            'reference' => 'DELIVERY-001',
            'unit_cost' => '4.1250',
        ]);

        $this->assertSame('partially_received', $order->fresh()->status);
        $this->assertSame('4.1250', $firstReceipt->fresh()->unit_cost);
        $this->assertSame('2.500', $firstReceipt->movement->fresh()->quantity_change);
        $this->assertSame('2.500', $firstReceipt->movement->fresh()->balance_after);
        $this->assertSame('DELIVERY-001', $firstReceipt->movement->reference);

        $lastReceipt = $procurement->receive($order, [
            'purchase_order_item_id' => $line->id,
            'quantity' => '2.500',
        ]);

        $this->assertSame('received', $order->fresh()->status);
        $this->assertSame('5.000', $lastReceipt->movement->fresh()->balance_after);
        $this->assertSame('5.000', $line->fresh()->received_quantity);
        $this->assertDatabaseCount('inventory_receipts', 2);
        $this->assertDatabaseCount('inventory_receipt_items', 2);
        $this->assertSame(2, InventoryMovement::where('inventory_item_id', $item->id)
            ->where('inventory_location_id', $location->id)
            ->count());
    }

    public function test_receipt_rejects_overdelivery_and_cancelled_or_unordered_orders(): void
    {
        $this->actingAs($this->createAdministrator());
        app(PlatformModules::class)->setEnabled('inventory', true);
        [$order, $line] = $this->createPurchaseOrder();
        $procurement = app(InventoryProcurementService::class);

        try {
            $procurement->receive($order, [
                'purchase_order_item_id' => $line->id,
                'quantity' => 1,
            ]);
            $this->fail('A draft order cannot be received.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('purchase_order', $exception->errors());
        }

        $procurement->markOrdered($order);

        try {
            $procurement->receive($order, [
                'purchase_order_item_id' => $line->id,
                'quantity' => 5.001,
            ]);
            $this->fail('A receipt cannot exceed the remaining ordered quantity.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }

        $this->assertDatabaseCount('inventory_receipts', 0);
        $this->assertDatabaseCount('inventory_movements', 0);
    }

    public function test_procurement_requires_inventory_module_and_administrator(): void
    {
        $administrator = $this->createAdministrator();
        [$order] = $this->createPurchaseOrder();
        $this->actingAs($administrator);
        app(PlatformModules::class)->setEnabled('inventory', false);

        try {
            app(InventoryProcurementService::class)->markOrdered($order);
            $this->fail('Procurement must be blocked while inventory is disabled.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        app(PlatformModules::class)->setEnabled('inventory', true);
        $staff = User::create([
            'name' => 'Viewer',
            'email' => 'viewer@example.com',
            'password' => 'temporary-password',
        ]);
        $this->actingAs($staff);

        try {
            app(InventoryProcurementService::class)->markOrdered($order);
            $this->fail('Non-administrators cannot manage procurement.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_procurement_panel_resources_are_controlled_by_inventory_flag(): void
    {
        $this->actingAs($this->createAdministrator());
        $this->assertTrue(app(PlatformModules::class)->enabled('inventory'));

        $this->get('/admin/inventory-purchase-orders')
            ->assertOk()
            ->assertSee('Pedidos de compra');
        $this->get('/admin/inventory-suppliers')
            ->assertOk()
            ->assertSee('Proveedores');
        $this->get('/admin/inventory-valuations')
            ->assertOk()
            ->assertSee('Valoración de existencias');

        app(PlatformModules::class)->setEnabled('inventory', false);

        $this->get('/admin/inventory-purchase-orders')->assertForbidden();
        $this->get('/admin/inventory-suppliers')->assertForbidden();

        app(PlatformModules::class)->setEnabled('inventory', true);
    }

    private function createAdministrator(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::create([
            'name' => 'Inventory Admin',
            'email' => 'inventory-admin@example.com',
            'password' => 'temporary-password',
        ]);
        $user->assignRole('admin');

        return $user;
    }

    private function createPurchaseOrder(): array
    {
        $supplier = InventorySupplier::create([
            'name' => 'Materials Supplier',
            'is_active' => true,
        ]);
        $item = InventoryItem::create([
            'sku' => 'PROC-001',
            'name' => 'Resin compound',
            'unit' => 'kg',
            'is_active' => true,
        ]);
        $location = InventoryLocation::create([
            'name' => 'Main store',
            'is_active' => true,
        ]);
        $order = InventoryPurchaseOrder::create([
            'number' => 'OC-TEST-001',
            'supplier_id' => $supplier->id,
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);
        $line = InventoryPurchaseOrderItem::create([
            'purchase_order_id' => $order->id,
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'quantity' => '5.000',
            'unit_cost' => '4.0000',
        ]);

        return [$order, $line, $item, $location];
    }
}
