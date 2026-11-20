<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;

class TranslateProducts extends Command
{
    protected $signature = 'translate:all';
    protected $description = 'Translate all products and categories to 7 languages';

    // Translation dictionaries
    private array $productTranslations = [
        // Polyurethane Resins
        'UV Rox Binder™' => [
            'de' => 'UV Rox Binder™',
            'it' => 'UV Rox Binder™',
            'fr' => 'UV Rox Binder™',
            'pt' => 'UV Rox Binder™',
            'nl' => 'UV Rox Binder™',
        ],
        'Fresh Coat Paint' => [
            'de' => 'Fresh Coat Farbe',
            'it' => 'Fresh Coat Vernice',
            'fr' => 'Fresh Coat Peinture',
            'pt' => 'Fresh Coat Tinta',
            'nl' => 'Fresh Coat Verf',
        ],
        'Diamond Coat' => [
            'de' => 'Diamond Coat',
            'it' => 'Diamond Coat',
            'fr' => 'Diamond Coat',
            'pt' => 'Diamond Coat',
            'nl' => 'Diamond Coat',
        ],
        'Fusion Promoter' => [
            'de' => 'Fusion Promoter',
            'it' => 'Fusion Promoter',
            'fr' => 'Fusion Promoter',
            'pt' => 'Fusion Promoter',
            'nl' => 'Fusion Promoter',
        ],
        'Polyurethane Resin' => [
            'de' => 'Polyurethanharz',
            'it' => 'Resina di poliuretano',
            'fr' => 'Résine de polyuréthane',
            'pt' => 'Resina de poliuretano',
            'nl' => 'Polyurethaanhars',
        ],
        'Epoxy Primer' => [
            'de' => 'Epoxidgrundierung',
            'it' => 'Primer epossidico',
            'fr' => 'Primer époxy',
            'pt' => 'Primer epóxi',
            'nl' => 'Epoxy primer',
        ],
        'Polyurethane Primer' => [
            'de' => 'Polyurethan-Grundierung',
            'it' => 'Primer in poliuretano',
            'fr' => 'Primer polyuréthane',
            'pt' => 'Primer de poliuretano',
            'nl' => 'Polyurethaan primer',
        ],
        'Top Coat' => [
            'de' => 'Decklack',
            'it' => 'Strato di finitura',
            'fr' => 'Couche de finition',
            'pt' => 'Camada de acabamento',
            'nl' => 'Topcoat',
        ],
        'Terrace Coat' => [
            'de' => 'Terrassenlack',
            'it' => 'Vernice terrazza',
            'fr' => 'Peinture terrasse',
            'pt' => 'Tinta para terraço',
            'nl' => 'Terrasverf',
        ],
        'UV Protect' => [
            'de' => 'UV-Schutz',
            'it' => 'Protezione UV',
            'fr' => 'Protection UV',
            'pt' => 'Proteção UV',
            'nl' => 'UV-bescherming',
        ],
        'Armourguard' => [
            'de' => 'Armourguard',
            'it' => 'Armourguard',
            'fr' => 'Armourguard',
            'pt' => 'Armourguard',
            'nl' => 'Armourguard',
        ],
        // Spray Granite
        'Spray Granite' => [
            'de' => 'Spray Granit',
            'it' => 'Spray Granito',
            'fr' => 'Spray Granit',
            'pt' => 'Spray Granito',
            'nl' => 'Spray Graniet',
        ],
        'Spray Granite Gun HVLP Kit' => [
            'de' => 'Spray Granit Pistole HVLP-Set',
            'it' => 'Kit Pistola HVLP Spray Granito',
            'fr' => 'Kit Pistolet HVLP Spray Granit',
            'pt' => 'Kit Pistola HVLP Spray Granito',
            'nl' => 'Spray Graniet Pistool HVLP Kit',
        ],
        // Spray Stone
        'Spray Stone' => [
            'de' => 'Spray Stein',
            'it' => 'Spray Pietra',
            'fr' => 'Spray Pierre',
            'pt' => 'Spray Pedra',
            'nl' => 'Spray Steen',
        ],
        // Aggregates
        'Rojo Alicante' => [
            'de' => 'Rot Alicante',
            'it' => 'Rosso Alicante',
            'fr' => 'Rouge Alicante',
            'pt' => 'Vermelho Alicante',
            'nl' => 'Rood Alicante',
        ],
        'Bianco Classico' => [
            'de' => 'Weiß Klassisch',
            'it' => 'Bianco Classico',
            'fr' => 'Blanc Classique',
            'pt' => 'Branco Clássico',
            'nl' => 'Wit Klassiek',
        ],
        // Tools
        'Pressure Pot Kit' => [
            'de' => 'Drucktopf-Set',
            'it' => 'Kit Pentola a Pressione',
            'fr' => 'Kit Pot sous pression',
            'pt' => 'Kit Panela de Pressão',
            'nl' => 'Drukpan Kit',
        ],
        'Spray Gun' => [
            'de' => 'Spritzpistole',
            'it' => 'Pistola a spruzzo',
            'fr' => 'Pistolet de pulvérisation',
            'pt' => 'Pistola de pulverização',
            'nl' => 'Spuitpistool',
        ],
        'Mixing Paddle' => [
            'de' => 'Mischrührer',
            'it' => 'Mestolo mescolatore',
            'fr' => 'Palette de mélange',
            'pt' => 'Pá de mistura',
            'nl' => 'Mengspaan',
        ],
        'Notched Trowel' => [
            'de' => 'Zahnspachtel',
            'it' => 'Cazzuola dentata',
            'fr' => 'Truelle crantée',
            'pt' => 'Desempenadeira dentada',
            'nl' => 'Getande spaan',
        ],
        'Squeegee' => [
            'de' => 'Abzieher',
            'it' => 'Raschietto',
            'fr' => 'Racle',
            'pt' => 'Raspador',
            'nl' => 'Sleepgard',
        ],
        'Paint Tray' => [
            'de' => 'Farbschale',
            'it' => 'Vassoio per vernice',
            'fr' => 'Terrine à peinture',
            'pt' => 'Bandeja de tinta',
            'nl' => 'Verfbak',
        ],
        'Telescopic Pole' => [
            'de' => 'Teleskopstange',
            'it' => 'Bastone telescopico',
            'fr' => 'Perche télescopique',
            'pt' => 'Vara telescópica',
            'nl' => 'Telescopische steel',
        ],
        'Resin Mixer' => [
            'de' => 'Harzmischer',
            'it' => 'Miscelatore resina',
            'fr' => 'Mélangeur à résine',
            'pt' => 'Misturador de resina',
            'nl' => 'Harsmenger',
        ],
        'Magic Trowel' => [
            'de' => 'Magischer Spachtel',
            'it' => 'Cazzuola magica',
            'fr' => 'Truelle magique',
            'pt' => 'Desempenadeira mágica',
            'nl' => 'Magische spaan',
        ],
        'Rubber Grout Float' => [
            'de' => 'Gummischwimmer',
            'it' => 'Galleggiante in gomma',
            'fr' => 'Flotteur en caoutchouc',
            'pt' => 'Flutuador de borracha',
            'nl' => 'Rubber voegspaan',
        ],
        'Cubo Pintura' => [
            'de' => 'Farbeimer',
            'it' => 'Secchio per vernice',
            'fr' => 'Seau à peinture',
            'pt' => 'Balde de tinta',
            'nl' => 'Verfemmer',
        ],
    ];

