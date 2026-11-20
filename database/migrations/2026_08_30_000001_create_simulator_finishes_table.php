<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulator_finishes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['chips', 'solid', 'metallic']);
            $table->decimal('price_per_m2', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulator_finishes');
    }
};
