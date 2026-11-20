<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table): void {
                if (! Schema::hasColumn('inventory_movements', 'unit_cost')) {
                    $table->decimal('unit_cost', 14, 4)->nullable();
                }
                if (! Schema::hasColumn('inventory_movements', 'total_cost')) {
                    $table->decimal('total_cost', 14, 4)->nullable();
                }
            });
        }

        if (! Schema::hasTable('inventory_valuation_balances')) {
            Schema::create('inventory_valuation_balances', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('inventory_item_id')->constrained()->restrictOnDelete();
                $table->foreignId('inventory_location_id')->constrained()->restrictOnDelete();
                $table->decimal('quantity', 14, 3)->default(0);
                $table->decimal('inventory_value', 14, 4)->default(0);
                $table->decimal('average_unit_cost', 14, 4)->default(0);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['inventory_item_id', 'inventory_location_id'], 'inventory_valuation_item_location_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_valuation_balances');

        if (Schema::hasTable('inventory_movements')) {
            Schema::table('inventory_movements', function (Blueprint $table): void {
                $columns = array_filter([
                    Schema::hasColumn('inventory_movements', 'unit_cost') ? 'unit_cost' : null,
                    Schema::hasColumn('inventory_movements', 'total_cost') ? 'total_cost' : null,
                ]);
                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
