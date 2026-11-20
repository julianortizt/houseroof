<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignProductImagesManual extends Command
{
    protected $signature = 'products:assign-images-manual';
    protected $description = 'Assign images to products using manual slug-to-file mapping';

    // Map: product slug => image file path (relative to storage/app/public)
    private const IMAGE_MAP = [
        // Resinas
        'uv-rox-binder' => '2/rox-binder-a-b-high.webp',
        'fusion-adhesion-promotor' => '1/fusion-promoter.webp',
        'exterior-white-alkali-resistant-primer' => '20/imprimacion-alcalina.webp',

        // Spray Granite
        'spray-granite' => '11/spray-granite-1kg-high.webp',
        'infinity-effects-pu-topcoat' => '4/spray-stone-copy-high.webp',
        'spray-on-granite-colours-and-finishes' => '11/spray-granite-1kg.webp',
        'colores-y-acabados-de-granito-en-aerosol' => '11/spray-granite-1kg.webp',
        'spray-granite-1323' => '11/spray-granite-1kg-high.webp',

        // Spray Stone
        'infinity-effects-spray-stone-system' => '1/spray-stone-copy-high.webp',
        'sistema-infinity-effects-spray-stone' => '1/spray-stone-copy-high.webp',
        'armourguard' => '23/amourguard.webp',
        'armourguard-1331' => '23/amourguard.webp',
        'diamond-coat' => '2/diamond-coat.webp',

        // Pinturas
        'fresh-coat-paint' => '3/fresch-coat-paint-high.webp',
        'exteri-pro-paint' => '4/exteri-pro-paint.webp',
        'barniz-de-poliuretano-infinity-effects' => '4/spray-stone-copy-high.webp',

        // Kits
        'kit-de-resina-de-4m2' => '10/kit-de-resina-de-4m2.webp',
        'complete-4-m2-resin-starter-kit' => '10/kit-resina.webp',
        'spray-granite-gun-hvlp-starter-kit' => '10/spray-gun-hvlp-1-high-ng385j.webp',
        'kit-de-inicio-de-pistola-hvlp-para-granito-pulverizado' => '10/spray-gun-hvlp-1-high-ng385j.webp',

        // Herramientas
        'resin-binding-trowels' => '16/magic-trowel-26-high.webp',
        'llanas-para-resina' => '16/magic-trowel-26-high.webp',
        '26-metal-squeegee-magic-trowel-holder' => '16/magic-trowel.webp',
        '26-magic-trowel-blade' => '16/magic-trowel-26-high.webp',
        '26-magic-trowel-blade-and-holder-complete' => '16/magic-trowel.webp',
        'rubber-grout-float' => 'products/rubber-grout-float-high.webp',
        '18-serrated-squeegee' => '17/serrated-squeegee-18-high.webp',
        'adjustable-alu-frame-12-18' => 'products/marco-aluminio-ajustable.webp',
        'marco-de-aluminio-ajustable-12-18' => 'products/marco-aluminio-ajustable.webp',
        '10l-pressure-pot-with-spray-gun' => '19/pressure-pot-kit.webp',
        'olla-a-presion-de-10-l-con-pistola-rociadora' => '19/pressure-pot-kit.webp',
        'professional-paint-scuttle-17-quart16l' => 'products/cubo-pintura-profesional.webp',
        'cubo-de-pintura-profesional-17-cuartos16-l' => 'products/cubo-pintura-profesional.webp',
        '18-professional-paint-tray' => 'products/paint-tray-1-high.webp',
        'bandeja-de-pintura-profesional-de-18' => 'products/paint-tray-1-high.webp',
        'resin-mixer' => '14/resin-mixer.webp',
        'mezclador-de-resina' => '14/resin-mixer.webp',
        'forex-action-resin-mixer' => '14/forex-action-resin-mixer.webp',
        'mezcladora-de-resina-de-accion-forex' => '14/forex-action-resin-mixer.webp',
        'telescopic-extension-pole-4-to-8' => 'products/telescopic-pole-4-8ft-high.webp',
        'pertiga-telescopica-extensible-de-120-m-a-240-m' => 'products/telescopic-pole-4-8ft-high.webp',
        'ism1-infinity-effects-texture-sprayer' => 'products/sprayer1-high.webp',
        'pulverizador-de-texturas-ism1-infinity-effects' => 'products/sprayer1-high.webp',
        'film-de-enmascarar-preencintado' => 'products/pre-taped-masking-film-20-high.webp',

        // Limpieza
        'multi-purpose-safe-solvent-cleaner' => '10/limpiador-solvente-seguro.webp',

        // Imprimaciones
        'imprimacion-para-entrada-de-vehiculos' => '12/imprimacion-entrada.webp',
        'imprimacion-blanca-para-exteriores-resistente-a-los-alcalis' => '20/imprimacion-alcalina-blaca.webp',

        // Áridos - Teseries Stone (English slugs)
        'teseries-stone-aggregate-alicante-red' => '20/rojo-alicante.webp',
        'teseries-stone-aggregate-classic-white' => '11/blanco-clasico.webp',
        'teseries-stone-aggregate-special-white' => '12/blanco-especial.webp',
        'teseries-stone-aggregate-ivory-cream' => '13/crema-marfil.webp',
        'teseries-stone-aggregate-spanish-quartz' => '14/cuarzo-espanol.webp',
        'teseries-stone-aggregate-oak-quartz' => '15/cuarzo-roble.webp',
        'teseries-stone-aggregate-blue-gray' => '16/gris-azulado.webp',
        'teseries-stone-aggregate-light-gray' => '17/gris-claro.webp',
        'teseries-stone-aggregate-dark-gray' => '18/gris-oscuro.webp',
        'teseries-stone-aggregate-caramel-brown' => '35/marron-caramelo.webp',
        'teseries-stone-aggregate-black' => '19/negro.webp',
        'teseries-stone-aggregate-valencia-pink' => '21/rosa-valencia.webp',

        // Áridos - Teseries Stone (Spanish slugs)
        'arido-teseries-stone-blanco-clasico' => '11/blanco-clasico.webp',
        'arido-teseries-stone-blanco-especial' => '12/blanco-especial.webp',
        'arido-teseries-stone-color-crema-marfil' => '13/crema-marfil.webp',
        'arido-teseries-stone-cuarzo-espanol' => '14/cuarzo-espanol.webp',
        'arido-teseries-stone-cuarzo-roble' => '15/cuarzo-roble.webp',
        'arido-teseries-stone-gris-azulado' => '16/gris-azulado.webp',
        'arido-teseries-stone-gris-claro' => '17/gris-claro.webp',
        'arido-teseries-stone-gris-oscuro' => '18/gris-oscuro.webp',
        'arido-teseries-stone-negro' => '19/negro.webp',
        'arido-teseries-stone-rojo-alicante' => '20/rojo-alicante.webp',
        'arido-teseries-stone-rosa-valencia' => '21/rosa-valencia.webp',
        'arido-teseries-stone-marron-caramelo' => '35/marron-caramelo.webp',
        'arido-teseries-stone-azul-grisaceo' => '16/gris-azulado.webp',

        // Fijafix duplicates
        'fijafix-1339' => '24/fijafix.webp',
    ];

    public function handle(): int
    {
        $assigned = 0;
        $notFound = 0;
        $alreadyHad = 0;

        foreach (self::IMAGE_MAP as $slug => $imagePath) {
            $fullPath = storage_path('app/public/' . $imagePath);

            if (!file_exists($fullPath)) {
                $this->warn("  ⚠️  File not found: {$imagePath}");
                $notFound++;
                continue;
            }

            $updated = DB::table('products')
                ->where('slug', $slug)
                ->where(function ($q) {
                    $q->whereNull('image_path')->orWhere('image_path', '');
                })
                ->update(['image_path' => $imagePath]);

            if ($updated) {
                $this->info("  ✅ {$slug} ← {$imagePath}");
                $assigned++;
            } else {
                $alreadyHad++;
            }
        }

        $this->newLine();
        $this->info("Results:");
        $this->info("  Assigned: {$assigned}");
        $this->info("  Already had image: {$alreadyHad}");
        $this->info("  File not found: {$notFound}");

        // Show remaining products without images
        $remaining = DB::table('products')
            ->whereNull('image_path')->orWhere('image_path', '')
            ->count();
        $this->info("  Still without image: {$remaining}");

        return self::SUCCESS;
    }
}
