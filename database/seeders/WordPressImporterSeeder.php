<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * WordPress Data Importer
 * Reads from the wordpress_temp database and seeds into Laravel tables.
 */
class WordPressImporterSeeder extends Seeder
{
    private string $wpDb = 'wordpress_temp';

    public function run(): void
    {
        $this->command->info('🔄 Importando datos de WordPress...');

        // 1. Import categories
        $this->importCategories();

        // 2. Import products with real WordPress data
        $this->importProducts();

        // 3. Import colors (stone aggregates)
        $this->importColors();

        $this->command->info('✅ Importación de WordPress completada!');
    }

    private function importCategories(): void
    {
        $this->command->info('📂 Importando categorías...');

        // Clear existing (with FK checks disabled)
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('categories')->truncate();
        DB::table('category_translations')->truncate();
        DB::table('product_translations')->truncate();
        DB::table('products')->truncate();
        DB::table('colors')->truncate();
        DB::table('color_translations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $categories = [
            ['name' => 'Resinas de Poliuretano', 'slug' => 'resinas-poliuretano', 'description' => 'Resinas de poliuretano para pavimentos y suelos decorativos', 'sort_order' => 1],
            ['name' => 'Aglutinantes', 'slug' => 'aglutinantes', 'description' => 'Aglutinantes para áridos y piedras decorativas', 'sort_order' => 2],
            ['name' => 'Kits', 'slug' => 'kits', 'description' => 'Kits completos de instalación', 'sort_order' => 3],
            ['name' => 'Spray Granite', 'slug' => 'spray-granite', 'description' => 'Sistema de granito pulverizado para superficies', 'sort_order' => 4],
            ['name' => 'Spray Stone', 'slug' => 'spray-stone', 'description' => 'Sistema de piedra pulverizada', 'sort_order' => 5],
            ['name' => 'Pinturas', 'slug' => 'pinturas', 'description' => 'Pinturas para interiores y exteriores', 'sort_order' => 6],
            ['name' => 'Herramientas', 'slug' => 'herramientas', 'description' => 'Herramientas profesionales para instalación', 'sort_order' => 7],
            ['name' => 'Áridos Decorativos', 'slug' => 'aridos-decorativos', 'description' => 'Piedras y áridos decorativos Teseries Stone', 'sort_order' => 8],
            ['name' => 'Sistemas', 'slug' => 'sistemas', 'description' => 'Sistemas completos de recubrimiento', 'sort_order' => 9],
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'description' => $cat['description'],
                'sort_order' => $cat['sort_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("  ✅ " . count($categories) . " categorías creadas");
    }

    private function importProducts(): void
    {
        $this->command->info('📦 Importando productos...');

        // Get WP products
        $wpProducts = DB::connection($this->wpDb)
            ->table('wp_posts as p')
            ->leftJoin('wp_postmeta as price', function ($join) {
                $join->on('p.ID', '=', 'price.post_id')
                    ->where('price.meta_key', '=', '_regular_price');
            })
            ->leftJoin('wp_postmeta as stock', function ($join) {
                $join->on('p.ID', '=', 'stock.post_id')
                    ->where('stock.meta_key', '=', '_stock_status');
            })
            ->leftJoin('wp_postmeta as stock_qty', function ($join) {
                $join->on('p.ID', '=', 'stock_qty.post_id')
                    ->where('stock_qty.meta_key', '=', '_stock');
            })
            ->select(
                'p.ID as wp_id',
                'p.post_title as name',
                'p.post_name as slug',
                'p.post_content as description',
                'price.meta_value as price',
                'stock.meta_value as stock_status',
                'stock_qty.meta_value as stock_qty'
            )
            ->where('p.post_type', 'product')
            ->where('p.post_status', 'publish')
            ->orderBy('p.ID')
            ->get();

        // Map WordPress category IDs to Laravel category slugs
        $categoryMap = [
            // English categories
            'resin-bound' => 'resinas-poliuretano',
            'resin-bound-kits' => 'kits',
            'resin-bound-tools' => 'herramientas',
            'resin-aggregates' => 'aglutinantes',
            'resin-bonded' => 'aglutinantes',
            'gravel-resin' => 'aglutinantes',
            'spray-granite' => 'spray-granite',
            'spray-stone' => 'spray-stone',
            'paint' => 'pinturas',
            'pintura' => 'pinturas',
            'countertop' => 'resinas-poliuretano',
            'encimeras' => 'resinas-poliuretano',
            'tools' => 'herramientas',
            'herramientas' => 'herramientas',
            'herramientas-agltuinado-resina' => 'herramientas',
            'kits' => 'kits',
            'kits-aglutinado-resina' => 'kits',
            'systems' => 'sistemas',
            'sistemas' => 'sistemas',
            'industrial-floor' => 'resinas-poliuretano',
            'suelos-industriales' => 'resinas-poliuretano',
            'aglutinado-resina' => 'aglutinantes',
            'agregados-resina' => 'aridos-decorativos',
            'resina-grava' => 'aglutinantes',
        ];

        // Map product names to category slugs (more precise mapping)
        $nameCategoryMap = [
            'UV Rox' => 'aglutinantes',
            'Kit de resina' => 'kits',
            'Complete 4 m' => 'kits',
            'starter kit' => 'kits',
            'Imprimaci' => 'resinas-poliuretano',
            'Solvent' => 'herramientas',
            'Fijafix' => 'aglutinantes',
            'FijaFix' => 'aglutinantes',
            'Alkali Resistant' => 'resinas-poliuretano',
            'Spray Granite' => 'spray-granite',
            'spray granite' => 'spray-granite',
            'PU Topcoat' => 'spray-granite',
            'Spray Stone' => 'spray-stone',
            'Spray-on granite' => 'spray-granite',
            'Armourguard' => 'spray-stone',
            'Armourguard' => 'spray-stone',
            'Diamond Coat' => 'spray-stone',
            'Fusion' => 'resinas-poliuretano',
            'Fresh Coat' => 'pinturas',
            'Exteri pro' => 'pinturas',
            'Trowel' => 'herramientas',
            'trowel' => 'herramientas',
            'Squeegee' => 'herramientas',
            'squeegee' => 'herramientas',
            'Float' => 'herramientas',
            'Frame' => 'herramientas',
            'Gun HVLP' => 'herramientas',
            'Pressure Pot' => 'herramientas',
            'Paint Scuttle' => 'herramientas',
            'Paint Tray' => 'herramientas',
            'paint tray' => 'herramientas',
            'Mixer' => 'herramientas',
            'mezclador' => 'herramientas',
            'Mezclador' => 'herramientas',
            'Telescopic' => 'herramientas',
            'Pertiga' => 'herramientas',
            'Extensi' => 'herramientas',
            'Extension' => 'herramientas',
            'ISM1' => 'herramientas',
            'Pulverizador' => 'herramientas',
            'Teseries Stone' => 'aridos-decorativos',
            'ario Teseries' => 'aridos-decorativos',
            'Colores y acabados' => 'spray-granite',
            'Barniz de poliuretano' => 'spray-granite',
            'Sistema Infinity' => 'spray-stone',
            'Forex Action' => 'herramientas',
            'Film de enmascarar' => 'herramientas',
            'Cubo de pintura' => 'herramientas',
            'Bandeja de pintura' => 'herramientas',
            'Marco de aluminio' => 'herramientas',
            'Kit de inicio de pistola' => 'herramientas',
            'Olla a presi' => 'herramientas',
            'Llanas para resina' => 'herramientas',
        ];

        // Products already cleared in importCategories()

        $count = 0;
        $usedSlugs = [];
        foreach ($wpProducts as $product) {
            $name = $product->name;
            $slug = Str::slug($name);
            
            // Handle duplicate slugs
            if (in_array($slug, $usedSlugs)) {
                $slug = $slug . '-' . $product->wp_id;
            }
            $usedSlugs[] = $slug;
            $price = $product->price ? (float) $product->price : 0;
            $stock = $product->stock_status === 'outofstock' ? 0 : ($product->stock_qty ?? 10);
            $isComingSoon = in_array($product->stock_status, ['outofstock']) && $price > 100;

            // Determine category from product name
            $categoryId = null;
            foreach ($nameCategoryMap as $pattern => $categorySlug) {
                if (Str::contains($name, $pattern, false) || Str::contains(mb_strtolower($name), mb_strtolower($pattern), false)) {
                    $cat = DB::table('categories')->where('slug', $categorySlug)->first();
                    if ($cat) {
                        $categoryId = $cat->id;
                    }
                    break;
                }
            }

            // Fallback: uncategorized
            if (!$categoryId) {
                $cat = DB::table('categories')->where('slug', 'herramientas')->first();
                $categoryId = $cat?->id;
            }

            // Clean description - strip HTML but keep basic formatting
            $description = $this->cleanHtml($product->description ?? '');

            DB::table('products')->insert([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'price' => $price,
                'stock' => max(0, (int) $stock),
                'is_active' => true,
                'is_coming_soon' => $isComingSoon,
                'category_id' => $categoryId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $count++;
        }

        $this->command->info("  ✅ {$count} productos importados");
    }

    private function importColors(): void
    {
        $this->command->info('🎨 Importando colores de áridos...');

        $colors = [
            ['name' => 'Blanco Clásico', 'slug' => 'blanco-clasico', 'description' => 'Mármol calcítico compuesto principalmente de calcita, opacos y cuarzo. Dureza: 4 Mohs.'],
            ['name' => 'Crema Marfil', 'slug' => 'crema-marfil', 'description' => 'Mármol de origen sedimentario carbonatado, compuesto por foraminíferos y vetas de calcita. Dureza: 3 Mohs.'],
            ['name' => 'Cuarzo Español', 'slug' => 'cuarzo-espanol', 'description' => 'Roca ígnea compuesta de feldespato y mica. Dureza: 3 Mohs.'],
            ['name' => 'Rojo Alicante', 'slug' => 'rojo-alicante', 'description' => 'Caliza rojiza, compuesta de carbonatos: caliza y dolomita. Dureza: 3 Mohs.'],
            ['name' => 'Blanco Especial', 'slug' => 'blanco-especial', 'description' => 'Piedra dolomita; Carbonato de calcio y magnesio. Dureza: 4 Mohs.'],
            ['name' => 'Rosa Valencia', 'slug' => 'rosa-valencia', 'description' => 'Caliza recristalizada de grano fino. Color amarillento con abundantes vetas rojizas.'],
            ['name' => 'Cuarzo Roble', 'slug' => 'cuarzo-roble', 'description' => 'Cuarzo, compuesto principalmente de sílice. Dureza: 7 Mohs.'],
            ['name' => 'Azul Grisáceo', 'slug' => 'azul-grisaceo', 'description' => 'Granito dolomítico grisáceo. Dureza: 4 Mohs.'],
            ['name' => 'Gris Claro', 'slug' => 'gris-claro', 'description' => 'Árido dolomítico grisáceo. Dureza: 5 Mohs.'],
            ['name' => 'Gris Oscuro', 'slug' => 'gris-oscuro', 'description' => 'Mármol dolomita homogéneo. Dureza: 3 Mohs.'],
            ['name' => 'Marrón Caramelo', 'slug' => 'marron-caramelo', 'description' => 'Mármol dolomita homogéneo. Dureza: 3 Mohs.'],
            ['name' => 'Negro', 'slug' => 'negro', 'description' => 'Caliza natural de origen volcánico basáltico. Dureza: 3 Mohs.'],
        ];

        foreach ($colors as $color) {
            DB::table('colors')->insert([
                'name' => $color['name'],
                'slug' => $color['slug'],
                'description' => $color['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info("  ✅ " . count($colors) . " colores creados");
    }

    private function cleanHtml(string $html): string
    {
        // Decode HTML entities
        $text = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Remove script/style tags
        $text = preg_replace('#<script[^>]*>.*?</script>#is', '', $text);
        $text = preg_replace('#<style[^>]*>.*?</style>#is', '', $text);
        // Convert <br> and <p> to newlines
        $text = preg_replace('#<br\s*/?>#i', "\n", $text);
        $text = preg_replace('#</p>#i', "\n", $text);
        $text = preg_replace('#<li>#i', "• ", $text);
        // Strip remaining tags
        $text = strip_tags($text);
        // Clean up whitespace
        $text = preg_replace('#\n{3,}#', "\n\n", $text);
        $text = trim($text);
        // Limit length for short description
        if (mb_strlen($text) > 1000) {
            $text = mb_substr($text, 0, 997) . '...';
        }
        return $text;
    }
}
