<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Costa Resin & Construction', 'type' => 'text'],
            ['key' => 'site_tagline', 'value' => 'Soluciones de resinas y pavimentos decorativos', 'type' => 'text'],
            ['key' => 'site_description', 'value' => 'Fabricación y distribución de resinas, áridos decorativos y soluciones para pavimentos profesionales.', 'type' => 'textarea'],
            ['key' => 'company_address', 'value' => 'Calle Principal 123, 28001 Madrid, España', 'type' => 'text'],
            ['key' => 'company_phone', 'value' => '+34 900 000 000', 'type' => 'text'],
            ['key' => 'company_email', 'value' => 'hello@TU_DOMINIO.com', 'type' => 'text'],
            ['key' => 'whatsapp_number', 'value' => '+34600000000', 'type' => 'text'],
            ['key' => 'free_shipping_threshold', 'value' => '500', 'type' => 'text'],
            ['key' => 'default_shipping_cost', 'value' => '15.00', 'type' => 'text'],
            ['key' => 'tax_rate', 'value' => '21', 'type' => 'text'],
            ['key' => 'stripe_key', 'value' => '', 'type' => 'text'],
            ['key' => 'facebook_url', 'value' => '', 'type' => 'text'],
            ['key' => 'instagram_url', 'value' => '', 'type' => 'text'],
            ['key' => 'youtube_url', 'value' => '', 'type' => 'text'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
