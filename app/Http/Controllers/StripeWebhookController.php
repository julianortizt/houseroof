<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Services\StripeService;
use App\Services\QuotePaymentService;
use App\Services\OrderStockResolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected QuotePaymentService $quotePayments,
        protected OrderStockResolutionService $orderResolution,
    ) {}

    public function handle(Request $request)
    {
        $webhookSecret = app(\App\Services\StripeCredentials::class)->webhookSecret();
        abort_unless(filled($webhookSecret), 503, 'Stripe webhook credentials are not configured.');

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                $webhookSecret
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $object = $event->data->object->toArray();

        if (in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            if (filled($object['metadata']['quote_payment_id'] ?? null)) {
                $this->quotePayments->handleCheckoutCompleted($object);
            } else {
                $order = $this->stripeService->handleCheckoutCompleted(['data' => ['object' => $object]]);

                if ($order) {
                    Mail::to($order->customer_email)->send(new OrderConfirmation($order));
                }
            }
        } elseif ($event->type === 'checkout.session.expired') {
            $this->quotePayments->handleCheckoutClosed($object, 'expired');
        } elseif ($event->type === 'checkout.session.async_payment_failed') {
            $this->quotePayments->handleCheckoutClosed($object, 'failed');
        } elseif ($event->type === 'refund.updated') {
            $this->quotePayments->handleRefundUpdated($object);
            $this->orderResolution->handleRefundUpdated($object);
        }

        return response()->json(['status' => 'success']);
    }
}
