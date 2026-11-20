<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El modal de sincronización y el panel del proyecto leen `synced` y
 * `last_synced_at` del proyecto, columnas que nunca se crearon (sólo existían
 * en las mediciones), así que cualquier intento de guardarlas fallaba.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mapscalculator_projects', function (Blueprint $table) {
            if (! Schema::hasColumn('mapscalculator_projects', 'synced')) {
                $table->boolean('synced')->default(false)->after('is_premium');
            }

            if (! Schema::hasColumn('mapscalculator_projects', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable()->after('synced');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mapscalculator_projects', function (Blueprint $table) {
            if (Schema::hasColumn('mapscalculator_projects', 'last_synced_at')) {
                $table->dropColumn('last_synced_at');
            }

            if (Schema::hasColumn('mapscalculator_projects', 'synced')) {
                $table->dropColumn('synced');
            }
        });
    }
};
