<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\PlatformModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_observer_records_allowlisted_changes_without_passwords(): void
    {
        $user = User::create([
            'name' => 'Original name',
            'email' => 'audit-subject@example.com',
            'password' => 'initial-secret',
        ]);
        $user->update([
            'name' => 'Updated name',
            'password' => 'replacement-secret',
        ]);

        $created = AuditLog::query()
            ->where('event', User::class . '.created')
            ->where('subject_id', $user->id)
            ->firstOrFail();
        $updated = AuditLog::query()
            ->where('event', User::class . '.updated')
            ->where('subject_id', $user->id)
            ->firstOrFail();

        $this->assertSame('Original name', $created->new_values['name']);
        $this->assertArrayNotHasKey('password', $created->new_values);
        $this->assertSame('Original name', $updated->old_values['name']);
        $this->assertSame('Updated name', $updated->new_values['name']);
        $this->assertArrayNotHasKey('password', $updated->new_values);
    }

    public function test_role_changes_are_logged_and_audit_log_is_read_only_in_admin(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Audit Administrator',
            'email' => 'audit-admin@example.com',
            'password' => 'temporary-password',
        ]);
        $administrator->assignRole('admin');
        $subject = User::create([
            'name' => 'Audited user',
            'email' => 'audited-user@example.com',
            'password' => 'temporary-password',
        ]);

        $this->actingAs($administrator);
        app(AuditLogger::class)->recordRoleChange($subject, ['editor'], ['admin']);

        $roleEvent = AuditLog::where('event', 'user.roles_changed')->firstOrFail();
        $this->assertSame(['editor'], $roleEvent->old_values['roles']);
        $this->assertSame(['admin'], $roleEvent->new_values['roles']);
        $this->assertSame($administrator->id, $roleEvent->actor_id);

        $this->get('/admin/audit-logs')
            ->assertOk()
            ->assertSee('Bitácora de auditoría');
    }

    public function test_super_panel_module_changes_are_audited(): void
    {
        $administrator = User::create([
            'name' => 'Module Administrator',
            'email' => 'module-admin@example.com',
            'password' => 'temporary-password',
        ]);
        $this->actingAs($administrator);

        app(PlatformModules::class)->setEnabled('inventory', false);
        app(PlatformModules::class)->setEnabled('inventory', true);

        $event = AuditLog::where('event', 'platform.modules_changed')->latest('id')->firstOrFail();
        $this->assertSame(['inventory' => false], $event->old_values['modules']);
        $this->assertSame(['inventory' => true], $event->new_values['modules']);
        $this->assertSame($administrator->id, $event->actor_id);
    }
}
