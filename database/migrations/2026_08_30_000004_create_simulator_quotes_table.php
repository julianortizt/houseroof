<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulator_quotes', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->foreignId('simulator_room_template_id')->constrained('simulator_room_templates');
            $table->foreignId('simulator_finish_variant_id')->constrained('simulator_finish_variants');
            $table->string('target_surface'); // floor | counter
            $table->decimal('area_m2', 8, 2);
            $table->decimal('price_per_m2_snapshot', 10, 2);
            $table->decimal('total_price', 12, 2);
            $table->string('status')->default('pending'); // pending|contacted|closed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulator_quotes');
    }
};
