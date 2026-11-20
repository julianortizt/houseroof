<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulator_finish_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulator_finish_id')->constrained('simulator_finishes')->cascadeOnDelete();
            $table->string('name');
            $table->string('base_color_hex')->nullable();
            $table->string('texture_color_path')->nullable();
            $table->string('texture_normal_path')->nullable();
            $table->string('texture_roughness_path')->nullable();
            $table->string('preview_image_path')->nullable();
            $table->unsignedTinyInteger('chip_density')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulator_finish_variants');
    }
};
