<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\OrderStockResolutionService;
use App\Services\StripeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderStockResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_paid_stock_review_order_can_be_fulfilled_after_restock(): void
    {
        $product = $this->product(stock: 5);
        $order = $this->order();
        $this->addItem($order, $product, 2);
        $this->addItem($order, $product, 1);

        $resolved = app(OrderStockResolutionService::class)->fulfillAfterRestock($order);

        $this->assertSame('processing', $resolved->status);
        $this->assertSame('paid', $resolved->payment_status);
        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_insufficient_stock_does_not_partially_decrement_or_resolve_order(): void
    {
        $product = $this->product(stock: 2);
        $order = $this->order();
        $this->addItem($order, $product, 3);

        try {
            app(OrderStockResolutionService::class)->fulfillAfterRestock($order);
            $this->fail('The order must remain in review when stock is insufficient.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('order', $exception->errors());
        }

        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame('stock_review', $order->fresh()->status);
    }

    public function test_stock_review_refund_marks_the_order_cancelled_only_after_stripe_confirms(): void
    {
        config(['services.stripe.secret' => 'sk_test_order_refund']);
        $order = $this->order();
        $order->update(['stripe_payment_intent_id' => 'pi_stock_review']);
        $this->assertSame('stock_review', $order->fresh()->status);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('pi_stock_review', $order->fresh()->stripe_payment_intent_id);
        $this->mock(StripeGateway::class)
            ->shouldReceive('createRefund')
            ->once()
            ->with(
                'sk_test_order_refund',
                'pi_stock_review',
                3000,
                \Mockery::type('string'),
                ['order_id' => (string) $order->id, 'resolution' => 'stock_review'],
            )
            ->andReturn(['id' => 're_stock_review', 'status' => 'succeeded']);

        $resolved = app(OrderStockResolutionService::class)->refundStockReview($order);
        $this->assertSame('cancelled', $resolved->status);
        $this->assertSame('refunded', $resolved->payment_status);
        $this->assertSame('succeeded', $resolved->stock_review_refund_status);

        app(OrderStockResolutionService::class)->refundStockReview($order);
        $this->assertSame('refunded', $order->fresh()->payment_status);
    }

    public function test_refund_webhook_must_match_the_recorded_payment_and_full_amount(): void
    {
        config(['services.stripe.secret' => 'sk_test_order_refund']);
        $order = $this->order();
        $order->update(['stripe_payment_intent_id' => 'pi_stock_review']);
        $this->mock(StripeGateway::class)
            ->shouldReceive('createRefund')
            ->once()
            ->andReturn(['id' => 're_stock_review', 'status' => 'pending']);

        $service = app(OrderStockResolutionService::class);
        $service->refundStockReview($order);
        $event = [
            'id' => 're_stock_review',
            'payment_intent' => 'pi_stock_review',
            'amount' => 3000,
            'status' => 'succeeded',
            'metadata' => ['order_id' => (string) $order->id, 'resolution' => 'stock_review'],
        ];

        $service->handleRefundUpdated(array_merge($event, ['amount' => 1000]));
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('stock_review', $order->fresh()->status);

        $service->handleRefundUpdated($event);
        $this->assertSame('refunded', $order->fresh()->payment_status);
        $this->assertSame('cancelled', $order->fresh()->status);
    }

    private function product(int $stock): Product
    {
        $category = Category::create(['slug' => 'stock-review-category']);

        return Product::create([
            'category_id' => $category->id,
            'slug' => 'stock-review-product',
            'name' => ['en' => 'Review product', 'es' => 'Producto en revisión'],
            'price' => 10,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }

    private function order(): Order
    {
        return Order::create([
            'order_number' => 'IE-' . now()->year . '-' . random_int(10000, 99999),
            'status' => 'stock_review',
            'payment_status' => 'paid',
            'subtotal' => 30,
            'shipping_cost' => 0,
            'tax' => 0,
            'total' => 30,
            'customer_email' => 'customer@example.test',
            'customer_name' => 'Customer',
            'shipping_address_line_1' => 'Address',
            'shipping_city' => 'Malaga',
            'shipping_postal_code' => '29001',
            'shipping_country' => 'ES',
        ]);
    }

    private function addItem(Order $order, Product $product, int $quantity): void
    {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Review product',
            'quantity' => $quantity,
            'unit_price' => 10,
            'total_price' => $quantity * 10,
        ]);
    }
}