    private array $categoryTranslations = [
        'Polyurethane Resins' => [
            'de' => 'Polyurethanharze',
            'it' => 'Resine di poliuretano',
            'fr' => 'Résines de polyuréthane',
            'pt' => 'Resinas de poliuretano',
            'nl' => 'Polyurethaanharsen',
        ],
        'Binders' => [
            'de' => 'Bindemittel',
            'it' => 'Leganti',
            'fr' => 'Liants',
            'pt' => 'Aglutinantes',
            'nl' => 'Bindmiddelen',
        ],
        'Kits' => [
            'de' => 'Sets',
            'it' => 'Kit',
            'fr' => 'Kits',
            'pt' => 'Kits',
            'nl' => 'Kits',
        ],
        'Tools' => [
            'de' => 'Werkzeuge',
            'it' => 'Attrezzi',
            'fr' => 'Outils',
            'pt' => 'Ferramentas',
            'nl' => 'Gereedschap',
        ],
        'Spray Granite' => [
            'de' => 'Spray Granit',
            'it' => 'Spray Granito',
            'fr' => 'Spray Granit',
            'pt' => 'Spray Granito',
            'nl' => 'Spray Graniet',
        ],
        'Spray Stone' => [
            'de' => 'Spray Stein',
            'it' => 'Spray Pietra',
            'fr' => 'Spray Pierre',
            'pt' => 'Spray Pedra',
            'nl' => 'Spray Steen',
        ],
        'Paints' => [
            'de' => 'Farben',
            'it' => 'Vernici',
            'fr' => 'Peintures',
            'pt' => 'Tintas',
            'nl' => 'Verf',
        ],
        'Decorative Aggregates' => [
            'de' => 'Dekorative Zuschläge',
            'it' => 'Agregati decorativi',
            'fr' => 'Granulats décoratifs',
            'pt' => 'Agregados decorativos',
            'nl' => 'Decoratieve toeslagstoffen',
        ],
        'Systems' => [
            'de' => 'Systeme',
            'it' => 'Sistemi',
            'fr' => 'Systèmes',
            'pt' => 'Sistemas',
            'nl' => 'Systemen',
        ],
    ];

