<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Room templates can be either a 3D model (model_glb_path) or a plain photo
     * (photo_url). The simulator ships with photo-based rooms, so the GLB path
     * must be optional — the original NOT NULL constraint made it impossible to
     * create a photo room at all.
     */
    public function up(): void
    {
        Schema::table('simulator_room_templates', function (Blueprint $table) {
            $table->string('model_glb_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('simulator_room_templates', function (Blueprint $table) {
            $table->string('model_glb_path')->nullable(false)->change();
        });
    }
};
