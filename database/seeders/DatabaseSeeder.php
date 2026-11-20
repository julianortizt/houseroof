<?php

namespace Database\Seeders;

use Database\Seeders\Simulator\SimulatorSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles and Permissions
        $this->call(RolesAndPermissionsSeeder::class);

        $this->call(HouseRoofAdminSeeder::class);

        // The legacy WordPress import requires a separate MySQL database and
        // MySQL-only FK statements. It is opt-in so fresh Turso/SQLite installs
        // can seed successfully.
        if (env('SEED_WORDPRESS', false)) {
            $this->call(WordPressImporterSeeder::class);
        }

        // Legal pages, settings and all editable House Roof copy.
        $this->call(PageSeeder::class);
        $this->call(SettingsSeeder::class);
        $this->call(ContentBlockSeeder::class);

        // Legacy media attachment and 3D simulator demo data.
        $this->call(MediaSeeder::class);
        $this->call(SimulatorSeeder::class);
    }
}
