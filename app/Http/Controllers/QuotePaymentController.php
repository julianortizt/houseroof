<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Services\QuotePaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class QuotePaymentController extends Controller
{
    public function checkout(Quote $quote, string $paymentType, QuotePaymentService $payments): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();
        abort_unless($customer !== null, 401);

        $checkout = $payments->createCheckout($quote, $customer, $paymentType);

        return redirect()->away($checkout['url']);
    }
}
