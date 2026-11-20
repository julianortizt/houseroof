<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'House Roof', 'type' => 'text'],
            ['key' => 'site_tagline', 'value' => 'Limpieza, mantenimiento y reparación de techos en Europa', 'type' => 'text'],
            ['key' => 'site_description', 'value' => 'Servicios profesionales de limpieza, mantenimiento y reparación de cubiertas para clientes residenciales, comerciales e industriales en toda Europa.', 'type' => 'textarea'],
            ['key' => 'site_email', 'value' => 'hello@houseroof.com', 'type' => 'text'],
            ['key' => 'site_phone', 'value' => '+34 900 000 000', 'type' => 'text'],
            ['key' => 'site_whatsapp', 'value' => '+34900000000', 'type' => 'text'],
            ['key' => 'site_address', 'value' => 'Calle Principal 123, 28001 Madrid, España', 'type' => 'text'],
            ['key' => 'company_email', 'value' => 'hello@houseroof.com', 'type' => 'text'],
            ['key' => 'logo_path', 'value' => 'site/house-roof-logo.svg', 'type' => 'image'],
            ['key' => 'logo_white_path', 'value' => 'site/house-roof-logo-white.svg', 'type' => 'image'],
            ['key' => 'favicon_path', 'value' => 'site/favicon.png', 'type' => 'image'],
            ['key' => 'homepage_template', 'value' => 'roof', 'type' => 'text'],
            ['key' => 'seo_title', 'value' => 'House Roof — Limpieza y reparación de techos en Europa', 'type' => 'text'],
            ['key' => 'seo_description', 'value' => 'Limpieza, mantenimiento y reparación de techos en Europa. Inspección, impermeabilización y urgencias para cubiertas residenciales, comerciales e industriales.', 'type' => 'textarea'],
            ['key' => 'seo_keywords', 'value' => 'limpieza de techos, mantenimiento de cubiertas, reparación de goteras, impermeabilización, servicios de techos Europa', 'type' => 'text'],
            ['key' => 'seo_og_image', 'value' => 'site/og-default.jpg', 'type' => 'image'],
            ['key' => 'whatsapp_number', 'value' => '+34900000000', 'type' => 'text'],
            ['key' => 'free_shipping_threshold', 'value' => '500', 'type' => 'text'],
            ['key' => 'default_shipping_cost', 'value' => '15.00', 'type' => 'text'],
            ['key' => 'tax_rate', 'value' => '21', 'type' => 'text'],
            ['key' => 'stripe_key', 'value' => '', 'type' => 'text'],
            ['key' => 'facebook_url', 'value' => '', 'type' => 'text'],
            ['key' => 'instagram_url', 'value' => '', 'type' => 'text'],
            ['key' => 'youtube_url', 'value' => '', 'type' => 'text'],
            ['key' => 'footer_company', 'value' => 'House Roof', 'type' => 'text'],
            ['key' => 'footer_copyright', 'value' => '© ' . date('Y') . ' House Roof. Todos los derechos reservados.', 'type' => 'text'],
            ['key' => 'hero_playback_rate', 'value' => '0.625', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
