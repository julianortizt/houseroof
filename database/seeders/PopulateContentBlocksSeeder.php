<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use Illuminate\Database\Seeder;

/**
 * Populates every editable content block from config/roof-content.php.
 * The same values are used by the frontend as per-locale fallbacks.
 */
class PopulateContentBlocksSeeder extends Seeder
{
    public function run(): void
    {
        $locales = config('roof-content.locales', ['es', 'en', 'de', 'it', 'fr', 'pt', 'nl']);
        $blocks = config('roof-content.blocks', []);
        $count = 0;

        foreach ($blocks as $page => $sections) {
            foreach ($sections as $section => $keys) {
                $order = 0;
                foreach ($keys as $key => $translations) {
                    $type = $this->typeFor($key);
                    foreach ($locales as $locale) {
                        $value = $translations[$locale] ?? $translations['en'] ?? '';
                        if ($value === '') {
                            continue;
                        }
                        ContentBlock::set($page, $section, $key, $value, $type, $locale, $order++);
                        $count++;
                    }
                }
            }
        }

        $this->seedLegacyTemplateKeys($locales);

        $this->command?->info("Content blocks populated: {$count} rows for House Roof.");
    }

    protected function typeFor(string $key): string
    {
        return match (true) {
            str_ends_with($key, '_image') => 'image',
            str_contains($key, 'content'),
            str_contains($key, '_text'),
            str_ends_with($key, '_subtitle'),
            str_ends_with($key, '_note') => 'richtext',
            default => 'text',
        };
    }

    /**
     * The optional "standard" homepage/template reads older block keys. Keep
     * them in sync so no resin-era copy can ever surface again.
     */
    protected function seedLegacyTemplateKeys(array $locales): void
    {
        foreach ($locales as $locale) {
            $home = fn (string $key): string => (string) (config("roof-content.blocks.home.roof.{$key}.{$locale}")
                ?? config("roof-content.blocks.home.roof.{$key}.en")
                ?? '');

            $map = [
                ['home', 'hero', 'slide_1_title', 'hero_title'],
                ['home', 'hero', 'slide_1_subtitle', 'hero_subtitle'],
                ['home', 'hero', 'slide_1_cta_text', 'hero_primary_cta'],
                ['home', 'hero', 'slide_1_cta_url', 'hero_primary_url'],
                ['home', 'services_section', 'resin_title', 'service_1_title'],
                ['home', 'services_section', 'resin_description', 'service_1_text'],
                ['home', 'services_section', 'granite_title', 'service_2_title'],
                ['home', 'services_section', 'granite_description', 'service_2_text'],
                ['home', 'value_prop', 'title', 'why_title'],
                ['home', 'value_prop', 'subtitle', 'why_subtitle'],
                ['home', 'cta', 'title', 'cta_title'],
                ['home', 'cta', 'subtitle', 'cta_subtitle'],
                ['home', 'cta', 'button_text', 'cta_button'],
            ];

            foreach ($map as [$page, $section, $legacyKey, $newKey]) {
                $value = $home($newKey);
                if ($value !== '') {
                    ContentBlock::set($page, $section, $legacyKey, $value, 'text', $locale);
                }
            }

            foreach ([1, 2, 3, 4] as $n) {
                $value = $home("trust_{$n}");
                if ($value !== '') {
                    ContentBlock::set('home', 'trust_bar', "item_{$n}", $value, 'text', $locale);
                }
            }
        }
    }
}
