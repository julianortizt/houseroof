<?php

namespace Tests\Feature;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserAdministrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_the_configured_developer_can_assign_super_admin(): void
    {
        config(['platform.developer_email' => 'developer@example.com']);
        $siteAdminRole = Role::create(['name' => 'site_admin', 'guard_name' => 'web']);
        $superAdminRole = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $siteAdmin = User::create([
            'name' => 'Site administrator',
            'email' => 'admin@example.com',
            'password' => 'temporary-password',
        ]);
        $siteAdmin->assignRole($siteAdminRole);

        $this->actingAs($siteAdmin);
        $this->expectException(ValidationException::class);
        UserResource::validatedRoleIds([$superAdminRole->id]);
    }

    public function test_developer_can_assign_super_admin_and_protected_users_cannot_be_deleted(): void
    {
        config(['platform.developer_email' => 'developer@example.com']);
        $superAdminRole = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'temporary-password',
        ]);
        $lastSuperAdmin = User::create([
            'name' => 'Super administrator',
            'email' => 'super-admin@example.com',
            'password' => 'temporary-password',
        ]);
        $lastSuperAdmin->assignRole($superAdminRole);

        $this->actingAs($developer);

        $this->assertSame(
            [(int) $superAdminRole->id],
            UserResource::validatedRoleIds([$superAdminRole->id]),
        );
        $this->assertFalse(UserResource::canDelete($developer));
        $this->assertFalse(UserResource::canDelete($lastSuperAdmin));

        $anotherSuperAdmin = User::create([
            'name' => 'Another super administrator',
            'email' => 'another-super-admin@example.com',
            'password' => 'temporary-password',
        ]);
        $anotherSuperAdmin->assignRole($superAdminRole);

        $this->assertTrue(UserResource::canDelete($anotherSuperAdmin));
    }

    public function test_non_developer_cannot_create_or_rename_a_user_to_the_developer_identity(): void
    {
        config(['platform.developer_email' => 'developer@example.com']);
        $siteAdminRole = Role::create(['name' => 'site_admin', 'guard_name' => 'web']);
        $siteAdmin = User::create([
            'name' => 'Site administrator',
            'email' => 'admin@example.com',
            'password' => 'temporary-password',
        ]);
        $siteAdmin->assignRole($siteAdminRole);

        $this->actingAs($siteAdmin);
        $this->expectException(ValidationException::class);
        UserResource::assertCanUseDeveloperEmail('DEVELOPER@example.com');
    }

    public function test_regular_admin_can_open_user_management_without_a_super_admin_option(): void
    {
        Role::create(['name' => 'site_admin', 'guard_name' => 'web']);
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $siteAdmin = User::create([
            'name' => 'Site administrator',
            'email' => 'admin@example.com',
            'password' => 'temporary-password',
        ]);
        $siteAdmin->assignRole('site_admin');

        $this->actingAs($siteAdmin)
            ->get('/admin/users/create')
            ->assertOk()
            ->assertDontSee('super_admin');
    }
}