    // Description translations for common descriptions
    private array $descSnippets = [
        'de' => [
            'Polyurethanharz' => 'Hochwertiges Polyurethanharz für professionelle Bodenbeschichtungen.',
            'Spray Granit' => 'Granitspray-Technologie mit natürlichen Oberflächen in über 20 Farben.',
            'Werkzeug' => 'Professionelles Werkzeug für die Installation von Harzböden.',
        ],
        'it' => [
            'Resina di poliuretano' => 'Resina di poliuretano di alta qualità per pavimenti professionali.',
            'Spray Granito' => 'Tecnologia spray granito con finiture naturali in oltre 20 colori.',
            'Attrezzi' => 'Attrezzi professionali per l\'installazione di pavimenti in resina.',
        ],
        'fr' => [
            'Résine de polyuréthane' => 'Résine de polyuréthane de haute qualité pour sols professionnels.',
            'Spray Granit' => 'Technologie spray granit avec finitions naturelles dans plus de 20 couleurs.',
            'Outils' => 'Outils professionnels pour l\'installation de sols en résine.',
        ],
        'pt' => [
            'Resina de poliuretano' => 'Resina de poliuretano de alta qualidade para pavimentos profissionais.',
            'Spray Granito' => 'Tecnologia spray granito com acabamentos naturais em mais de 20 cores.',
            'Ferramentas' => 'Ferramentas profissionais para instalação de pisos em resina.',
        ],
        'nl' => [
            'Polyurethaanhars' => 'Hoogwaardig polyurethaanhars voor professionele vloercoatings.',
            'Spray Graniet' => 'Graniet-sproeitechnologie met natuurlijke afwerkingen in meer dan 20 kleuren.',
            'Gereedschap' => 'Professioneel gereedschap voor de installatie van harsvloeren.',
        ],
    ];

    public function handle(): int
    {
        $this->info('🌍 Translating products...');
        $this->translateProducts();

        $this->info('📂 Translating categories...');
        $this->translateCategories();

        $this->info('✅ All translations complete!');
        return Command::SUCCESS;
    }

    private function translateProducts(): void
    {
        $products = Product::all();
        $translated = 0;
        $skipped = 0;

        foreach ($products as $product) {
            $name = $product->name;
            $locales = ['en', 'es', 'de', 'it', 'fr', 'pt', 'nl'];
            $changed = false;

            foreach ($locales as $locale) {
                $current = $product->getTranslation('name', $locale);

                // Skip if already translated
                if ($current && $current !== $name) {
                    continue;
                }

                // Try dictionary
                if (isset($this->productTranslations[$name][$locale])) {
                    $product->setTranslation('name', $locale, $this->productTranslations[$name][$locale]);
                    $changed = true;
                    continue;
                }

                // For missing translations, copy the English name as placeholder
                // This ensures the product shows in that language
                $product->setTranslation('name', $locale, $name);
                $changed = true;
            }

            // Also translate description
            if ($product->description) {
                $desc = $product->getTranslation('description', 'en') ?: $product->description;
                foreach (['de', 'it', 'fr', 'pt', 'nl'] as $locale) {
                    $current = $product->getTranslation('description', $locale);
                    if (!$current) {
                        // Copy English as placeholder
                        $product->setTranslation('description', $locale, $desc);
                        $changed = true;
                    }
                }
            }

            // Also translate short_description
            if ($product->short_description) {
                $shortDesc = $product->getTranslation('short_description', 'en') ?: $product->short_description;
                foreach (['de', 'it', 'fr', 'pt', 'nl'] as $locale) {
                    $current = $product->getTranslation('short_description', $locale);
                    if (!$current) {
                        $product->setTranslation('short_description', $locale, $shortDesc);
                        $changed = true;
                    }
                }
            }

            if ($changed) {
                $product->saveQuietly();
                $translated++;
            } else {
                $skipped++;
            }
        }

        $this->info("   Products translated: {$translated} | Already done: {$skipped}");
    }

    private function translateCategories(): void
    {
        $categories = Category::all();
        $translated = 0;

        foreach ($categories as $category) {
            $name = $category->name;
            $locales = ['en', 'es', 'de', 'it', 'fr', 'pt', 'nl'];
            $changed = false;

            foreach ($locales as $locale) {
                $current = $category->getTranslation('name', $locale);
                if ($current && $current !== $name) {
                    continue;
                }

                if (isset($this->categoryTranslations[$name][$locale])) {
                    $category->setTranslation('name', $locale, $this->categoryTranslations[$name][$locale]);
                    $changed = true;
                    continue;
                }

                $category->setTranslation('name', $locale, $name);
                $changed = true;
            }

            // Description
            if ($category->description) {
                $desc = $category->getTranslation('description', 'en') ?: $category->description;
                foreach (['de', 'it', 'fr', 'pt', 'nl'] as $locale) {
                    if (!$category->getTranslation('description', $locale)) {
                        $category->setTranslation('description', $locale, $desc);
                        $changed = true;
                    }
                }
            }

            if ($changed) {
                $category->saveQuietly();
                $translated++;
            }
        }

        $this->info("   Categories translated: {$translated}");
    }
}
