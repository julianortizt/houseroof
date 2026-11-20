<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('simulator_room_templates', function (Blueprint $table) {
            $table->string('photo_url')->nullable()->after('model_glb_path');
            $table->string('preview_image_path')->nullable()->after('photo_url');
        });
    }

    public function down(): void
    {
        Schema::table('simulator_room_templates', function (Blueprint $table) {
            $table->dropColumn(['photo_url', 'preview_image_path']);
        });
    }
};
