<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * quotes.status was created as enum('new','contacted','quoted','closed'),
     * which SQLite turns into a CHECK constraint. Meanwhile the Mapulator writes
     * its own statuses (borrador, enviado, aprobado, rechazado, expirado) and
     * Quote::getStatusLabelAttribute() maps BOTH sets.
     *
     * Whichever constraint is in place, half the flow breaks: inserting a
     * Mapulator quote fails against the enum, and the development database had
     * been widened by hand in the opposite direction, so the legacy statuses
     * were rejected there. A plain string accepts both sets.
     */
    public function up(): void
    {
        // quote_items references quotes, so the table rebuild must not trip
        // over foreign keys.
        Schema::withoutForeignKeyConstraints(function () {
            Schema::table('quotes', function (Blueprint $table) {
                $table->string('status', 20)->default('new')->change();
            });
        });
    }

    public function down(): void
    {
        Schema::withoutForeignKeyConstraints(function () {
            Schema::table('quotes', function (Blueprint $table) {
                $table->enum('status', ['new', 'contacted', 'quoted', 'closed'])->default('new')->change();
            });
        });
    }
};
