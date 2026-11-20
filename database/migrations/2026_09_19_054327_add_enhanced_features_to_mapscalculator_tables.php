<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing columns to measurements table (check if they exist first).
        //
        // NOTA: antes esto se hacía con un mapa de definiciones y
        // call_user_func_array(), que pasaba los argumentos de forma posicional.
        // Firmas reales de Blueprint: string($column, $length = null),
        // decimal($column, $total = 8, $places = 2), boolean($column, $default = false).
        // Por eso el 3er/4o argumento se descartaba en silencio: la columna
        // quedaba NOT NULL sin default (rompía la creación de proyectos) y
        // decimal() recibía un array como $total (SQL inválido en MySQL).
        Schema::table('mapscalculator_measurements', function (Blueprint $table) {
            if (!Schema::hasColumn('mapscalculator_measurements', 'map_layer')) {
                $table->string('map_layer', 20)->default('satellite');
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'map_bounds')) {
                $table->json('map_bounds')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'map_zoom')) {
                $table->decimal('map_zoom', 4, 2)->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'map_center')) {
                $table->json('map_center')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'drawing_mode')) {
                $table->string('drawing_mode', 20)->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'circles')) {
                $table->json('circles')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'lines')) {
                $table->json('lines')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'compasses')) {
                $table->json('compasses')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'dragpins')) {
                $table->json('dragpins')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'geojson_features')) {
                $table->json('geojson_features')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'export_data')) {
                $table->json('export_data')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'synced')) {
                $table->boolean('synced')->default(false);
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'last_synced_at')) {
                $table->timestamp('last_synced_at')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'cloud_id')) {
                $table->string('cloud_id', 64)->nullable()->unique();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'sync_conflicts')) {
                $table->json('sync_conflicts')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_measurements', 'is_premium')) {
                $table->boolean('is_premium')->default(false);
            }
        });

        // Add enhanced fields to projects table
        Schema::table('mapscalculator_projects', function (Blueprint $table) {
            if (!Schema::hasColumn('mapscalculator_projects', 'default_map_layer')) {
                $table->string('default_map_layer', 20)->default('satellite');
            }
            if (!Schema::hasColumn('mapscalculator_projects', 'default_map_bounds')) {
                $table->json('default_map_bounds')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_projects', 'default_map_zoom')) {
                $table->decimal('default_map_zoom', 4, 2)->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_projects', 'default_map_center')) {
                $table->json('default_map_center')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_projects', 'share_token')) {
                $table->string('share_token', 64)->nullable()->unique();
            }
            if (!Schema::hasColumn('mapscalculator_projects', 'is_public')) {
                $table->boolean('is_public')->default(false);
            }
            if (!Schema::hasColumn('mapscalculator_projects', 'shared_at')) {
                $table->timestamp('shared_at')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_projects', 'default_measurement_settings')) {
                $table->json('default_measurement_settings')->nullable();
            }
            if (!Schema::hasColumn('mapscalculator_projects', 'is_premium')) {
                $table->boolean('is_premium')->default(false);
            }
        });

        // Add premium subscription table
        if (!Schema::hasTable('mapscalculator_subscriptions')) {
            Schema::create('mapscalculator_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('plan', 20); // free, premium, pro
                $table->string('status', 20); // active, cancelled, expired, trial
                $table->string('provider', 20); // revenuecat, stripe, paddle
                $table->string('provider_subscription_id', 100)->nullable();
                $table->string('provider_customer_id', 100)->nullable();
                $table->timestamp('trial_ends_at')->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'status']);
                $table->index(['provider', 'provider_subscription_id']);
            });
        }

        // Add cloud sync logs table
        if (!Schema::hasTable('mapscalculator_sync_logs')) {
            Schema::create('mapscalculator_sync_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->morphs('syncable');
                $table->enum('action', ['create', 'update', 'delete', 'sync']);
                $table->enum('status', ['pending', 'synced', 'conflict', 'failed']);
                $table->json('local_data')->nullable();
                $table->json('remote_data')->nullable();
                $table->json('conflict_resolution')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('synced_at')->nullable();
                $table->timestamps();

                $table->index(['syncable_type', 'syncable_id'], 'mapscalculator_sync_logs_syncable_index');
                $table->index(['user_id', 'status'], 'mapscalculator_sync_logs_user_status_index');
            });
        }

        // Add measurement templates table
        if (!Schema::hasTable('mapscalculator_measurement_templates')) {
            Schema::create('mapscalculator_measurement_templates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name');
                $table->string('type');
                $table->json('default_settings');
                $table->json('default_layers');
                $table->boolean('is_system')->default(false);
                $table->boolean('is_public')->default(false);
                $table->unsignedInteger('usage_count')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index(['user_id', 'type']);
            });
        }

        // Add drawing layers table
        if (!Schema::hasTable('mapscalculator_drawing_layers')) {
            Schema::create('mapscalculator_drawing_layers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('measurement_id')->constrained('mapscalculator_measurements')->cascadeOnDelete();
                $table->string('name');
                $table->string('type');
                $table->json('data');
                $table->json('style');
                $table->boolean('visible')->default(true);
                $table->boolean('locked')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->index(['measurement_id', 'sort_order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapscalculator_drawing_layers');
        Schema::dropIfExists('mapscalculator_measurement_templates');
        Schema::dropIfExists('mapscalculator_sync_logs');
        Schema::dropIfExists('mapscalculator_subscriptions');

        Schema::table('mapscalculator_projects', function (Blueprint $table) {
            $table->dropColumn([
                'default_map_layer', 'default_map_bounds', 'default_map_zoom', 'default_map_center',
                'share_token', 'is_public', 'shared_at', 'default_measurement_settings', 'is_premium'
            ]);
        });

        Schema::table('mapscalculator_measurements', function (Blueprint $table) {
            $table->dropColumn([
                'map_layer', 'map_bounds', 'map_zoom', 'map_center',
                'drawing_mode', 'circles', 'lines', 'compasses', 'dragpins',
                'geojson_features', 'export_data', 'synced', 'last_synced_at',
                'cloud_id', 'sync_conflicts', 'is_premium'
            ]);
        });
    }
};