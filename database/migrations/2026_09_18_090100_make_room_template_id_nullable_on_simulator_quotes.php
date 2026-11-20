<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Visitors can upload their own photo instead of picking a predefined room.
     * Those leads have no room template, and the NOT NULL constraint made the
     * lead endpoint reject them.
     */
    public function up(): void
    {
        Schema::table('simulator_quotes', function (Blueprint $table) {
            $table->foreignId('simulator_room_template_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('simulator_quotes', function (Blueprint $table) {
            $table->foreignId('simulator_room_template_id')->nullable(false)->change();
        });
    }
};
