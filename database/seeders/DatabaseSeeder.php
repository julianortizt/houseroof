<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Simulator\SimulatorSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles and Permissions
        $this->call(RolesAndPermissionsSeeder::class);

        $accounts = [
            ['name' => 'Super Admin', 'email' => 'admin@costaresin.local', 'password' => 'costaresin-admin-2026!', 'role' => 'super_admin'],
            ['name' => 'Development', 'email' => 'dev@costaresin.local', 'password' => 'costaresin-dev-2026!', 'role' => 'super_admin'],
            ['name' => 'Manager', 'email' => 'manager@costaresin.local', 'password' => 'costaresin-manager-2026!', 'role' => 'manager'],
            ['name' => 'Site Admin', 'email' => 'siteadmin@costaresin.local', 'password' => 'costaresin-siteadmin-2026!', 'role' => 'site_admin'],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => Hash::make($account['password'])],
            );
            $user->syncRoles([$account['role']]);
        }

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
