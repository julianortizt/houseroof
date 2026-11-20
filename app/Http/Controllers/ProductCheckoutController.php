<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\StripeCredentials;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductCheckoutController extends Controller
{
    public function success(
        Request $request,
        StripeCredentials $credentials,
        StripeService $stripe,
    ): View {
        abort_unless($credentials->productCheckoutReady(), 404);

        $sessionId = $request->query('session_id');
        abort_unless(is_string($sessionId) && str_starts_with($sessionId, 'cs_'), 404);

        $session = $stripe->getSession($sessionId);
        $order = Order::query()
            ->where('stripe_checkout_session_id', $session->id)
            ->firstOrFail();

        return view('pages.shop.order-success', [
            'paymentReceived' => $session->payment_status === 'paid' && $order->payment_status === 'paid',
        ]);
    }
}
