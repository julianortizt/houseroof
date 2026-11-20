<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

class PopulateContentBlocksSeeder extends Seeder
{
    public function run(): void
    {
        // Helper to set for all 7 locales
        $setAll = function (string $page, string $section, string $key, string $value, string $type = 'text', int $sortOrder = 0) {
            foreach (['es', 'en', 'de', 'it', 'fr', 'pt', 'nl'] as $locale) {
                ContentBlock::set($page, $section, $key, $value, $type, $locale, $sortOrder);
            }
        };

        // ════════════════════════════════════════════
        // ABOUT PAGE
        // ════════════════════════════════════════════

        // Hero
        $setAll('about', 'hero', 'title', 'About Us', 'text', 0);
        $setAll('about', 'hero', 'subtitle', 'Costa Resin & Construction — Manufacturer and distributor of polyurethane resins, granite chips and decorative aggregates for pavements.', 'text', 1);

        // Story
        $setAll('about', 'story', 'title', 'Our Story', 'text', 10);
        $setAll('about', 'story', 'content', '<p><strong>Costa Resin & Construction</strong> was built to provide high-quality resin, aggregate, and pavement solutions for residential and commercial projects.</p><p>Our team blends technical expertise with reliable materials and professional installation support so each project is durable, attractive, and easy to maintain.</p><p>From initial specification to final finish, we focus on practical performance, aesthetic consistency, and long-term value.</p>', 'richtext', 11);
        $setAll('about', 'story', 'image', '', 'image', 12);

        // Values
        $setAll('about', 'values', 'title', 'Why Choose Us', 'text', 20);
        $setAll('about', 'values', 'value_1_title', 'On-site Manufacturing', 'text', 21);
        $setAll('about', 'values', 'value_1_text', 'All our products are manufactured at our plant in Benissa, Alicante, guaranteeing full quality control.', 'richtext', 22);
        $setAll('about', 'values', 'value_2_title', 'European Shipping', 'text', 23);
        $setAll('about', 'values', 'value_2_text', 'We ship across the European Union. Free shipping on orders over 500 euros to mainland Spain.', 'richtext', 24);
        $setAll('about', 'values', 'value_3_title', 'Professional Installation', 'text', 25);
        $setAll('about', 'values', 'value_3_text', 'Our expert team installs resin pavements across Spain. Request a no-obligation quote.', 'richtext', 26);

        // CTA
        $setAll('about', 'cta', 'title', 'Need a quote for your project?', 'text', 30);
        $setAll('about', 'cta', 'subtitle', 'Request a quote or contact us for any enquiry', 'text', 31);
        $setAll('about', 'cta', 'button_text', 'Request quote', 'text', 32);

        // ════════════════════════════════════════════
        // SERVICES PAGE
        // ════════════════════════════════════════════

        $setAll('services', 'hero', 'title', 'Installation Services', 'text', 0);
        $setAll('services', 'hero', 'subtitle', 'Professional installation of epoxy resin and granite chip pavements throughout Spain', 'text', 1);

        $setAll('services', 'resin', 'badge', 'Service 1', 'text', 10);
        $setAll('services', 'resin', 'title', 'Epoxy Resin Pavement Installation', 'text', 11);
        $setAll('services', 'resin', 'content', '<p>Our team of professionals installs high-quality epoxy resin pavements for all types of spaces: terraces, garages, industrial warehouses, commercial premises, and residential areas.</p><p>We use polyurethane and epoxy resins manufactured at our plant in Benissa, Alicante, guaranteeing durable and aesthetically superior results.</p>', 'richtext', 12);
        $setAll('services', 'resin', 'image', '', 'image', 13);

        $setAll('services', 'granite', 'badge', 'Service 2', 'text', 20);
        $setAll('services', 'granite', 'title', 'Spray Granite / Decorative Aggregates', 'text', 21);
        $setAll('services', 'granite', 'content', '<p>Granite chip spray technology that creates natural, durable finishes on facades, floors, furniture, and all types of surfaces.</p><p>Available in more than 20 natural aggregate colors: from classic white to red Alicante, cream marble, Spanish quartz, and many more.</p>', 'richtext', 22);
        $setAll('services', 'granite', 'image', '', 'image', 23);

        $setAll('services', 'cta', 'title', 'Request a quote?', 'text', 30);
        $setAll('services', 'cta', 'text', 'Fill in the quote form with the details of your project and we will send you a personalized quote with no obligation.', 'richtext', 31);
        $setAll('services', 'cta', 'button_text', 'Free quote request', 'text', 32);

        // ════════════════════════════════════════════
        // CONTACT PAGE
        // ════════════════════════════════════════════

        $setAll('contact', 'hero', 'title', 'Contact', 'text', 0);
        $setAll('contact', 'hero', 'subtitle', 'Request a quote or contact us for any enquiry', 'text', 1);
        $setAll('contact', 'info', 'form_title', 'Request an installation quote', 'text', 10);
        $setAll('contact', 'info', 'address', 'C/ industrial, s/n<br>03720 Benissa (Alicante)<br>Spain', 'text', 11);
        $setAll('contact', 'info', 'phone', '+34 654 195 493', 'text', 12);
        $setAll('contact', 'info', 'email', 'hello@TU_DOMINIO.com', 'text', 13);

        $this->command->info('ContentBlocks populated for about, services, and contact pages.');
    }
}
