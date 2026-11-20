<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El email del cliente es opcional en el CRM (clients.email es nullable), pero
 * la columna heredada `quotes.email` es NOT NULL: crear un presupuesto para un
 * cliente sin email reventaba con "NOT NULL constraint failed: quotes.email".
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('quotes', 'email')) {
            return;
        }

        Schema::table('quotes', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        // No se revierte: volver a NOT NULL fallaría si ya hay filas sin email.
    }
};
