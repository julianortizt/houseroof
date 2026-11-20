<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Safe, self-contained seeder used by the visual web installer.
 *
 * It loads only the data required for a fresh install to work:
 * roles & permissions, store settings, legal pages, product categories
 * and decorative colours.
 *
 * It deliberately does NOT run the WordPress importer or the media seeder,
 * which depend on an external database / uploaded image files that are not
 * present on a clean Hostinger deployment.
 */
class InstallSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(PageSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ColorSeeder::class);
    }
}
