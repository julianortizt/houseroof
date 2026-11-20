<?php

namespace Database\Seeders;

use App\Models\Color;
use App\Models\Page;
use App\Models\Product;
use App\Models\Category;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\Simulator\SimulatorSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles and Permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // 0. Super Admin (Administrador general)
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@costaresin.local',
            'password' => Hash::make('costaresin-admin-2026!'),
        ])->assignRole('super_admin');

        // 1. Development (Super Admin - only one who can touch code)
        User::create([
            'name' => 'Development',
            'email' => 'dev@costaresin.local',
            'password' => Hash::make('costaresin-dev-2026!'),
        ])->assignRole('super_admin');

        // 2. Manager
        User::create([
            'name' => 'Manager',
            'email' => 'manager@costaresin.local',
            'password' => Hash::make('costaresin-manager-2026!'),
        ])->assignRole('manager');

        // 3. Site Admin
        User::create([
            'name' => 'Site Admin',
            'email' => 'siteadmin@costaresin.local',
            'password' => Hash::make('costaresin-siteadmin-2026!'),
        ])->assignRole('site_admin');

        // Import real data from WordPress database
        $this->call(WordPressImporterSeeder::class);

        // Legal pages
        $this->call(PageSeeder::class);

        // Settings
        $this->call(SettingsSeeder::class);

        // Attach images via Spatie Media Library
        $this->call(MediaSeeder::class);

        // Simulator 3D demo data
        $this->call(SimulatorSeeder::class);
    }
}
