<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ContentBlock;

class TranslateContentBlocks extends Command
{
    protected $signature = 'translate:content-blocks';
    protected $description = 'Create content blocks for all 5 new locales';

    private array $blockTranslations = [
        'home' => [
            'hero' => [
                'slide_1_title' => ['de'=>'Fabrik in Spanien','it'=>'Fabbrica in Spagna','fr'=>'Fabrique en Espagne','pt'=>'Fábrica na Espanha','nl'=>'Fabriek in Spanje'],
                'slide_1_subtitle' => ['de'=>'Polyurethanharze, Granitflakes und dekorative Zuschläge.','it'=>'Resine di poliuretano, chips di granito e aggregati decorativi.','fr'=>'Résines de polyuréthane, éclats de granit et granulats décoratifs.','pt'=>'Resinas de poliuretano, lascas de granito e agregados decorativos.','nl'=>'Polyurethaanharsen, granietchips en decoratieve toeslagstoffen.'],
                'slide_1_cta_text' => ['de'=>'Jetzt kaufen','it'=>'Acquista ora','fr'=>'Acheter maintenant','pt'=>'Comprar agora','nl'=>'Nu kopen'],
                'slide_2_title' => ['de'=>'Installationsdienst','it'=>'Servizio di Installazione','fr'=>'Service d\'installation','pt'=>'Serviço de Instalação','nl'=>'Installatieservice'],
                'slide_2_subtitle' => ['de'=>'Unser Team installiert Harzböden in ganz Spanien.','it'=>'Il nostro team installa pavimenti in resina in tutta Spagna.','fr'=>'Notre équipe installe des sols en résine dans toute l\'Espagne.','pt'=>'Nossa equipe instala pisos de resina em toda a Espanha.','nl'=>'Ons team installeert harsvloeren in heel Spanje.'],
                'slide_2_cta_text' => ['de'=>'Angebot anfordern','it'=>'Richiedi preventivo','fr'=>'Demander un devis','pt'=>'Solicitar cotação','nl'=>'Offerte aanvragen'],
                'slide_3_title' => ['de'=>'Spray Granit','it'=>'Spray Granito','fr'=>'Spray Granit','pt'=>'Spray Granito','nl'=>'Spray Graniet'],
                'slide_3_subtitle' => ['de'=>'Technologie mit natürlichen Oberflächen in über 20 Farben.','it'=>'Tecnologia con finiture naturali in oltre 20 colori.','fr'=>'Technologie avec finitions naturelles dans plus de 20 couleurs.','pt'=>'Tecnologia com acabamentos naturais em mais de 20 cores.','nl'=>'Technologie met natuurlijke afwerkingen in meer dan 20 kleuren.'],
                'slide_3_cta_text' => ['de'=>'Produkte ansehen','it'=>'Vedi prodotti','fr'=>'Voir les produits','pt'=>'Ver produtos','nl'=>'Bekijk producten'],
            ],
            'cta' => [
                'title' => ['de'=>'Bereit zu bestellen?','it'=>'Pronto per ordinare?','fr'=>'Prêt à commander?','pt'=>'Pronto para encomendar?','nl'=>'Klaar om te bestellen?'],
                'subtitle' => ['de'=>'Kontaktieren Sie uns für ein Angebot','it'=>'Contattaci per un preventivo','fr'=>'Contactez-nous pour un devis','pt'=>'Entre em contato para uma cotação','nl'=>'Neem contact op voor een offerte'],
                'button_text' => ['de'=>'Jetzt anfragen','it'=>'Richiedi ora','fr'=>'Demander maintenant','pt'=>'Solicitar agora','nl'=>'Nu aanvragen'],
            ],
        ],
        'about' => [
            'hero' => [
                'title' => ['de'=>'Über uns','it'=>'Chi siamo','fr'=>'À propos de nous','pt'=>'Sobre nós','nl'=>'Over ons'],
                'subtitle' => ['de'=>'Harzlösungen aus Spanien','it'=>'Soluzioni in resina dalla Spagna','fr'=>'Solutions en résine depuis l\'Espagne','pt'=>'Soluções em resina da Espanha','nl'=>'Harsoplossingen uit Spanje'],
            ],
        ],
        'services' => [
            'hero' => [
                'title' => ['de'=>'Unsere Dienstleistungen','it'=>'I nostri servizi','fr'=>'Nos services','pt'=>'Nossos serviços','nl'=>'Onze diensten'],
                'subtitle' => ['de'=>'Professionelle Installation','it'=>'Installazione professionale','fr'=>'Installation professionnelle','pt'=>'Instalação profissional','nl'=>'Professionele installatie'],
            ],
        ],
    ];

    public function handle(): int
    {
        $locales = ['de', 'it', 'fr', 'pt', 'nl'];
        $created = 0;

        foreach ($locales as $locale) {
            foreach ($this->blockTranslations as $page => $sections) {
                foreach ($sections as $section => $fields) {
                    foreach ($fields as $key => $translations) {
                        $exists = ContentBlock::where('page', $page)
                            ->where('section', $section)
                            ->where('key', $key)
                            ->where('locale', $locale)
                            ->exists();

                        if (!$exists) {
                            ContentBlock::create([
                                'page' => $page,
                                'section' => $section,
                                'key' => $key,
                                'value' => $translations[$locale],
                                'type' => 'text',
                                'locale' => $locale,
                                'sort_order' => 0,
                            ]);
                            $created++;
                        }
                    }
                }
            }
        }

        $this->info("Content blocks created for 5 locales: {$created} new blocks");
        return Command::SUCCESS;
    }
}
