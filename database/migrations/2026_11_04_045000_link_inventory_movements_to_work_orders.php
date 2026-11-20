<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inventory_movements') || Schema::hasColumn('inventory_movements', 'work_order_id')) {
            return;
        }

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->foreignId('work_order_id')
                ->nullable()
                ->after('inventory_location_id')
                ->constrained('mapscalculator_work_orders')
                ->nullOnDelete();
            $table->index(['work_order_id', 'created_at'], 'inventory_movements_work_order_created_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inventory_movements') || ! Schema::hasColumn('inventory_movements', 'work_order_id')) {
            return;
        }

        Schema::table('inventory_movements', function (Blueprint $table): void {
            $table->dropForeign(['work_order_id']);
            $table->dropIndex('inventory_movements_work_order_created_index');
            $table->dropColumn('work_order_id');
        });
    }
};
