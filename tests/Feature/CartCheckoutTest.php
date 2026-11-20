<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use App\Services\PlatformModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_is_available_but_checkout_explains_that_online_payments_are_disabled(): void
    {
        $this->get('/carrito?lang=es')
            ->assertOk()
            ->assertSee('Carrito de compras');

        $this->get('/checkout?lang=es')
            ->assertOk()
            ->assertSee('La compra online todavía no está disponible')
            ->assertDontSee('Datos del cliente');
    }

    public function test_cart_displays_current_prices_and_checkout_uses_current_stock(): void
    {
        $product = $this->product(['price' => 10, 'stock' => 4]);
        $cart = app(CartService::class);
        $cart->addItem($product->id, 2);

        $product->update(['price' => 12]);
        $this->assertSame(12.0, (float) $cart->getItems()[$product->id . '_default']['price']);

        $items = \Illuminate\Support\Facades\DB::transaction(fn () => $cart->getPurchasableItems());
        $this->assertSame(12.0, $items[0]['price']);
        $this->assertSame(2, $items[0]['quantity']);

        $product->update(['stock' => 1]);
        try {
            \Illuminate\Support\Facades\DB::transaction(fn () => $cart->getPurchasableItems());
            $this->fail('Checkout must reject quantities above current stock.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('cart', $exception->errors());
        }
    }

    public function test_add_to_cart_rejects_inactive_products_and_excess_quantity(): void
    {
        $product = $this->product(['is_active' => false]);

        try {
            app(CartService::class)->addItem($product->id);
            $this->fail('Inactive products must not be added to the cart.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('cart', $exception->errors());
        }

        $product->update(['is_active' => true]);
        try {
            app(CartService::class)->addItem($product->id, 100);
            $this->fail('Cart quantities must be capped.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quantity', $exception->errors());
        }
    }

    public function test_cart_summary_returns_consistent_totals_with_shipping_and_tax(): void
    {
        $product = $this->product(['price' => 25, 'stock' => 10]);
        $cart = app(CartService::class);
        $cart->addItem($product->id, 2);

        $summary = $cart->getSummary();

        $this->assertSame(50.0, $summary['subtotal']);
        $this->assertSame(15.0, $summary['shipping']);
        $this->assertSame(13.65, round($summary['tax'], 2));
        $this->assertSame(78.65, round($summary['total'], 2));
        $this->assertFalse($summary['is_empty']);
    }

    public function test_whatsapp_button_builds_a_clean_message_url_and_reports_ready_state(): void
    {
        $component = new \App\Livewire\WhatsAppButton();
        $component->phone = '+34 654 123 456';

        $this->assertSame('34654123456', $component->cleanPhone('+34 654 123 456'));
        $this->assertStringContainsString('wa.me/34654123456', $component->getWhatsAppUrl('Hola equipo'));
        $this->assertStringContainsString('Hola%20equipo', $component->getWhatsAppUrl('Hola equipo'));
    }

    public function test_ai_widget_reports_when_the_assistant_is_ready_or_unconfigured(): void
    {
        $component = new \App\Livewire\AiChatWidget();
        $component->enabled = true;
        $component->apiKey = 'test-key';
        $this->assertSame('ready', $component->getStatus());

        $component->enabled = true;
        $component->apiKey = '';
        $this->assertSame('demo', $component->getStatus());
    }

    public function test_checkout_success_route_requires_a_valid_payment_configuration_and_session(): void
    {
        $this->get('/checkout/exito')
            ->assertNotFound();
    }

    private function product(array $attributes = []): Product
    {
        $category = Category::create([
            'slug' => 'resin-products',
            'name' => ['en' => 'Resin products', 'es' => 'Productos de resina'],
            'is_active' => true,
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'slug' => 'resin-coating',
            'name' => ['en' => 'Resin coating', 'es' => 'Revestimiento de resina'],
            'price' => 20,
            'stock' => 5,
            'is_active' => true,
            'is_coming_soon' => false,
        ], $attributes));
    }
}
