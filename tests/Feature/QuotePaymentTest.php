<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuotePayment;
use App\Models\QuotePaymentRefund;
use App\Models\Setting;
use App\Models\User;
use App\Models\MapsCalculator\Client;
use App\Models\MapsCalculator\Project;
use App\Models\MapsCalculator\WorkOrder;
use App\Services\PlatformModules;
use App\Services\QuotePaymentService;
use App\Services\StripeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuotePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_pay_quote_deposit_and_duplicate_webhooks_are_idempotent(): void
    {
        $this->enableServicePayments();
        app(PlatformModules::class)->setEnabled('customer_portal', true);
        $customer = $this->verifiedCustomer();
        $quote = $this->approvedQuote($customer->email);

        $gateway = Mockery::mock(StripeGateway::class);
        $gateway->shouldReceive('createCheckoutSession')
            ->once()
            ->withArgs(fn (string $secret, array $parameters, string $key): bool => $secret === 'sk_live_quote_test'
                && $parameters['line_items'][0]['price_data']['unit_amount'] === 30000
                && $parameters['metadata']['quote_id'] === (string) $quote->id
                && filled($key))
            ->andReturn(['id' => 'cs_quote_deposit', 'url' => 'https://checkout.stripe.test/session']);
        $this->app->instance(StripeGateway::class, $gateway);

        $this->actingAs($customer, 'customer')
            ->withSession(['locale' => 'es'])
            ->get('/cliente/portal')
            ->assertOk()
            ->assertSee('Pagar anticipo');

        $this->post(route('customer.quotes.payment', [$quote, 'deposit']))
            ->assertRedirect('https://checkout.stripe.test/session');

        $payment = QuotePayment::firstOrFail();
        $this->assertSame(30000, $payment->amount_cents);
        $this->assertSame('pending', $payment->status);

        $event = [
            'id' => 'cs_quote_deposit',
            'amount_total' => 30000,
            'currency' => 'eur',
            'payment_status' => 'paid',
            'payment_intent' => 'pi_quote_deposit',
            'metadata' => [
                'quote_payment_id' => (string) $payment->id,
                'quote_id' => (string) $quote->id,
                'payment_type' => 'deposit',
            ],
        ];
        $service = app(QuotePaymentService::class);
        $service->handleCheckoutCompleted(array_merge($event, ['amount_total' => 100]));
        $this->assertSame('pending', $payment->fresh()->status);
        $service->handleCheckoutCompleted($event);
        $service->handleCheckoutCompleted($event);

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('pi_quote_deposit', $payment->fresh()->stripe_payment_intent_id);
        $this->assertSame(1, AuditLog::where('event', 'quote_payment.received')->count());
    }

    public function test_customer_cannot_create_a_payment_for_another_customers_quote(): void
    {
        $this->enableServicePayments();
        $customer = $this->verifiedCustomer();
        $quote = $this->approvedQuote('other@example.com');

        try {
            app(QuotePaymentService::class)->createCheckout($quote, $customer, 'deposit');
            $this->fail('A customer must not be able to pay another customer quote.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        $this->assertDatabaseCount('quote_payments', 0);
    }

    public function test_final_balance_is_available_only_after_deposit_and_work_order_completion(): void
    {
        $this->enableServicePayments();
        $customer = $this->verifiedCustomer();
        $quote = $this->approvedQuote($customer->email);
        $deposit = $quote->payments()->create([
            'customer_id' => $customer->id,
            'payment_type' => 'deposit',
            'amount_cents' => 30000,
            'currency' => 'eur',
            'status' => 'paid',
            'checkout_idempotency_key' => 'deposit-already-paid',
            'stripe_payment_intent_id' => 'pi_already_paid',
            'paid_at' => now(),
        ]);

        $summary = app(QuotePaymentService::class)->paymentSummary($quote->fresh());
        $this->assertFalse($summary['can_pay_balance']);
        $this->assertSame(70000, $summary['balance_due_cents']);

        $client = Client::create(['name' => 'Service client', 'email' => $customer->email]);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Completed service',
            'type' => 'interior',
            'status' => 'finalizada',
        ]);
        WorkOrder::create([
            'project_id' => $project->id,
            'quote_id' => $quote->id,
            'status' => 'finalizada',
        ]);

        $summary = app(QuotePaymentService::class)->paymentSummary($quote->fresh());
        $this->assertTrue($summary['can_pay_balance']);
        $this->assertSame(30000, $deposit->fresh()->amount_cents);
    }

    public function test_administrator_can_issue_a_partial_refund_without_exceeding_remaining_payment(): void
    {
        $this->enableServicePayments();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Payment Administrator',
            'email' => 'payment-admin@example.com',
            'password' => 'local-password',
        ]);
        $administrator->assignRole('admin');
        $this->actingAs($administrator);
        $quote = $this->approvedQuote('customer@example.com');
        $payment = $quote->payments()->create([
            'payment_type' => 'deposit',
            'amount_cents' => 30000,
            'currency' => 'eur',
            'status' => 'paid',
            'checkout_idempotency_key' => 'refundable-payment',
            'stripe_payment_intent_id' => 'pi_refundable',
            'paid_at' => now(),
        ]);

        $gateway = Mockery::mock(StripeGateway::class);
        $gateway->shouldReceive('createRefund')
            ->once()
            ->withArgs(fn (string $secret, string $paymentIntent, int $amount, string $idempotencyKey, array $metadata): bool => $secret === 'sk_live_quote_test'
                && $paymentIntent === 'pi_refundable'
                && $amount === 10000
                && filled($idempotencyKey)
                && filled($metadata['quote_payment_refund_id'] ?? null))
            ->andReturn(['id' => 're_partial', 'status' => 'succeeded']);
        $this->app->instance(StripeGateway::class, $gateway);

        $refund = app(QuotePaymentService::class)->refund($payment, 10000);
        $this->assertSame('succeeded', $refund->status);
        $this->assertSame(20000, $payment->fresh()->refundable_amount_cents);
        $this->assertSame(1, AuditLog::where('event', 'quote_payment.refund_requested')->count());

        try {
            app(QuotePaymentService::class)->refund($payment, 25000);
            $this->fail('A refund cannot exceed the remaining refundable amount.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('amount', $exception->errors());
        }
    }

    public function test_retrying_a_refund_with_a_stripe_id_retrieves_it_instead_of_creating_another(): void
    {
        $this->enableServicePayments();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Payment Administrator',
            'email' => 'payment-admin@example.com',
            'password' => 'local-password',
        ]);
        $administrator->assignRole('admin');
        $this->actingAs($administrator);
        $quote = $this->approvedQuote('customer@example.com');
        $payment = $quote->payments()->create([
            'payment_type' => 'deposit',
            'amount_cents' => 30000,
            'currency' => 'eur',
            'status' => 'paid',
            'checkout_idempotency_key' => 'refund-retry-payment',
            'stripe_payment_intent_id' => 'pi_refund_retry',
            'paid_at' => now(),
        ]);
        $refund = $payment->refunds()->create([
            'amount_cents' => 10000,
            'status' => 'pending',
            'stripe_refund_id' => 're_pending',
            'idempotency_key' => 'refund-retry-key',
        ]);

        $gateway = Mockery::mock(StripeGateway::class);
        $gateway->shouldReceive('retrieveRefund')
            ->once()
            ->with('sk_live_quote_test', 're_pending')
            ->andReturn(['id' => 're_pending', 'status' => 'succeeded']);
        $gateway->shouldNotReceive('createRefund');
        $this->app->instance(StripeGateway::class, $gateway);

        $updated = app(QuotePaymentService::class)->retryRefund($refund);

        $this->assertSame('succeeded', $updated->status);
    }

    public function test_refund_without_stripe_id_cannot_be_retried_after_idempotency_window(): void
    {
        $this->enableServicePayments();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Payment Administrator',
            'email' => 'payment-admin@example.com',
            'password' => 'local-password',
        ]);
        $administrator->assignRole('admin');
        $this->actingAs($administrator);
        $quote = $this->approvedQuote('customer@example.com');
        $payment = $quote->payments()->create([
            'payment_type' => 'deposit',
            'amount_cents' => 30000,
            'currency' => 'eur',
            'status' => 'paid',
            'checkout_idempotency_key' => 'stale-refund-payment',
            'stripe_payment_intent_id' => 'pi_stale_refund',
            'paid_at' => now(),
        ]);
        $refund = $payment->refunds()->create([
            'amount_cents' => 10000,
            'status' => 'pending',
            'idempotency_key' => 'stale-refund-key',
        ]);
        $refund->forceFill(['created_at' => now()->subHours(24)])->saveQuietly();

        $gateway = Mockery::mock(StripeGateway::class);
        $gateway->shouldNotReceive('createRefund');
        $gateway->shouldNotReceive('retrieveRefund');
        $this->app->instance(StripeGateway::class, $gateway);

        try {
            app(QuotePaymentService::class)->retryRefund($refund->fresh());
            $this->fail('A stale refund without a Stripe ID must not be resubmitted.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $this->assertSame('pending', $refund->fresh()->status);
    }

    public function test_quote_amount_and_deposit_cannot_change_after_checkout_is_started(): void
    {
        $customer = $this->verifiedCustomer();
        $quote = $this->approvedQuote($customer->email);
        $quote->payments()->create([
            'customer_id' => $customer->id,
            'payment_type' => 'deposit',
            'amount_cents' => 30000,
            'currency' => 'eur',
            'status' => 'pending',
            'checkout_idempotency_key' => 'pending-checkout',
        ]);

        try {
            $quote->update(['total' => 1200]);
            $this->fail('A pending checkout must lock the quote total.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('total', $exception->errors());
        }

        try {
            $quote->update(['deposit_percent' => 40]);
            $this->fail('A pending checkout must lock the deposit percentage.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('deposit_percent', $exception->errors());
        }
    }

    public function test_administrator_can_view_the_immutable_service_payment_ledger(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Payment Administrator',
            'email' => 'payment-admin@example.com',
            'password' => 'local-password',
        ]);
        $administrator->assignRole('admin');
        $quote = $this->approvedQuote('customer@example.com');
        $quote->payments()->create([
            'payment_type' => 'deposit',
            'amount_cents' => 30000,
            'currency' => 'eur',
            'status' => 'paid',
            'checkout_idempotency_key' => 'ledger-payment',
            'stripe_payment_intent_id' => 'pi_ledger',
            'paid_at' => now(),
        ]);

        $this->actingAs($administrator)
            ->get('/admin/quote-payments')
            ->assertOk()
            ->assertSee($quote->formatted_number)
            ->assertSee('Anticipo')
            ->assertSee('300,00 €');
    }

    private function enableServicePayments(): void
    {
        config([
            'services.stripe.key' => null,
            'services.stripe.secret' => null,
            'services.stripe.webhook_secret' => null,
        ]);
        $modules = app(PlatformModules::class);
        $modules->setEnabled('invoicing', true);
        $modules->setEnabled('payments', true);
        Setting::set('stripe_secret_key_encrypted', Crypt::encryptString('sk_live_quote_test'), 'encrypted', 'payments');
        Setting::set('stripe_service_payments_enabled', '1', 'boolean', 'payments');
        Setting::set('stripe_service_payments_fiscal_review_confirmed', '1', 'boolean', 'payments');
    }

    private function verifiedCustomer(): Customer
    {
        $customer = Customer::create([
            'name' => 'Service customer',
            'email' => 'customer@example.com',
            'password' => 'A-secure-password-2026',
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();

        return $customer;
    }

    private function approvedQuote(string $email): Quote
    {
        return Quote::create([
            'name' => 'Service customer',
            'email' => $email,
            'service_type' => 'resin',
            'status' => 'aprobado',
            'approved_at' => now(),
            'total' => 1000,
            'deposit_percent' => 30,
        ]);
    }
}
