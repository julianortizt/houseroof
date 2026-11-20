<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El CRM de clientes (Módulo 3 del prompt) y la plantilla del presupuesto en
 * PDF ya referenciaban `company`, pero la columna no existía: la línea
 * "Empresa" del PDF nunca se imprimía. Se añade también el NIF/CIF, habitual
 * en presupuestos de obra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mapscalculator_clients', function (Blueprint $table) {
            if (! Schema::hasColumn('mapscalculator_clients', 'company')) {
                $table->string('company')->nullable()->after('name');
            }
            if (! Schema::hasColumn('mapscalculator_clients', 'nif')) {
                $table->string('nif', 30)->nullable()->after('phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mapscalculator_clients', function (Blueprint $table) {
            $table->dropColumn(['company', 'nif']);
        });
    }
};
