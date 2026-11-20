<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Resinas
            ['cat_slug' => 'resinas', 'slug' => 'fusion-promoter', 'price' => 89.90, 'stock' => 150, 'sku' => 'IE-FUS-001', 'weight_kg' => 5.0, 'name_es' => 'Fusion Promoter', 'name_en' => 'Fusion Promoter', 'desc_es' => 'Promotor de adhesión de alta calidad para pavimentos de resina.', 'short_es' => 'Promotor de adhesión para pavimentos decorativos.', 'featured' => true],
            ['cat_slug' => 'resinas', 'slug' => 'diamond-coat', 'price' => 129.90, 'stock' => 100, 'sku' => 'IE-DIA-001', 'weight_kg' => 10.0, 'name_es' => 'Diamond Coat', 'name_en' => 'Diamond Coat', 'desc_es' => 'Capa superior de resina epóxica con acabado diamantina. Ultra resistente.', 'short_es' => 'Capa superior epóxica de alta resistencia.', 'featured' => true],
            ['cat_slug' => 'resinas', 'slug' => 'fresch-coat', 'price' => 79.90, 'stock' => 120, 'sku' => 'IE-FRE-001', 'weight_kg' => 5.0, 'name_es' => 'Fresch Coat', 'name_en' => 'Fresch Coat', 'desc_es' => 'Revestimiento de resina para refrescar superficies existentes.', 'short_es' => 'Revestimiento para renovar superficies.', 'featured' => false, 'coming_soon' => true],
            ['cat_slug' => 'resinas', 'slug' => 'exteri-pro-paint', 'price' => 99.90, 'stock' => 80, 'sku' => 'IE-EXT-001', 'weight_kg' => 5.0, 'name_es' => 'Exteri Pro Paint', 'name_en' => 'Exteri Pro Paint', 'desc_es' => 'Pintura de resina profesional para exteriores. Resistente a UV y clima.', 'short_es' => 'Pintura resina profesional para exteriores.', 'featured' => true],
            ['cat_slug' => 'resinas', 'slug' => 'capa-superior-pu', 'price' => 69.90, 'stock' => 200, 'sku' => 'IE-CSU-001', 'weight_kg' => 5.0, 'name_es' => 'Capa Superior PU', 'name_en' => 'Top Coat PU', 'desc_es' => 'Capa superior de poliuretano para protección de pavimentos.', 'short_es' => 'Capa superior PU de protección.', 'featured' => false],

            // Aglutinantes
            ['cat_slug' => 'aglutinantes', 'slug' => 'rox-binder-a-b', 'price' => 149.90, 'stock' => 100, 'sku' => 'IE-ROX-AB', 'weight_kg' => 10.0, 'name_es' => 'Rox Binder A+B', 'name_en' => 'Rox Binder A+B', 'desc_es' => 'Aglutinante de dos componentes para mortero de resina.', 'short_es' => 'Aglutinante bincomponente para mortero.', 'featured' => true],
            ['cat_slug' => 'aglutinantes', 'slug' => 'rox-binder-back', 'price' => 79.90, 'stock' => 120, 'sku' => 'IE-ROX-BK', 'weight_kg' => 5.0, 'name_es' => 'Rox Binder Back', 'name_en' => 'Rox Binder Back', 'desc_es' => 'Componente B del aglutinante Rox Binder.', 'short_es' => 'Componente B Rox Binder.', 'featured' => false],
            ['cat_slug' => 'aglutinantes', 'slug' => 'uv-rox-resin-binder', 'price' => 119.90, 'stock' => 80, 'sku' => 'IE-UV-001', 'weight_kg' => 5.0, 'name_es' => 'UV Rox Resin Binder Transparent', 'name_en' => 'UV Rox Resin Binder Transparent', 'desc_es' => 'Aglutinante transparente resistente a rayos UV.', 'short_es' => 'Aglutinante transparente UV resistente.', 'featured' => true],

            // Kits
            ['cat_slug' => 'kits', 'slug' => 'kit-inicio-resina-4m2', 'price' => 199.90, 'stock' => 60, 'sku' => 'IE-KIT-04', 'weight_kg' => 15.0, 'name_es' => 'Kit de Inicio de Resina de 4m²', 'name_en' => 'Resin Starter Kit 4m²', 'desc_es' => 'Kit completo para instalar 4m² de pavimento de resina.', 'short_es' => 'Kit completo 4m² para principiantes.', 'featured' => true],
            ['cat_slug' => 'kits', 'slug' => 'kit-resina-4m2', 'price' => 179.90, 'stock' => 80, 'sku' => 'IE-KIT-04P', 'weight_kg' => 14.0, 'name_es' => 'Kit de Resina de 4m²', 'name_en' => 'Resin Kit 4m²', 'desc_es' => 'Kit de resina para instalar 4m² de pavimento decorativo.', 'short_es' => 'Kit resina 4m².', 'featured' => false],

            // Spray Granite
            ['cat_slug' => 'spray-granite', 'slug' => 'spray-granite-1kg', 'price' => 29.90, 'stock' => 300, 'sku' => 'IE-SG-1K', 'weight_kg' => 1.0, 'name_es' => 'Spray Granite 1kg', 'name_en' => 'Spray Granite 1kg', 'desc_es' => 'Bote de 1kg de chips de granito para aplicación por pulverización.', 'short_es' => 'Chips granito 1kg para pulverizar.', 'featured' => false],
            ['cat_slug' => 'spray-granite', 'slug' => 'spray-granite-10kg', 'price' => 249.90, 'stock' => 50, 'sku' => 'IE-SG-10K', 'weight_kg' => 10.0, 'name_es' => 'Spray Granite 10kg', 'name_en' => 'Spray Granite 10kg', 'desc_es' => 'Bote de 10kg de chips de granito para proyectos grandes.', 'short_es' => 'Chips granito 10kg proyecto grande.', 'featured' => true],
            ['cat_slug' => 'spray-granite', 'slug' => 'spray-granite-10-1', 'price' => 349.90, 'stock' => 30, 'sku' => 'IE-SG-101', 'weight_kg' => 10.0, 'name_es' => 'Spray Granite 10-1', 'name_en' => 'Spray Granite 10-1', 'desc_es' => 'Granito spray versión 10-1 de alta calidad.', 'short_es' => 'Spray granite alta calidad 10-1.', 'featured' => false, 'coming_soon' => true],

            // Herramientas
            ['cat_slug' => 'herramientas', 'slug' => 'forex-action-resin-mixer', 'price' => 49.90, 'stock' => 150, 'sku' => 'IE-MIX-001', 'weight_kg' => 2.0, 'name_es' => 'Mezclador de Resina Forex Action', 'name_en' => 'Forex Action Resin Mixer', 'desc_es' => 'Mezclador profesional para resinas de dos componentes.', 'short_es' => 'Mezclador profesional de resinas.', 'featured' => false],
            ['cat_slug' => 'herramientas', 'slug' => 'large-paint-mixer', 'price' => 39.90, 'stock' => 100, 'sku' => 'IE-MIX-002', 'weight_kg' => 1.5, 'name_es' => 'Mezclador de Pintura Grande', 'name_en' => 'Large Paint Mixer', 'desc_es' => 'Mezclador grande para pinturas y resinas.', 'short_es' => 'Mezclador grande profesional.', 'featured' => false],
            ['cat_slug' => 'herramientas', 'slug' => 'magic-trowel-26', 'price' => 59.90, 'stock' => 80, 'sku' => 'IE-TROW-26', 'weight_kg' => 1.0, 'name_es' => 'Magic Trowel 26"', 'name_en' => 'Magic Trowel 26"', 'desc_es' => 'Llana mágica de 26 pulgadas para acabados perfectos.', 'short_es' => 'Llana mágica 26" acabados perfectos.', 'featured' => false],
            ['cat_slug' => 'herramientas', 'slug' => 'serrated-squeegee-18', 'price' => 44.90, 'stock' => 90, 'sku' => 'IE-SQ-018', 'weight_kg' => 0.8, 'name_es' => 'Escurridor Dentado 18"', 'name_en' => 'Serrated Squeegee 18"', 'desc_es' => 'Escurridor dentado para aplicación uniforme de resina.', 'short_es' => 'Escurridor dentado 18".', 'featured' => false],
            ['cat_slug' => 'herramientas', 'slug' => 'spray-gun-hvlp', 'price' => 189.90, 'stock' => 40, 'sku' => 'IE-GUN-HVLP', 'weight_kg' => 3.0, 'name_es' => 'Pistola de Pulverización HVLP', 'name_en' => 'HVLP Spray Gun', 'desc_es' => 'Pistola HVLP profesional para spray granite.', 'short_es' => 'Pistola HVLP para spray granite.', 'featured' => true],
            ['cat_slug' => 'herramientas', 'slug' => 'pressure-pot-spray-gun-kit', 'price' => 349.90, 'stock' => 20, 'sku' => 'IE-PPG-KIT', 'weight_kg' => 12.0, 'name_es' => 'Kit Pistola de Pulverización con Depósito', 'name_en' => 'Pressure Pot Spray Gun Kit', 'desc_es' => 'Kit completo: pistola de pulverización con depósito a presión.', 'short_es' => 'Kit completo pistola + depósito.', 'featured' => false, 'coming_soon' => true],

            // Imprimaciones
            ['cat_slug' => 'imprimaciones', 'slug' => 'imprimacion-alcalina', 'price' => 39.90, 'stock' => 200, 'sku' => 'IE-IMP-ALC', 'weight_kg' => 5.0, 'name_es' => 'Imprimación Alcalina Blanca', 'name_en' => 'Alkaline Primer White', 'desc_es' => 'Imprimación alcalina blanca para preparación de superficies.', 'short_es' => 'Imprimación alcalina blanca.', 'featured' => false],
            ['cat_slug' => 'imprimaciones', 'slug' => 'imprimacion-entrada', 'price' => 44.90, 'stock' => 150, 'sku' => 'IE-IMP-ENT', 'weight_kg' => 5.0, 'name_es' => 'Imprimación de Entrada', 'name_en' => 'Entry Primer', 'desc_es' => 'Imprimación de entrada para zonas de alto tránsito.', 'short_es' => 'Imprimación zonas de alto tránsito.', 'featured' => false],
            ['cat_slug' => 'imprimaciones', 'slug' => 'imprimacion-polirrapida', 'price' => 49.90, 'stock' => 120, 'sku' => 'IE-IMP-POL', 'weight_kg' => 5.0, 'name_es' => 'Imprimación Polirrápida', 'name_en' => 'Fast-Track Primer', 'desc_es' => 'Imprimación de secado rápido para agilizar instalaciones.', 'short_es' => 'Imprimación secado rápido.', 'featured' => false],

            // Protección
            ['cat_slug' => 'proteccion', 'slug' => 'amourguard', 'price' => 89.90, 'stock' => 100, 'sku' => 'IE-AMG-001', 'weight_kg' => 5.0, 'name_es' => 'Amourguard', 'name_en' => 'Amourguard', 'desc_es' => 'Protector de alta resistencia para pavimentos de resina.', 'short_es' => 'Protector alta resistencia.', 'featured' => true],
            ['cat_slug' => 'proteccion', 'slug' => 'fijafix', 'price' => 59.90, 'stock' => 150, 'sku' => 'IE-FJF-001', 'weight_kg' => 2.0, 'name_es' => 'FijaFix', 'name_en' => 'FijaFix', 'desc_es' => 'Fijador y sellador para acabados de resina.', 'short_es' => 'Fijador y sellador resina.', 'featured' => false],

            // Limpieza
            ['cat_slug' => 'limpieza', 'slug' => 'limpiador-solvente', 'price' => 24.90, 'stock' => 250, 'sku' => 'IE-LIM-SOL', 'weight_kg' => 2.0, 'name_es' => 'Limpiador Solvente Seguro', 'name_en' => 'Safe Solvent Cleaner', 'desc_es' => 'Limpiador a base de solvente seguro para herramientas.', 'short_es' => 'Limpiador solvente seguro.', 'featured' => false],
        ];

        foreach ($products as $data) {
            $category = Category::where('slug', $data['cat_slug'])->first();
            if (!$category) continue;

            $product = Product::create([
                'category_id' => $category->id,
                'slug' => $data['slug'],
                'price' => $data['price'],
                'stock' => $data['stock'],
                'sku' => $data['sku'],
                'weight_kg' => $data['weight_kg'] ?? null,
                'is_active' => true,
                'is_featured' => $data['featured'] ?? false,
                'is_coming_soon' => $data['coming_soon'] ?? false,
                'sort_order' => 0,
            ]);

            // Set translations separately
            $product->setTranslation('name', 'es', $data['name_es']);
            $product->setTranslation('name', 'en', $data['name_en']);
            $product->setTranslation('description', 'es', $data['desc_es'] ?? '');
            $product->setTranslation('description', 'en', $data['desc_en'] ?? '');
            $product->setTranslation('short_description', 'es', $data['short_es'] ?? '');
            $product->setTranslation('short_description', 'en', $data['short_en'] ?? '');
            $product->save();
        }
    }
}
