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
            ['name' => 'Super Admin', 'email' => 'admin@houseroof.com', 'password' => '0d3452938e9c4eb0af90d588a7ffb068', 'role' => 'super_admin'],
            ['name' => 'Development', 'email' => 'dev@houseroof.com', 'password' => 'efe137134a1f4443932f27fa581131df', 'role' => 'super_admin'],
            ['name' => 'Manager', 'email' => 'manager@houseroof.com', 'password' => 'f2e7e986b49845a4887ebe7a4b8f52e6', 'role' => 'manager'],
            ['name' => 'Site Admin', 'email' => 'siteadmin@houseroof.com', 'password' => '0af1fd54584b481981b08bab9ce0b489', 'role' => 'site_admin'],
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
