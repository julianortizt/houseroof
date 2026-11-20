<?php

namespace Database\Seeders;

use App\Models\MapsCalculator\PriceItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PriceItemSeeder extends Seeder
{
    /**
     * Catálogo de precios del módulo MapsCalculator.
     *
     * Sin esto, una instalación limpia (migrate:fresh --seed) deja la tabla
     * mapscalculator_price_items vacía: la página de presupuesto no muestra
     * líneas y MapulatorProject::generateAutoQuote() no genera nada.
     *
     * Es idempotente: se puede ejecutar varias veces sin duplicar líneas
     * (`php artisan db:seed --class=PriceItemSeeder`).
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Resina autonivelante estándar',
                'category' => 'resina_autonivelante',
                'unit' => 'm2',
                'unit_price_m2' => 35.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Resina autonivelante premium',
                'category' => 'resina_autonivelante',
                'unit' => 'm2',
                'unit_price_m2' => 45.00,
                'sort_order' => 2,
            ],
            [
                'name' => 'Resina decorativa chips',
                'category' => 'resina_decorativa',
                'unit' => 'm2',
                'unit_price_m2' => 55.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Resina industrial alta resistencia',
                'category' => 'resina_industrial',
                'unit' => 'm2',
                'unit_price_m2' => 65.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Preparación superficie',
                'category' => 'preparacion_superficie',
                'unit' => 'm2',
                'unit_price_m2' => 8.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Imprimación epoxi',
                'category' => 'imprimacion',
                'unit' => 'm2',
                'unit_price_m2' => 12.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Zócalo perimetral',
                'category' => 'zocalo_perimetral',
                'unit' => 'ml',
                'unit_price_m2' => 15.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Mano de obra instalación',
                'category' => 'mano_obra',
                'unit' => 'm2',
                'unit_price_m2' => 25.00,
                'sort_order' => 1,
            ],
            [
                'name' => 'Traslado materiales',
                'category' => 'traslado',
                'unit' => 'unidad',
                'unit_price_m2' => 150.00,
                'sort_order' => 1,
            ],
        ];

        foreach ($items as $item) {
            PriceItem::updateOrCreate(
                ['name' => $item['name']],
                $item + [
                    'slug' => Str::slug($item['name']),
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('PriceItemSeeder: ' . count($items) . ' líneas de catálogo.');
    }
}
