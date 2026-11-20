<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HouseRoofAdminSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Super Admin', 'email' => 'admin@houseroof.com', 'password' => '0d3452938e9c4eb0af90d588a7ffb068', 'role' => 'super_admin'],
            ['name' => 'Development', 'email' => 'dev@houseroof.com', 'password' => 'efe137134a1f4443932f27fa581131df', 'role' => 'super_admin'],
            ['name' => 'Manager', 'email' => 'manager@houseroof.com', 'password' => 'f2e7e986b49845a4887ebe7a4b8f52e6', 'role' => 'manager'],
            ['name' => 'Site Admin', 'email' => 'siteadmin@houseroof.com', 'password' => '0af1fd54584b481981b08bab9ce0b489', 'role' => 'site_admin'],
        ];

        // libSQL's FFI driver can crash after several role-sync statements in
        // one process. Deployments seed one account per process instead.
        $only = env('HOUSE_ROOF_ADMIN_ONLY');
        if (is_string($only) && $only !== '') {
            $accounts = array_values(array_filter(
                $accounts,
                static fn (array $account): bool => $account['email'] === $only,
            ));
        }

        foreach ($accounts as $account) {
            $user = User::firstOrCreate(
                ['email' => $account['email']],
                ['name' => $account['name'], 'password' => Hash::make($account['password'])],
            );
            $user->syncRoles([$account['role']]);
        }
    }
}
