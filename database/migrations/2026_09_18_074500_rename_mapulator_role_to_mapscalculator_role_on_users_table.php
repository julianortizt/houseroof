<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The column was created as "mapulator_role" but the User model
        // (and the MapsCalculator module) expects "mapscalculator_role".
        if (Schema::hasColumn('users', 'mapulator_role') && ! Schema::hasColumn('users', 'mapscalculator_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('mapulator_role', 'mapscalculator_role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'mapscalculator_role') && ! Schema::hasColumn('users', 'mapulator_role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->renameColumn('mapscalculator_role', 'mapulator_role');
            });
        }
    }
};
