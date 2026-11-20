<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Color;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MediaSeeder extends Seeder
{
    /**
     * Map of product slugs to their WordPress image filenames.
     */
    protected array $productImages = [
        'fusion-promoter' => 'fusion-promotr-copy-high-jm4ygz.webp',
        'diamond-coat' => 'diamond-coat-copy-high.webp',
        'fresch-coat' => 'fresch-coat-paint-high.webp',
        'exteri-pro-paint' => 'spray-stone-copy-high.webp',
        'capa-superior-pu' => 'capa-superior-pu.webp',
        'rox-binder-a-b' => 'rox-binder-a-b-high.webp',
        'rox-binder-back' => 'rox-binder-back-high.webp',
        'uv-rox-resin-binder' => 'rox-binder-pot-made-in-spain.jpg',
        'kit-inicio-resina-4m2' => 'kit-de-inicio-de-resina-de-4m2.webp',
        'kit-resina-4m2' => 'kit-de-resina-de-4m2.webp',
        'spray-granite-1kg' => 'spray-granite-1kg-high.webp',
        'spray-granite-10kg' => 'spray-granite-10kg-high.webp',
        'spray-granite-10-1' => 'spray-granite-10-1-high.webp',
        'forex-action-resin-mixer' => 'forex-action-resin-mixer.webp',
        'large-paint-mixer' => 'large-paint-mixer-high.webp',
        'magic-trowel-26' => 'magic-trowel-26-high.webp',
        'serrated-squeegee-18' => 'serrated-squeegee-18-high.webp',
        'spray-gun-hvlp' => 'spray-gun-hvlp-1-high-ng385j.webp',
        'pressure-pot-spray-gun-kit' => 'pressure-pot-spray-gun-kit-high.webp',
        'imprimacion-alcalina' => 'imprimacion-alcalina-blaca.webp',
        'imprimacion-entrada' => 'imprimacion-entrada.webp',
        'imprimacion-polirrapida' => 'imprimacion-polirrapida.webp',
        'amourguard' => 'spray-stone.jpg',
        'fijafix' => 'spray-granite.jpg',
        'limpiador-solvente' => 'limpiador-solvente-seguro.webp',
    ];

    /**
     * Map of color slugs to their WordPress image filenames.
     */
    protected array $colorImages = [
        'blanco-clasico' => 'blanco-clasico.webp',
        'blanco-especial' => 'blanco-especial.webp',
        'crema-marfil' => 'crema-marfil.webp',
        'cuarzo-espanol' => 'cuarzo-espanol.webp',
        'cuarzo-roble' => 'cuarzo-roble.webp',
        'gris-azulado' => 'gris-azulado.webp',
        'gris-claro' => 'gris-claro.webp',
        'gris-oscuro' => 'gris-oscuro.webp',
        'negro' => 'negro.webp',
        'rojo-alicante' => 'rojo-alicante.webp',
        'rosa-valencia' => 'rosa-valencia.webp',
    ];

    /**
     * Site images (logo, hero, factory, etc.)
     */
    protected array $siteImages = [
        'logo' => 'logo.png',
        'logo-alt' => 'logo-alt.png',
        'factory' => 'factory.jpg',
        'terrace-example' => 'terrace-example.png',
        'van' => 'van.jpg',
        'pool-terrace' => 'pool-terrace.jpg',
        'industrial-floor' => 'industrial-floor.jpg',
        'countertop' => 'countertop.jpg',
        'countertop-1' => 'countertop-1.jpg',
        'hero-bg' => 'hero-bg.jpg',
        'leopard' => 'leopard.jpg',
    ];

    public function run(): void
    {
        $this->command->info('📸 Starting MediaSeeder...');

        // Attach product images
        $this->attachProductImages();

        // Attach color images
        $this->attachColorImages();

        // Copy site images
        $this->copySiteImages();

        $this->command->info('✅ MediaSeeder completed!');
    }

    protected function attachProductImages(): void
    {
        $this->command->info('📦 Attaching product images...');

        foreach ($this->productImages as $slug => $filename) {
            $product = Product::where('slug', $slug)->first();

            if (!$product) {
                $this->command->warn("  ⚠️  Product not found: {$slug}");
                continue;
            }

            $sourcePath = storage_path("app/public/products/{$filename}");

            if (!file_exists($sourcePath)) {
                $this->command->warn("  ⚠️  Image not found: {$filename} (looked in storage/app/public/products/)");
                continue;
            }

            // Clear existing media
            $product->clearMediaCollection('default');

            // Add new media
            $product->addMedia($sourcePath)
                ->usingName(pathinfo($filename, PATHINFO_FILENAME))
                ->toMediaCollection('default');

            $this->command->info("  ✅ {$slug} → {$filename}");
        }
    }

    protected function attachColorImages(): void
    {
        $this->command->info('🎨 Attaching color images...');

        foreach ($this->colorImages as $slug => $filename) {
            $color = Color::where('slug', $slug)->first();

            if (!$color) {
                $this->command->warn("  ⚠️  Color not found: {$slug}");
                continue;
            }

            $sourcePath = storage_path("app/public/colors/{$filename}");

            if (!file_exists($sourcePath)) {
                $this->command->warn("  ⚠️  Image not found: {$filename} (looked in storage/app/public/colors/)");
                continue;
            }

            // Clear existing media
            $color->clearMediaCollection('default');

            // Add new media
            $color->addMedia($sourcePath)
                ->usingName(pathinfo($filename, PATHINFO_FILENAME))
                ->toMediaCollection('default');

            $this->command->info("  ✅ {$slug} → {$filename}");
        }
    }

    protected function copySiteImages(): void
    {
        $this->command->info('🌐 Copying site images...');

        foreach ($this->siteImages as $name => $filename) {
            $sourcePath = storage_path("app/public/site/{$filename}");

            if (!file_exists($sourcePath)) {
                $this->command->warn("  ⚠️  Site image not found: {$filename} (looked in storage/app/public/site/)");
                continue;
            }

            $this->command->info("  ✅ {$name} → {$filename}");
        }
    }
}
