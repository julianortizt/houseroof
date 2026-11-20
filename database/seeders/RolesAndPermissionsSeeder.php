<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view_dashboard',
            'manage products',
            'manage categories',
            'manage projects',
            'manage blog posts',
            'manage orders',
            'manage quotes',
            'manage work orders',
            'manage inventory',
            'manage invoicing',
            'manage accounting',
            'manage payments',
            'manage simulator',
            'manage customer portal',
            'manage administration',
            'manage colors',
            'manage pages',
            'manage settings',
            'manage users',
            'manage site_settings',
            'manage maintenance',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $allPermissions = Permission::all();

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions($allPermissions);
        $superAdmin->givePermissionTo($allPermissions);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'view_dashboard',
            'manage products',
            'manage inventory',
            'manage quotes',
            'manage work orders',
            'manage invoicing',
            'manage accounting',
            'manage payments',
            'manage simulator',
            'manage customer portal',
            'manage projects',
            'manage orders',
            'manage categories',
            'manage pages',
            'manage administration',
            'manage colors',
        ]);

        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view_dashboard',
            'manage products',
            'manage inventory',
            'manage quotes',
            'manage work orders',
            'manage invoicing',
            'manage accounting',
            'manage payments',
            'manage customer portal',
            'manage projects',
            'manage orders',
            'manage categories',
            'manage pages',
            'manage colors',
            'manage administration',
        ]);

        $siteAdmin = Role::firstOrCreate(['name' => 'site_admin', 'guard_name' => 'web']);
        $siteAdmin->syncPermissions([
            'view_dashboard',
            'manage products',
            'manage categories',
            'manage projects',
            'manage blog posts',
            'manage orders',
            'manage quotes',
            'manage work orders',
            'manage inventory',
            'manage invoicing',
            'manage payments',
            'manage colors',
            'manage pages',
            'manage customer portal',
        ]);
    }
}
