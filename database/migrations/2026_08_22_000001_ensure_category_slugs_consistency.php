<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ensures all product categories have correct slugs and JSON translations.
 *
 * This migration is idempotent — safe to run multiple times.
 * It reconciles the categories table with the canonical slug list used
 * across navigation, footer links, and the Filament admin panel.
 *
 * Run with: php artisan migrate
 * Or manually: php artisan tinker --execute="Artisan::call('migrate', ['--path' => '2026_08_22_000001_ensure_category_slugs_consistency.php']);"
 */
return new class extends Migration
{
    private const CANONICAL_CATEGORIES = [
        'resinas-poliuretano' => [
            'sort_order' => 1,
            'name_es' => 'Resinas de Poliuretano',
            'name_en' => 'Polyurethane Resins',
            'desc_es' => 'Resinas de alta calidad para la fabricación de pavimentos decorativos. Fabricadas en España con los mejores materiales.',
            'desc_en' => 'High quality resins for manufacturing decorative pavements. Made in Spain with the finest materials.',
        ],
        'aglutinantes' => [
            'sort_order' => 2,
            'name_es' => 'Aglutinantes',
            'name_en' => 'Binders',
            'desc_es' => 'Aglutinantes y ligantes para morteros de resina y áridos decorativos.',
            'desc_en' => 'Binders and binding agents for resin mortars and decorative aggregates.',
        ],
        'kits' => [
            'sort_order' => 3,
            'name_es' => 'Kits',
            'name_en' => 'Kits',
            'desc_es' => 'Kits completos con todo lo necesario para instalar pavimentos de resina.',
            'desc_en' => 'Complete kits with everything needed to install resin pavements.',
        ],
        'spray-granite' => [
            'sort_order' => 4,
            'name_es' => 'Spray Granite',
            'name_en' => 'Spray Granite',
            'desc_es' => 'Productos para la aplicación de chips de granito por pulverización.',
            'desc_en' => 'Products for spray application of granite chips.',
        ],
        'spray-stone' => [
            'sort_order' => 5,
            'name_es' => 'Spray Stone',
            'name_en' => 'Spray Stone',
            'desc_es' => 'Sistemas de pulverización de piedra decorativa para fachadas y suelos.',
            'desc_en' => 'Decorative stone spray systems for facades and floors.',
        ],
        'pinturas' => [
            'sort_order' => 6,
            'name_es' => 'Pinturas',
            'name_en' => 'Paints',
            'desc_es' => 'Pinturas y recubrimientos para interiores y exteriores.',
            'desc_en' => 'Paints and coatings for interiors and exteriors.',
        ],
        'herramientas' => [
            'sort_order' => 7,
            'name_es' => 'Herramientas',
            'name_en' => 'Tools',
            'desc_es' => 'Herramientas profesionales para la aplicación de resinas y áridos decorativos.',
            'desc_en' => 'Professional tools for applying resins and decorative aggregates.',
        ],
        'aridos-decorativos' => [
            'sort_order' => 8,
            'name_es' => 'Áridos Decorativos',
            'name_en' => 'Decorative Aggregates',
            'desc_es' => 'Áridos y chips de granito en más de 20 colores naturales.',
            'desc_en' => 'Aggregates and granite chips in more than 20 natural colours.',
        ],
        'sistemas' => [
            'sort_order' => 9,
            'name_es' => 'Sistemas',
            'name_en' => 'Systems',
            'desc_es' => 'Sistemas completos de pavimento decorativo.',
            'desc_en' => 'Complete decorative pavement systems.',
        ],
    ];

    public function up(): void
    {
        foreach (self::CANONICAL_CATEGORIES as $slug => $data) {
            $exists = DB::table('categories')->where('slug', $slug)->exists();

            $nameJson = json_encode(['es' => $data['name_es'], 'en' => $data['name_en']], JSON_UNESCAPED_UNICODE);
            $descJson = json_encode(['es' => $data['desc_es'], 'en' => $data['desc_en']], JSON_UNESCAPED_UNICODE);

            if ($exists) {
                // Update existing: ensure JSON translations and sort_order are correct
                DB::table('categories')
                    ->where('slug', $slug)
                    ->update([
                        'name'        => $nameJson,
                        'description' => $descJson,
                        'sort_order'  => $data['sort_order'],
                        'is_active'   => true,
                    ]);
            } else {
                // Create missing category
                DB::table('categories')->insert([
                    'slug'        => $slug,
                    'name'        => $nameJson,
                    'description' => $descJson,
                    'sort_order'  => $data['sort_order'],
                    'is_active'   => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // This migration only ensures data consistency — no structural changes to reverse.
        // Individual categories can be removed manually if needed.
    }
};
