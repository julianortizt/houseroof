<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The Mapulator quote flow (App\Models\Quote, QuotePdfController and the
     * mapscalculator routes) reads and writes columns that no migration ever
     * created: they had been added by hand to the development SQLite file, so a
     * fresh "migrate" produced a quotes table the module cannot use.
     *
     * Everything here is additive and guarded, so it is a no-op on databases
     * that already have the columns.
     */
    private const COLUMNS = [
        'quote_number' => ['string', null, true],
        'valid_until' => ['date', null, false],
        'subtotal' => ['decimal', 12, false],
        'tax_percent' => ['decimal', 5, false],
        'tax_amount' => ['decimal', 12, false],
        'total' => ['decimal', 12, false],
        'terms_conditions' => ['text', null, false],
        'is_admin_created' => ['boolean', null, false],
        'sent_at' => ['timestamp', null, false],
        'approved_at' => ['timestamp', null, false],
        'rejected_at' => ['timestamp', null, false],
        'version' => ['unsignedInteger', null, false],
        'parent_quote_id' => ['unsignedBigInteger', null, true],
        'project_id' => ['unsignedBigInteger', null, true],
        'pdf_path' => ['string', null, false],
    ];

    public function up(): void
    {
        $missing = array_values(array_filter(
            array_keys(self::COLUMNS),
            fn (string $column) => ! Schema::hasColumn('quotes', $column),
        ));

        if ($missing === []) {
            return;
        }

        Schema::table('quotes', function (Blueprint $table) use ($missing) {
            foreach ($missing as $column) {
                [$type, $size, $index] = self::COLUMNS[$column];

                match ($type) {
                    'string' => $table->string($column)->nullable(),
                    'text' => $table->text($column)->nullable(),
                    'date' => $table->date($column)->nullable(),
                    'timestamp' => $table->timestamp($column)->nullable(),
                    'boolean' => $table->boolean($column)->default(false),
                    'decimal' => $table->decimal($column, $size, 2)->default(0),
                    'unsignedInteger' => $table->unsignedInteger($column)->default(1),
                    'unsignedBigInteger' => $table->unsignedBigInteger($column)->nullable(),
                };

                if ($index) {
                    $table->index($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            foreach (array_keys(self::COLUMNS) as $column) {
                if (Schema::hasColumn('quotes', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
