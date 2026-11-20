<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The MapsCalculator module had no migrations at all: "clients" and
     * "quote_items" only existed as hand-made tables in the development SQLite
     * file, and "measurements", "work_orders", "work_logs" and "price_items"
     * did not exist anywhere.
     *
     * Worse, the module's Project model pointed at "projects", which is the
     * public portfolio table (slug/title/location) — a completely different
     * entity. The module now owns its own prefixed tables so the portfolio is
     * never touched and internal client projects can never leak to /proyectos.
     */
    public function up(): void
    {
        // Adopt-and-replace the hand-made, unreferenced leftover so that a
        // fresh install and this database end up with the same schema. It is
        // only dropped when empty; if it ever holds rows it is left untouched.
        if (Schema::hasTable('clients') && ! Schema::hasTable('mapscalculator_clients') && DB::table('clients')->count() === 0) {
            Schema::drop('clients');
        }

        if (! Schema::hasTable('mapscalculator_clients')) {
            Schema::create('mapscalculator_clients', function (Blueprint $table) {
                $table->id();
                $table->string('name')->index();
                $table->string('email')->nullable();
                $table->string('phone', 50)->nullable();
                $table->text('address')->nullable();
                $table->text('notes')->nullable();
                $table->string('portal_token', 64)->nullable()->unique();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('mapscalculator_projects')) {
            Schema::create('mapscalculator_projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('client_id')->constrained('mapscalculator_clients')->cascadeOnDelete();
                $table->string('name');
                $table->string('type', 20)->default('interior');     // interior | exterior | mixto
                $table->string('status', 30)->default('nuevo')->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->date('scheduled_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('mapscalculator_measurements')) {
            Schema::create('mapscalculator_measurements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('mapscalculator_projects')->cascadeOnDelete();
                $table->string('type', 20);                          // interior | exterior
                $table->string('label')->nullable();
                $table->json('geojson')->nullable();                 // exterior: drawn polygons
                $table->json('polygon_points')->nullable();          // interior: scaled points
                $table->json('calibration_line')->nullable();        // interior: scale reference
                $table->string('reference_image_path')->nullable();
                $table->decimal('scale_factor', 16, 10)->nullable();
                $table->decimal('area_m2', 12, 2)->default(0);
                $table->decimal('perimeter_m', 12, 2)->default(0);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mapscalculator_price_items')) {
            Schema::create('mapscalculator_price_items', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('category', 50)->index();
                $table->string('unit', 20)->default('m2');           // m2 | ml | unidad | hora | bulto
                $table->decimal('unit_price_m2', 10, 2)->default(0);
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mapscalculator_work_orders')) {
            Schema::create('mapscalculator_work_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('mapscalculator_projects')->cascadeOnDelete();
                $table->foreignId('quote_id')->nullable()->constrained('quotes')->nullOnDelete();
                $table->string('status', 30)->default('agendada')->index();
                $table->date('scheduled_start')->nullable();
                $table->date('scheduled_end')->nullable();
                $table->date('actual_start')->nullable();
                $table->date('actual_end')->nullable();
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mapscalculator_work_logs')) {
            Schema::create('mapscalculator_work_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('work_order_id')->constrained('mapscalculator_work_orders')->cascadeOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->date('date')->index();
                $table->string('type', 20)->default('general');       // antes | durante | despues | general
                $table->text('description');
                $table->string('photo_path')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mapscalculator_work_logs');
        Schema::dropIfExists('mapscalculator_work_orders');
        Schema::dropIfExists('mapscalculator_price_items');
        Schema::dropIfExists('mapscalculator_measurements');
        Schema::dropIfExists('mapscalculator_projects');
        Schema::dropIfExists('mapscalculator_clients');
    }
};
