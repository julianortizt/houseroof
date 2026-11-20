<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reparación del esquema del módulo MapsCalculator.
 *
 * POR QUÉ EXISTE ESTA MIGRACIÓN
 * -----------------------------
 * En producción faltaban ficheros de migración del módulo
 * (`create_mapscalculator_tables`, `add_mapscalculator_columns_to_quotes_table`,
 * `add_enhanced_features_to_mapscalculator_tables`) porque nunca se subieron.
 * El problema es que sus filas SÍ estaban en la tabla `migrations` (la base de
 * datos se creó importando un volcado anterior), así que `php artisan
 * migrate:status` decía que todo estaba al día mientras que, en realidad:
 *
 *   - a `mapscalculator_measurements` le faltaban `circles`, `lines`,
 *     `map_layer`, `map_bounds`, `map_zoom`, `map_center`, `compasses`,
 *     `dragpins`, `geojson_features`, `export_data`, `cloud_id`,
 *     `sync_conflicts`, `is_premium` y `drawing_mode`;
 *   - a `mapscalculator_projects` le faltaban `default_measurement_settings`
 *     e `is_premium`;
 *   - `mapscalculator_subscriptions` y `mapscalculator_sync_logs` existían con
 *     un esquema antiguo (sin `deleted_at`, `metadata`, `user_id`, `action`…);
 *   - no existían `mapscalculator_measurement_templates` ni
 *     `mapscalculator_drawing_layers`.
 *
 * Consecuencia visible: al guardar una medición el INSERT fallaba con
 * «Unknown column 'circles' in 'INSERT INTO'» y el editor mostraba la pantalla
 * de error de Livewire.
 *
 * CÓMO SE COMPORTA
 * ----------------
 * Es idempotente: cada cambio va protegido con `hasTable()` / `hasColumn()`, de
 * modo que se puede ejecutar tantas veces como haga falta y en cualquier
 * entorno (producción o local) sin duplicar ni borrar nada. Sólo añade: no
 * modifica ni elimina columnas existentes ni datos.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ------------------------------------------------------------------
        // 1. Medidas: columnas que el editor y el modelo ya usan
        // ------------------------------------------------------------------
        if (Schema::hasTable('mapscalculator_measurements')) {
            Schema::table('mapscalculator_measurements', function (Blueprint $table) {
                if (! Schema::hasColumn('mapscalculator_measurements', 'map_layer')) {
                    $table->string('map_layer', 20)->default('satellite');
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'map_bounds')) {
                    $table->json('map_bounds')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'map_zoom')) {
                    $table->decimal('map_zoom', 4, 2)->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'map_center')) {
                    $table->json('map_center')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'drawing_mode')) {
                    $table->string('drawing_mode', 20)->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'circles')) {
                    $table->json('circles')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'lines')) {
                    $table->json('lines')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'compasses')) {
                    $table->json('compasses')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'dragpins')) {
                    $table->json('dragpins')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'geojson_features')) {
                    $table->json('geojson_features')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'export_data')) {
                    $table->json('export_data')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'synced')) {
                    $table->boolean('synced')->default(false);
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'last_synced_at')) {
                    $table->timestamp('last_synced_at')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'cloud_id')) {
                    $table->string('cloud_id', 64)->nullable()->unique();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'sync_conflicts')) {
                    $table->json('sync_conflicts')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_measurements', 'is_premium')) {
                    $table->boolean('is_premium')->default(false);
                }
            });
        }

        // ------------------------------------------------------------------
        // 2. Proyectos: ajustes por defecto del mapa
        // ------------------------------------------------------------------
        if (Schema::hasTable('mapscalculator_projects')) {
            Schema::table('mapscalculator_projects', function (Blueprint $table) {
                if (! Schema::hasColumn('mapscalculator_projects', 'default_map_layer')) {
                    $table->string('default_map_layer', 20)->default('satellite');
                }
                if (! Schema::hasColumn('mapscalculator_projects', 'default_map_bounds')) {
                    $table->json('default_map_bounds')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_projects', 'default_map_zoom')) {
                    $table->decimal('default_map_zoom', 4, 2)->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_projects', 'default_map_center')) {
                    $table->json('default_map_center')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_projects', 'share_token')) {
                    $table->string('share_token', 64)->nullable()->unique();
                }
                if (! Schema::hasColumn('mapscalculator_projects', 'is_public')) {
                    $table->boolean('is_public')->default(false);
                }
                if (! Schema::hasColumn('mapscalculator_projects', 'shared_at')) {
                    $table->timestamp('shared_at')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_projects', 'default_measurement_settings')) {
                    $table->json('default_measurement_settings')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_projects', 'is_premium')) {
                    $table->boolean('is_premium')->default(false);
                }
            });
        }

        // ------------------------------------------------------------------
        // 3. Suscripciones: en producción existía una versión antigua de la
        //    tabla, así que se completan las columnas que faltan.
        // ------------------------------------------------------------------
        if (! Schema::hasTable('mapscalculator_subscriptions')) {
            Schema::create('mapscalculator_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('plan', 20);
                $table->string('status', 20);
                $table->string('provider', 20);
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
        } else {
            Schema::table('mapscalculator_subscriptions', function (Blueprint $table) {
                if (! Schema::hasColumn('mapscalculator_subscriptions', 'provider_subscription_id')) {
                    $table->string('provider_subscription_id', 100)->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_subscriptions', 'provider_customer_id')) {
                    $table->string('provider_customer_id', 100)->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_subscriptions', 'trial_ends_at')) {
                    $table->timestamp('trial_ends_at')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_subscriptions', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_subscriptions', 'metadata')) {
                    $table->json('metadata')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_subscriptions', 'deleted_at')) {
                    $table->timestamp('deleted_at')->nullable();
                }
            });
        }

        // ------------------------------------------------------------------
        // 4. Registro de sincronización: igual, existía con esquema antiguo.
        // ------------------------------------------------------------------
        if (! Schema::hasTable('mapscalculator_sync_logs')) {
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
        } else {
            Schema::table('mapscalculator_sync_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('mapscalculator_sync_logs', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                }
                if (! Schema::hasColumn('mapscalculator_sync_logs', 'action')) {
                    $table->enum('action', ['create', 'update', 'delete', 'sync'])->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_sync_logs', 'local_data')) {
                    $table->json('local_data')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_sync_logs', 'remote_data')) {
                    $table->json('remote_data')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_sync_logs', 'conflict_resolution')) {
                    $table->json('conflict_resolution')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_sync_logs', 'error_message')) {
                    $table->text('error_message')->nullable();
                }
                if (! Schema::hasColumn('mapscalculator_sync_logs', 'synced_at')) {
                    $table->timestamp('synced_at')->nullable();
                }
            });
        }

        // ------------------------------------------------------------------
        // 5. Tablas que faltaban por completo
        // ------------------------------------------------------------------
        if (! Schema::hasTable('mapscalculator_measurement_templates')) {
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

        if (! Schema::hasTable('mapscalculator_drawing_layers')) {
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
     * No se revierte: esta migración sólo añade lo que la aplicación ya daba por
     * hecho. Deshacerla borraría columnas con datos reales de mediciones.
     */
    public function down(): void
    {
        // Intencionadamente vacío.
    }
};
