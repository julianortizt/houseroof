<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quotes', 'deposit_percent')) {
            Schema::table('quotes', function (Blueprint $table): void {
                $table->decimal('deposit_percent', 5, 2)->default(30)->after('total');
            });
        }

        Schema::create('quote_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_type', 20);
            $table->unsignedBigInteger('amount_cents');
            $table->char('currency', 3)->default('eur');
            $table->string('status', 20)->default('pending')->index();
            $table->string('checkout_idempotency_key')->unique();
            $table->string('stripe_checkout_session_id')->nullable()->unique();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('quote_payment_refunds', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quote_payment_id')->constrained()->restrictOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stripe_refund_id')->nullable()->unique();
            $table->string('idempotency_key')->unique();
            $table->unsignedBigInteger('amount_cents');
            $table->string('status', 20)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_payment_refunds');
        Schema::dropIfExists('quote_payments');

        if (Schema::hasColumn('quotes', 'deposit_percent')) {
            Schema::table('quotes', function (Blueprint $table): void {
                $table->dropColumn('deposit_percent');
            });
        }
    }
};
