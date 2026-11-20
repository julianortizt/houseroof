<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda la captura (PNG del lienzo con los polígonos) de las mediciones
 * interiores para poder incrustarla en el PDF del presupuesto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mapscalculator_measurements', function (Blueprint $table) {
            if (! Schema::hasColumn('mapscalculator_measurements', 'capture_path')) {
                $table->string('capture_path')->nullable()->after('reference_image_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mapscalculator_measurements', function (Blueprint $table) {
            if (Schema::hasColumn('mapscalculator_measurements', 'capture_path')) {
                $table->dropColumn('capture_path');
            }
        });
    }
};
