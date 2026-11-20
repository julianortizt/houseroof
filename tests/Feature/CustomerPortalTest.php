<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Quote;
use App\Services\PlatformModules;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_portal_is_enabled_by_default_and_can_be_hidden_in_the_super_panel(): void
    {
        $modules = app(PlatformModules::class);
        $this->assertTrue($modules->enabled('customer_portal'));
        $this->withSession(['locale' => 'es'])->get('/cliente/acceso')->assertOk()->assertSee('Acceso de clientes');

        $modules->setEnabled('customer_portal', false);

        $this->get('/cliente/acceso')->assertNotFound();

        $modules->setEnabled('customer_portal', true);
        $this->get('/cliente/portal')->assertRedirect(route('customer.login'));
    }

    public function test_customer_registration_requires_email_verification_before_accessing_the_portal(): void
    {
        Notification::fake();
        app(PlatformModules::class)->setEnabled('customer_portal', true);

        $this->post(route('customer.register.post'), [
            'name' => 'Cliente de prueba',
            'email' => ' CUSTOMER@example.com ',
            'password' => 'A-secure-password-2026',
            'password_confirmation' => 'A-secure-password-2026',
        ])->assertRedirect(route('verification.notice'));

        $customer = Customer::query()->where('email', 'customer@example.com')->firstOrFail();
        Notification::assertSentTo($customer, VerifyEmail::class);
        $this->assertNull($customer->email_verified_at);

        $this->get('/cliente/portal')->assertRedirect(route('verification.notice'));

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $customer->id, 'hash' => sha1($customer->getEmailForVerification())],
        );

        $this->get($verificationUrl)->assertRedirect(route('customer.portal'));
        $this->assertNotNull($customer->fresh()->email_verified_at);
        $this->withSession(['locale' => 'es'])->get('/cliente/portal')->assertOk()->assertSee('Todavía no hay actividad asociada');
    }

    public function test_portal_only_displays_quotes_belonging_to_the_verified_email(): void
    {
        app(PlatformModules::class)->setEnabled('customer_portal', true);
        $customer = Customer::create([
            'name' => 'Cliente de prueba',
            'email' => 'customer@example.com',
            'password' => 'A-secure-password-2026',
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();
        $ownQuote = Quote::create([
            'name' => 'Cotización privada',
            'email' => $customer->email,
            'service_type' => 'resin',
            'total' => 125,
        ]);
        Quote::create([
            'name' => 'Cotización ajena',
            'email' => 'other@example.com',
            'service_type' => 'resin',
            'total' => 900,
        ]);

        $this->actingAs($customer, 'customer')
            ->get('/cliente/portal')
            ->assertOk()
            ->assertSee('#' . $ownQuote->id)
            ->assertDontSee('900,00 €');

        app(PlatformModules::class)->setEnabledModules([
            'quotes' => false,
            'work_orders' => false,
            'invoicing' => false,
            'accounting' => false,
        ]);

        $this->get('/cliente/portal')->assertOk()->assertDontSee('125,00 €');
    }

    public function test_verified_customer_can_approve_own_quote_without_creating_a_work_order(): void
    {
        app(PlatformModules::class)->setEnabled('customer_portal', true);
        $customer = Customer::create([
            'name' => 'Cliente de prueba',
            'email' => 'customer@example.com',
            'password' => 'A-secure-password-2026',
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();
        $quote = Quote::create([
            'name' => 'Cotización aceptable',
            'email' => $customer->email,
            'service_type' => 'resin',
            'status' => 'enviado',
            'total' => 125,
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.quotes.respond', $quote), ['decision' => 'approved'])
            ->assertRedirect(route('customer.portal'));

        $this->assertSame('aprobado', $quote->fresh()->status);
        $this->assertNotNull($quote->fresh()->approved_at);
        $this->assertDatabaseCount('mapscalculator_work_orders', 0);
    }

    public function test_customer_sees_deposit_and_balance_terms_before_accepting_a_quote(): void
    {
        app(PlatformModules::class)->setEnabled('customer_portal', true);
        $customer = Customer::create([
            'name' => 'Cliente de prueba',
            'email' => 'customer@example.com',
            'password' => 'A-secure-password-2026',
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();
        Quote::create([
            'name' => 'Cotización enviada',
            'email' => $customer->email,
            'service_type' => 'resin',
            'status' => 'enviado',
            'total' => 1000,
        ]);

        $this->actingAs($customer, 'customer')
            ->withSession(['locale' => 'es'])
            ->get('/cliente/portal')
            ->assertOk()
            ->assertSee('Anticipo inicial (30,00%)')
            ->assertSee('300,00 €')
            ->assertSee('El saldo se solicitará al finalizar el trabajo.')
            ->assertSee('Aceptar');
    }

    public function test_customer_cannot_respond_to_another_customers_quote(): void
    {
        app(PlatformModules::class)->setEnabled('customer_portal', true);
        $customer = Customer::create([
            'name' => 'Cliente de prueba',
            'email' => 'customer@example.com',
            'password' => 'A-secure-password-2026',
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();
        $foreignQuote = Quote::create([
            'name' => 'Cotización de otra persona',
            'email' => 'other@example.com',
            'service_type' => 'resin',
            'status' => 'enviado',
        ]);

        $this->actingAs($customer, 'customer')
            ->post(route('customer.quotes.respond', $foreignQuote), ['decision' => 'approved'])
            ->assertNotFound();

        $this->assertSame('enviado', $foreignQuote->fresh()->status);
    }

    public function test_customer_login_matches_existing_accounts_without_case_sensitivity(): void
    {
        app(PlatformModules::class)->setEnabled('customer_portal', true);
        $customer = Customer::create([
            'name' => 'Cliente existente',
            'email' => 'Customer@Example.com',
            'password' => 'A-secure-password-2026',
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        $this->post(route('customer.login.post'), [
            'email' => 'CUSTOMER@example.com',
            'password' => 'A-secure-password-2026',
        ])->assertRedirect(route('customer.portal'));

        $this->assertAuthenticatedAs($customer, 'customer');
    }
}
