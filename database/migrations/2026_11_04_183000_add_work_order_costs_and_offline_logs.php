<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mapscalculator_work_orders', function (Blueprint $table): void {
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->decimal('other_cost', 12, 2)->default(0);
            $table->text('cost_notes')->nullable();
        });

        Schema::table('mapscalculator_work_logs', function (Blueprint $table): void {
            $table->uuid('client_uuid')->nullable()->unique();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('stripe_refund_id')->nullable()->unique();
            $table->string('stock_review_refund_status', 20)->nullable();
            $table->string('stock_review_refund_idempotency_key', 100)->nullable();
            $table->unsignedInteger('stock_review_refund_amount_cents')->nullable();
            $table->timestamp('stock_review_refund_requested_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropUnique(['stripe_refund_id']);
            $table->dropColumn([
                'stripe_refund_id',
                'stock_review_refund_status',
                'stock_review_refund_idempotency_key',
                'stock_review_refund_amount_cents',
                'stock_review_refund_requested_at',
            ]);
        });

        Schema::table('mapscalculator_work_logs', function (Blueprint $table): void {
            $table->dropUnique(['client_uuid']);
            $table->dropColumn('client_uuid');
        });

        Schema::table('mapscalculator_work_orders', function (Blueprint $table): void {
            $table->dropColumn(['labor_cost', 'other_cost', 'cost_notes']);
        });
    }
};
