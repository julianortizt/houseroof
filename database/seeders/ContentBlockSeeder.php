<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class ContentBlockSeeder extends Seeder
{
    public function run(): void
    {
        $blocks = [
            // ===== HOMEPAGE =====
            // Hero Slider
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_title', 'value' => 'Resinas de alta calidad para pavimentos decorativos', 'type' => 'text', 'locale' => 'es', 'sort_order' => 1],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_title', 'value' => 'High quality resins for decorative pavements', 'type' => 'text', 'locale' => 'en', 'sort_order' => 1],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_subtitle', 'value' => 'Fabricamos y distribuimos resinas de poliuretano, chips de granito y áridos decorativos. Sede en Benissa, Alicante.', 'type' => 'text', 'locale' => 'es', 'sort_order' => 2],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_subtitle', 'value' => 'We manufacture and distribute polyurethane resins, granite chips and decorative aggregates. Based in Benissa, Alicante.', 'type' => 'text', 'locale' => 'en', 'sort_order' => 2],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_image', 'value' => 'images/factory-benissa.jpg', 'type' => 'image', 'locale' => 'es', 'sort_order' => 3],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_badge', 'value' => '🏭 Fábrica en España', 'type' => 'text', 'locale' => 'es', 'sort_order' => 4],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_badge', 'value' => '🏭 Factory in Spain', 'type' => 'text', 'locale' => 'en', 'sort_order' => 4],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_cta_text', 'value' => 'Comprar ahora', 'type' => 'text', 'locale' => 'es', 'sort_order' => 5],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_cta_text', 'value' => 'Shop now', 'type' => 'text', 'locale' => 'en', 'sort_order' => 5],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_1_cta_url', 'value' => '/tienda', 'type' => 'text', 'locale' => 'es', 'sort_order' => 6],

            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_title', 'value' => 'Instalación profesional de pavimentos en resina', 'type' => 'text', 'locale' => 'es', 'sort_order' => 10],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_title', 'value' => 'Professional resin pavement installation', 'type' => 'text', 'locale' => 'en', 'sort_order' => 10],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_subtitle', 'value' => 'Equipo experto en instalación de suelos epóxicos, poliuretano y chips de granito para exteriores e interiores.', 'type' => 'text', 'locale' => 'es', 'sort_order' => 11],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_subtitle', 'value' => 'Our expert team installs epoxy, polyurethane and granite chip flooring for indoor and outdoor spaces.', 'type' => 'text', 'locale' => 'en', 'sort_order' => 11],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_image', 'value' => 'images/industrial-floor.jpg', 'type' => 'image', 'locale' => 'es', 'sort_order' => 13],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_badge', 'value' => '🔧 Servicio de Instalación', 'type' => 'text', 'locale' => 'es', 'sort_order' => 14],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_badge', 'value' => '🔧 Installation Service', 'type' => 'text', 'locale' => 'en', 'sort_order' => 14],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_cta_text', 'value' => 'Solicitar cotización', 'type' => 'text', 'locale' => 'es', 'sort_order' => 15],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_2_cta_url', 'value' => '/cotizacion', 'type' => 'text', 'locale' => 'es', 'sort_order' => 16],

            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_3_title', 'value' => 'Chips de granito en más de 20 colores', 'type' => 'text', 'locale' => 'es', 'sort_order' => 20],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_3_title', 'value' => 'Granite chips in more than 20 colours', 'type' => 'text', 'locale' => 'en', 'sort_order' => 20],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_3_subtitle', 'value' => 'Granite chip spray technology with natural finishes in more than 20 colours.', 'type' => 'text', 'locale' => 'en', 'sort_order' => 21],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_3_image', 'value' => 'images/service-granite.jpg', 'type' => 'image', 'locale' => 'es', 'sort_order' => 23],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_3_badge', 'value' => '🎨 Spray Granite', 'type' => 'text', 'locale' => 'es', 'sort_order' => 24],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_3_badge', 'value' => '🎨 Spray Granite', 'type' => 'text', 'locale' => 'en', 'sort_order' => 24],
            ['page' => 'home', 'section' => 'hero', 'key' => 'slide_3_cta_text', 'value' => 'Ver productos', 'type' => 'text', 'locale' => 'es', 'sort_order' => 25],

            // CTA Section
            ['page' => 'home', 'section' => 'cta', 'key' => 'title', 'value' => '¿Necesita un presupuesto para su proyecto?', 'type' => 'text', 'locale' => 'es', 'sort_order' => 1],
            ['page' => 'home', 'section' => 'cta', 'key' => 'title', 'value' => 'Need a quote for your project?', 'type' => 'text', 'locale' => 'en', 'sort_order' => 1],
            ['page' => 'home', 'section' => 'cta', 'key' => 'subtitle', 'value' => 'Contacte con nosotros para obtener una cotización personalizada. Asesoramiento técnico gratuito para profesionales y particulares.', 'type' => 'text', 'locale' => 'es', 'sort_order' => 2],
            ['page' => 'home', 'section' => 'cta', 'key' => 'subtitle', 'value' => 'Contact us for a personalised quote. Free technical advice for professionals and private customers.', 'type' => 'text', 'locale' => 'en', 'sort_order' => 2],
            ['page' => 'home', 'section' => 'cta', 'key' => 'button_text', 'value' => 'Solicitar cotización', 'type' => 'text', 'locale' => 'es', 'sort_order' => 3],
            ['page' => 'home', 'section' => 'cta', 'key' => 'button_text', 'value' => 'Request a quote', 'type' => 'text', 'locale' => 'en', 'sort_order' => 3],

            // Services Section
            ['page' => 'home', 'section' => 'services_section', 'key' => 'resin_title', 'value' => 'Instalación de Resina Epóxica', 'type' => 'text', 'locale' => 'es', 'sort_order' => 1],
            ['page' => 'home', 'section' => 'services_section', 'key' => 'resin_title', 'value' => 'Epoxy Resin Installation', 'type' => 'text', 'locale' => 'en', 'sort_order' => 1],
            ['page' => 'home', 'section' => 'services_section', 'key' => 'resin_description', 'value' => 'Pavimentos decorativos de resina epóxica para terrazas, garajes, naves industriales y espacios comerciales.', 'type' => 'text', 'locale' => 'es', 'sort_order' => 2],
            ['page' => 'home', 'section' => 'services_section', 'key' => 'resin_image', 'value' => 'images/service-resin.jpg', 'type' => 'image', 'locale' => 'es', 'sort_order' => 3],
            ['page' => 'home', 'section' => 'services_section', 'key' => 'granite_title', 'value' => 'Chips de Granito y Áridos', 'type' => 'text', 'locale' => 'es', 'sort_order' => 4],
            ['page' => 'home', 'section' => 'services_section', 'key' => 'granite_title', 'value' => 'Granite Chips & Aggregates', 'type' => 'text', 'locale' => 'en', 'sort_order' => 4],
            ['page' => 'home', 'section' => 'services_section', 'key' => 'granite_description', 'value' => 'Aplicación de chips de granito y áridos decorativos con tecnología de pulverización. Acabados naturales en más de 20 colores.', 'type' => 'text', 'locale' => 'es', 'sort_order' => 5],
            ['page' => 'home', 'section' => 'services_section', 'key' => 'granite_image', 'value' => 'images/service-granite.jpg', 'type' => 'image', 'locale' => 'es', 'sort_order' => 6],

            // Value Proposition
            ['page' => 'home', 'section' => 'value_prop', 'key' => 'title', 'value' => '¿Por qué elegir Costa Resin & Construction?', 'type' => 'text', 'locale' => 'es', 'sort_order' => 1],
            ['page' => 'home', 'section' => 'value_prop', 'key' => 'title', 'value' => 'Why choose Costa Resin & Construction?', 'type' => 'text', 'locale' => 'en', 'sort_order' => 1],
            ['page' => 'home', 'section' => 'value_prop', 'key' => 'subtitle', 'value' => 'Somos fabricantes directos con sede en Benissa, Alicante. Productos de calidad premium con envío a toda la Unión Europea.', 'type' => 'text', 'locale' => 'es', 'sort_order' => 2],

            // ===== ABOUT PAGE =====
            ['page' => 'about', 'section' => 'hero', 'key' => 'title', 'value' => 'Sobre Nosotros', 'type' => 'text', 'locale' => 'es', 'sort_order' => 1],
            ['page' => 'about', 'section' => 'hero', 'key' => 'title', 'value' => 'About Us', 'type' => 'text', 'locale' => 'en', 'sort_order' => 1],
            ['page' => 'about', 'section' => 'story', 'key' => 'title', 'value' => 'Nuestra Historia', 'type' => 'text', 'locale' => 'es', 'sort_order' => 1],
            ['page' => 'about', 'section' => 'story', 'key' => 'title', 'value' => 'Our Story', 'type' => 'text', 'locale' => 'en', 'sort_order' => 1],

            // ===== SERVICES PAGE =====
            ['page' => 'services', 'section' => 'hero', 'key' => 'title', 'value' => 'Nuestros Servicios', 'type' => 'text', 'locale' => 'es', 'sort_order' => 1],
            ['page' => 'services', 'section' => 'hero', 'key' => 'title', 'value' => 'Our Services', 'type' => 'text', 'locale' => 'en', 'sort_order' => 1],

            // ===== GLOBAL =====
            ['page' => 'global', 'section' => 'whatsapp', 'key' => 'phone', 'value' => '+34654195493', 'type' => 'text', 'locale' => 'es', 'sort_order' => 1],
            ['page' => 'global', 'section' => 'whatsapp', 'key' => 'message', 'value' => 'Hola, me interesa información sobre sus productos y servicios.', 'type' => 'text', 'locale' => 'es', 'sort_order' => 2],
            ['page' => 'global', 'section' => 'whatsapp', 'key' => 'message', 'value' => 'Hello, I am interested in information about your products and services.', 'type' => 'text', 'locale' => 'en', 'sort_order' => 2],
        ];

        foreach ($blocks as $block) {
            ContentBlock::updateOrCreate(
                [
                    'page' => $block['page'],
                    'section' => $block['section'],
                    'key' => $block['key'],
                    'locale' => $block['locale'],
                ],
                [
                    'value' => $block['value'],
                    'type' => $block['type'],
                    'sort_order' => $block['sort_order'] ?? 0,
                ]
            );
        }

        $this->command->info('Content blocks seeded: ' . count($blocks) . ' blocks');
    }
}
