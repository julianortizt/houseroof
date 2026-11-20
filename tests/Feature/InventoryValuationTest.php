<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\InventoryLocation;
use App\Models\InventoryValuationBalance;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\InventoryValuationService;
use App\Services\PlatformModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryValuationTest extends TestCase
{
    use RefreshDatabase;

    public function test_weighted_average_cost_requires_and_uses_opening_snapshot(): void
    {
        $administrator = $this->createAdministrator();
        $this->actingAs($administrator);
        app(PlatformModules::class)->setEnabled('inventory', true);

        $item = InventoryItem::create([
            'sku' => 'COST-001',
            'name' => 'Valued material',
            'unit' => 'kg',
            'is_active' => true,
        ]);
        $location = InventoryLocation::create(['name' => 'Valuation store', 'is_active' => true]);
        $inventory = app(InventoryService::class);
        $inventory->recordMovement([
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'movement_type' => 'receipt',
            'quantity' => '10.000',
            'reference' => 'OPENING-STOCK',
        ]);
        $valuation = app(InventoryValuationService::class);

        try {
            $valuation->enableCosting();
            $this->fail('Costing cannot be enabled before the positive opening stock is valued.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('inventory_costing', $exception->errors());
        }

        $opening = $valuation->setOpeningBalance($item, [
            'inventory_location_id' => $location->id,
            'quantity' => '10.000',
            'unit_cost' => '5.0000',
        ]);
        $this->assertSame('50.0000', $opening->inventory_value);

        $receipt = $inventory->recordMovement([
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'movement_type' => 'receipt',
            'quantity' => '10.000',
            'unit_cost' => '7.0000',
            'reference' => 'PURCHASE-001',
        ]);
        $this->assertSame('7.0000', $receipt->fresh()->unit_cost);
        $this->assertSame('70.0000', $receipt->fresh()->total_cost);
        $this->assertSame('120.0000', $opening->fresh()->inventory_value);
        $this->assertSame('6.0000', $opening->fresh()->average_unit_cost);

        $valuation->enableCosting();
        $this->assertTrue($valuation->isCostingEnabled());

        $issue = $inventory->recordMovement([
            'inventory_item_id' => $item->id,
            'inventory_location_id' => $location->id,
            'movement_type' => 'issue',
            'quantity' => '5.000',
            'reference' => 'CONSUMPTION-001',
        ]);
        $balance = InventoryValuationBalance::query()->where('inventory_item_id', $item->id)->firstOrFail();
        $this->assertSame('-5.000', $issue->fresh()->quantity_change);
        $this->assertSame('6.0000', $issue->fresh()->unit_cost);
        $this->assertSame('30.0000', $issue->fresh()->total_cost);
        $this->assertSame('15.000', $balance->quantity);
        $this->assertSame('90.0000', $balance->inventory_value);
        $this->assertSame('6.0000', $balance->average_unit_cost);
    }

    public function test_costed_receipt_requires_unit_cost_and_item_module_is_gated(): void
    {
        $this->actingAs($this->createAdministrator());
        app(PlatformModules::class)->setEnabled('inventory', true);
        $item = InventoryItem::create(['sku' => 'COST-002', 'name' => 'New material', 'unit' => 'kg']);
        $location = InventoryLocation::create(['name' => 'New stock room']);

        app(InventoryValuationService::class)->enableCosting();

        try {
            app(InventoryService::class)->recordMovement([
                'inventory_item_id' => $item->id,
                'inventory_location_id' => $location->id,
                'movement_type' => 'receipt',
                'quantity' => 3,
            ]);
            $this->fail('A costed receipt must include its unit cost.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('unit_cost', $exception->errors());
        }

        $this->assertDatabaseCount('inventory_movements', 0);
    }

    private function createAdministrator(): User
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::create([
            'name' => 'Valuation Admin',
            'email' => 'valuation-admin@example.com',
            'password' => 'temporary-password',
        ]);
        $user->assignRole('admin');

        return $user;
    }
}
