<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\FiscalProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FiscalProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_fiscal_profile_can_enable_actual_legal_issuance(): void
    {
        $profile = app(FiscalProfile::class);
        $profile->save([
            'legal_name' => 'Costa Resin S.L.',
            'tax_id' => 'B12345678',
            'address' => 'Calle Mayor 1',
            'postal_code' => '29001',
            'city' => 'Málaga',
            'province' => 'Málaga',
            'country' => 'es',
            'contact_email' => 'billing@example.com',
            'phone' => '+34951000000',
            'invoice_serie' => 'F1',
            'issuance_enabled' => true,
        ]);

        $this->assertSame('Costa Resin S.L.', Setting::get('fiscal_legal_name'));
        $this->assertSame('ES', $profile->get()['country']);
        $this->assertTrue((bool) Setting::get('fiscal_issuance_enabled'));
        $this->assertSame('F1', Setting::get('fiscal_invoice_serie'));
        $audit = AuditLog::where('event', 'platform.fiscal_profile_changed')->firstOrFail();
        $this->assertContains('legal_name', $audit->new_values['changed_fields']);
    }

    public function test_fiscal_settings_page_requires_admin_and_enabled_invoicing_module(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'local-password',
        ]);
        $administrator->assignRole('admin');

        $modules = app(\App\Services\PlatformModules::class);
        $modules->setEnabled('accounting', false);
        $modules->setEnabled('invoicing', false);
        $this->actingAs($administrator)
            ->get('/admin/fiscal-settings')
            ->assertForbidden();

        $modules->setEnabled('invoicing', true);

        $this->get('/admin/fiscal-settings')
            ->assertOk()
            ->assertSee('Datos de la empresa')
            ->assertSee('Habilitar emisión fiscal');
    }

    public function test_invalid_fiscal_profile_email_is_rejected_without_writing_settings(): void
    {
        try {
            app(FiscalProfile::class)->save([
                'legal_name' => 'Example Company',
                'contact_email' => 'not-an-email',
            ]);
            $this->fail('Invalid fiscal contact email should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('contact_email', $exception->errors());
        }

        $this->assertNull(Setting::where('key', 'fiscal_legal_name')->first());
    }
}
