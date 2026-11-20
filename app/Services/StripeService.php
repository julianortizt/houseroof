<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;

class StripeService
{
    public function __construct()
    {
        $secretKey = app(StripeCredentials::class)->secretKey();
        if (filled($secretKey)) {
            Stripe::setApiKey($secretKey);
        }
    }

    /**
     * Create a Stripe Checkout Session for an order.
     *
     * @throws ApiErrorException
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $cancelUrl): StripeSession
    {
        abort_unless(app(PlatformModules::class)->enabled('payments'), 404, 'Online payments are disabled.');
        abort_unless(app(PlatformModules::class)->enabled('products'), 404, 'Product checkout is disabled.');
        $credentials = app(StripeCredentials::class);
        abort_unless($credentials->checkoutEnabled(), 404, 'Stripe checkout is not explicitly enabled.');
        abort_unless(filled($credentials->secretKey()), 503, 'Stripe secret key is not configured.');

        $lineItems = [];

        foreach ($order->items as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) ($item->unit_price * 100),
                    'product_data' => [
                        'name' => $item->product_name . ($item->variant_name ? ' - ' . $item->variant_name : ''),
                    ],
                ],
                'quantity' => $item->quantity,
            ];
        }

        // Add shipping as a line item if applicable
        if ($order->shipping_cost > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) ($order->shipping_cost * 100),
                    'product_data' => [
                        'name' => 'Envío',
                    ],
                ],
                'quantity' => 1,
            ];
        }

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'customer_email' => $order->customer_email,
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
            'billing_address_collection' => 'auto',
            'shipping_address_collection' => [
                'allowed_countries' => $this->getEuCountries(),
            ],
        ];

        // Apply IVA (21%) as automatic tax if configured
        $sessionParams['automatic_tax'] = ['enabled' => true];

        return StripeSession::create($sessionParams);
    }

    /**
     * Retrieve a checkout session by ID.
     */
    public function getSession(string $sessionId): StripeSession
    {
        return StripeSession::retrieve([
            'id' => $sessionId,
            'expand' => ['payment_intent'],
        ]);
    }

    /**
     * Handle Stripe webhook event for checkout.session.completed
     */
    public function handleCheckoutCompleted(array $payload): ?Order
    {
        $sessionId = $payload['data']['object']['id'];
        $orderId = $payload['data']['object']['metadata']['order_id'] ?? null;

        return \Illuminate\Support\Facades\DB::transaction(function () use ($sessionId, $orderId, $payload): ?Order {
            $order = Order::query()
                ->where(function ($query) use ($sessionId, $orderId): void {
                    $query->where('stripe_checkout_session_id', $sessionId);
                    if ($orderId) {
                        $query->orWhere(function ($fallback) use ($orderId): void {
                            $fallback->whereKey($orderId)
                                ->whereNull('stripe_checkout_session_id');
                        });
                    }
                })
                ->lockForUpdate()
                ->first();

            if (! $order || ($payload['data']['object']['payment_status'] ?? null) !== 'paid') {
                return $order;
            }

            if ($order->payment_status !== 'paid') {
                $checkoutSession = $payload['data']['object'];
                if (($checkoutSession['currency'] ?? 'eur') !== 'eur') {
                    return $order;
                }

                $updates = [
                    'payment_status' => 'paid',
                    'status' => 'processing',
                    'stripe_payment_intent_id' => $checkoutSession['payment_intent'] ?? null,
                ];

                if (isset($checkoutSession['amount_total']) && is_numeric($checkoutSession['amount_total'])) {
                    $updates['total'] = ((int) $checkoutSession['amount_total']) / 100;
                }
                if (isset($checkoutSession['total_details']['amount_tax']) && is_numeric($checkoutSession['total_details']['amount_tax'])) {
                    $updates['tax'] = ((int) $checkoutSession['total_details']['amount_tax']) / 100;
                }

                $stockChanges = [];
                $hasInsufficientStock = false;
                foreach ($order->items()->lockForUpdate()->get() as $item) {
                    if ($item->product_variant_id) {
                        $stockItem = ProductVariant::query()->lockForUpdate()->find($item->product_variant_id);
                    } else {
                        $stockItem = Product::query()->lockForUpdate()->find($item->product_id);
                    }

                    if (! $stockItem || $stockItem->stock < $item->quantity) {
                        $hasInsufficientStock = true;
                        break;
                    }

                    $stockChanges[] = [$stockItem, $item->quantity];
                }

                if ($hasInsufficientStock) {
                    $updates['status'] = 'stock_review';
                    \Illuminate\Support\Facades\Log::critical('Paid product order requires manual stock review.', [
                        'order_id' => $order->id,
                    ]);
                } else {
                    foreach ($stockChanges as [$stockItem, $quantity]) {
                        $stockItem->decrement('stock', $quantity);
                    }
                }

                $order->update($updates);
            }

            return $order->fresh();
        });
    }

    protected function getEuCountries(): array
    {
        return [
            'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR',
            'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL',
            'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE',
        ];
    }
}
