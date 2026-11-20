<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\AuditLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Services\PlatformModules;
use App\Services\StripeCredentials;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StripePaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_stripe_credentials_are_encrypted_and_checkout_stays_disabled(): void
    {
        config([
            'platform.developer_email' => 'developer@example.com',
            'services.stripe.key' => null,
            'services.stripe.secret' => null,
            'services.stripe.webhook_secret' => null,
            'services.stripe.checkout_enabled' => false,
        ]);
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'local-password',
        ]);
        $this->actingAs($developer);

        app(StripeCredentials::class)->save([
            'publishable_key' => 'pk_live_public123',
            'secret_key' => 'sk_live_secret123',
            'webhook_secret' => 'whsec_hook123',
            'enable_product_checkout' => false,
        ]);

        $encryptedSecret = Setting::where('key', 'stripe_secret_key_encrypted')->value('value');
        $this->assertNotSame('sk_live_secret123', $encryptedSecret);
        $this->assertSame('sk_live_secret123', Crypt::decryptString($encryptedSecret));
        $this->assertSame('sk_live_secret123', app(StripeCredentials::class)->secretKey());
        $this->assertFalse(app(StripeCredentials::class)->checkoutEnabled());
        $audit = AuditLog::where('event', 'platform.stripe_configuration_changed')->firstOrFail();
        $this->assertContains('secret_key', $audit->new_values['updated_credentials']);
        $this->assertStringNotContainsString('sk_live_secret123', json_encode($audit->new_values));
    }

    public function test_stripe_settings_page_is_super_panel_only_and_does_not_render_saved_secrets(): void
    {
        config(['platform.developer_email' => 'developer@example.com']);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'local-password',
        ]);
        $administrator->assignRole('admin');
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'local-password',
        ]);
        Setting::set('stripe_secret_key_encrypted', Crypt::encryptString('sk_live_secret123'), 'encrypted', 'payments');

        $this->actingAs($administrator)
            ->get('/super-panel/stripe-settings')
            ->assertForbidden();

        $this->actingAs($developer)
            ->get('/super-panel/stripe-settings')
            ->assertOk()
            ->assertDontSee('sk_live_secret123')
            ->assertDontSee(Setting::get('stripe_secret_key_encrypted'));
    }

    public function test_product_checkout_cannot_be_enabled_without_platform_modules(): void
    {
        config([
            'platform.developer_email' => 'developer@example.com',
            'services.stripe.key' => null,
            'services.stripe.secret' => null,
            'services.stripe.webhook_secret' => null,
        ]);
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'local-password',
        ]);
        $this->actingAs($developer);

        try {
            app(StripeCredentials::class)->save([
                'publishable_key' => 'pk_live_public123',
                'secret_key' => 'sk_live_secret123',
                'webhook_secret' => 'whsec_hook123',
                'enable_product_checkout' => true,
                'product_checkout_fiscal_review_confirmed' => true,
            ]);
            $this->fail('Checkout should require the products and payments modules.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('enable_product_checkout', $exception->errors());
        }

        $this->assertNull(Setting::where('key', 'stripe_secret_key_encrypted')->first());
        $this->assertFalse(app(StripeCredentials::class)->checkoutEnabled());
    }

    public function test_product_checkout_can_be_enabled_after_all_credentials_and_modules_are_ready(): void
    {
        config([
            'platform.developer_email' => 'developer@example.com',
            'services.stripe.key' => null,
            'services.stripe.secret' => null,
            'services.stripe.webhook_secret' => null,
        ]);
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'local-password',
        ]);
        $this->actingAs($developer);
        $modules = app(PlatformModules::class);
        $modules->setEnabled('products', true);
        $modules->setEnabled('payments', true);

        app(StripeCredentials::class)->save([
            'publishable_key' => 'pk_live_public123',
            'secret_key' => 'sk_live_secret123',
            'webhook_secret' => 'whsec_hook123',
            'enable_product_checkout' => true,
            'product_checkout_fiscal_review_confirmed' => true,
        ]);

        $this->assertTrue(app(StripeCredentials::class)->checkoutEnabled());
    }

    public function test_product_checkout_requires_explicit_fiscal_review(): void
    {
        config([
            'platform.developer_email' => 'developer@example.com',
            'services.stripe.key' => null,
            'services.stripe.secret' => null,
            'services.stripe.webhook_secret' => null,
        ]);
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'local-password',
        ]);
        $this->actingAs($developer);
        $modules = app(PlatformModules::class);
        $modules->setEnabled('products', true);
        $modules->setEnabled('payments', true);

        try {
            app(StripeCredentials::class)->save([
                'publishable_key' => 'pk_live_public123',
                'secret_key' => 'sk_live_secret123',
                'webhook_secret' => 'whsec_hook123',
                'enable_product_checkout' => true,
                'product_checkout_fiscal_review_confirmed' => false,
            ]);
            $this->fail('Product checkout must require fiscal review confirmation.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('product_checkout_fiscal_review_confirmed', $exception->errors());
        }

        $this->assertFalse(app(StripeCredentials::class)->checkoutEnabled());
    }

    public function test_service_payments_require_invoice_module_and_fiscal_review_confirmation(): void
    {
        config([
            'platform.developer_email' => 'developer@example.com',
            'services.stripe.key' => null,
            'services.stripe.secret' => null,
            'services.stripe.webhook_secret' => null,
        ]);
        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'local-password',
        ]);
        $this->actingAs($developer);
        app(PlatformModules::class)->setEnabled('accounting', false);
        app(PlatformModules::class)->setEnabled('invoicing', false);
        app(PlatformModules::class)->setEnabled('payments', true);
        $settings = [
            'publishable_key' => 'pk_live_public123',
            'secret_key' => 'sk_live_secret123',
            'webhook_secret' => 'whsec_hook123',
            'enable_product_checkout' => false,
            'enable_service_payments' => true,
        ];

        try {
            app(StripeCredentials::class)->save($settings + ['service_payment_fiscal_review_confirmed' => true]);
            $this->fail('Service payments must require the invoicing module.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('enable_service_payments', $exception->errors());
        }

        app(PlatformModules::class)->setEnabled('invoicing', true);

        try {
            app(StripeCredentials::class)->save($settings + ['service_payment_fiscal_review_confirmed' => false]);
            $this->fail('Service payments must require explicit fiscal review confirmation.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('service_payment_fiscal_review_confirmed', $exception->errors());
        }

        app(StripeCredentials::class)->save($settings + ['service_payment_fiscal_review_confirmed' => true]);
        $this->assertTrue(app(StripeCredentials::class)->servicePaymentsEnabled());
    }

    public function test_operational_status_reports_when_stripe_and_fiscal_layers_are_ready_for_live_business(): void
    {
        config([
            'platform.developer_email' => 'developer@example.com',
            'services.stripe.key' => 'pk_live_public123',
            'services.stripe.secret' => 'sk_live_secret123',
            'services.stripe.webhook_secret' => 'whsec_hook123',
        ]);

        $developer = User::create([
            'name' => 'Developer',
            'email' => 'developer@example.com',
            'password' => 'local-password',
        ]);
        $this->actingAs($developer);

        app(PlatformModules::class)->setEnabled('products', true);
        app(PlatformModules::class)->setEnabled('payments', true);
        app(PlatformModules::class)->setEnabled('invoicing', true);
        app(PlatformModules::class)->setEnabled('accounting', true);

        app(StripeCredentials::class)->save([
            'publishable_key' => 'pk_live_public123',
            'secret_key' => 'sk_live_secret123',
            'webhook_secret' => 'whsec_hook123',
            'enable_product_checkout' => true,
            'product_checkout_fiscal_review_confirmed' => true,
            'enable_service_payments' => true,
            'service_payment_fiscal_review_confirmed' => true,
        ]);

        app(\App\Services\FiscalProfile::class)->save([
            'legal_name' => 'Resin Solutions SL',
            'tax_id' => 'B12345678',
            'address' => 'Calle Mayor 1',
            'postal_code' => '28001',
            'city' => 'Madrid',
            'province' => 'Madrid',
            'country' => 'ES',
            'contact_email' => 'billing@example.com',
            'phone' => '+34 600 000 001',
            'invoice_serie' => 'F1',
            'issuance_enabled' => true,
        ]);

        $stripeStatus = app(StripeCredentials::class)->operationalStatus();
        $fiscalStatus = app(\App\Services\FiscalProfile::class)->operationalStatus();

        $this->assertTrue($stripeStatus['ready']);
        $this->assertTrue($fiscalStatus['ready']);
        $this->assertContains('product_checkout', $stripeStatus['enabled_flows']);
        $this->assertContains('service_payments', $stripeStatus['enabled_flows']);
    }

    public function test_repeated_paid_webhooks_do_not_decrement_product_stock_more_than_once(): void
    {
        config(['services.stripe.secret' => 'sk_test_local_only']);
        $category = Category::create(['slug' => 'webhook-category']);
        $product = Product::create([
            'category_id' => $category->id,
            'slug' => 'webhook-product',
            'price' => 10,
            'stock' => 5,
        ]);
        $order = Order::create([
            'order_number' => 'IE-2026-00999',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'stripe_checkout_session_id' => 'cs_test_once',
            'subtotal' => 20,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 20,
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Customer',
            'shipping_address_line_1' => 'Calle Mayor 1',
            'shipping_city' => 'Málaga',
            'shipping_postal_code' => '29001',
            'shipping_country' => 'ES',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Webhook product',
            'quantity' => 2,
            'unit_price' => 10,
            'total_price' => 20,
        ]);
        $payload = [
            'data' => [
                'object' => [
                    'id' => 'cs_test_once',
                    'payment_status' => 'paid',
                    'payment_intent' => 'pi_test_once',
                    'currency' => 'eur',
                    'amount_total' => 2300,
                    'total_details' => ['amount_tax' => 300],
                    'metadata' => ['order_id' => $order->id],
                ],
            ],
        ];

        $service = app(StripeService::class);
        $service->handleCheckoutCompleted($payload);
        $service->handleCheckoutCompleted($payload);

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('processing', $order->fresh()->status);
        $this->assertSame('23.00', $order->fresh()->total);
        $this->assertSame('3.00', $order->fresh()->tax);
    }

    public function test_paid_webhook_flags_stock_conflict_without_overselling(): void
    {
        config(['services.stripe.secret' => 'sk_test_local_only']);
        $category = Category::create(['slug' => 'limited-stock-category']);
        $product = Product::create([
            'category_id' => $category->id,
            'slug' => 'limited-stock-product',
            'price' => 10,
            'stock' => 1,
        ]);
        $order = Order::create([
            'order_number' => 'IE-2026-00998',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'stripe_checkout_session_id' => 'cs_test_stock_conflict',
            'subtotal' => 20,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 20,
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Customer',
            'shipping_address_line_1' => 'Calle Mayor 1',
            'shipping_city' => 'Málaga',
            'shipping_postal_code' => '29001',
            'shipping_country' => 'ES',
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Limited stock product',
            'quantity' => 2,
            'unit_price' => 10,
            'total_price' => 20,
        ]);

        app(StripeService::class)->handleCheckoutCompleted([
            'data' => [
                'object' => [
                    'id' => 'cs_test_stock_conflict',
                    'payment_status' => 'paid',
                    'currency' => 'eur',
                    'amount_total' => 2000,
                    'metadata' => ['order_id' => $order->id],
                ],
            ],
        ]);

        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('stock_review', $order->fresh()->status);
    }

    public function test_webhook_metadata_cannot_override_a_mismatched_stored_checkout_session(): void
    {
        config(['services.stripe.secret' => 'sk_test_local_only']);
        $order = Order::create([
            'order_number' => 'IE-2026-01000',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'stripe_checkout_session_id' => 'cs_expected',
            'subtotal' => 20,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 20,
            'customer_email' => 'customer@example.com',
            'customer_name' => 'Customer',
            'shipping_address_line_1' => 'Calle Mayor 1',
            'shipping_city' => 'Málaga',
            'shipping_postal_code' => '29001',
            'shipping_country' => 'ES',
        ]);

        $result = app(StripeService::class)->handleCheckoutCompleted([
            'data' => [
                'object' => [
                    'id' => 'cs_wrong',
                    'payment_status' => 'paid',
                    'metadata' => ['order_id' => $order->id],
                ],
            ],
        ]);

        $this->assertNull($result);
        $this->assertSame('unpaid', $order->fresh()->payment_status);
    }
}
