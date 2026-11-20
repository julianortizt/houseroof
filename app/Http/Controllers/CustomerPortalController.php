<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Quote;
use App\Models\MapsCalculator\Client;
use App\Services\PlatformModules;
use App\Services\QuotePaymentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.portal');
        }

        return view('customer.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $email = mb_strtolower(trim($credentials['email']));
        $customer = Customer::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        if (! $customer || ! Hash::check($credentials['password'], $customer->password)) {
            return back()->withErrors(['email' => 'Las credenciales no son válidas.'])->onlyInput('email');
        }

        Auth::guard('customer')->login($customer, $request->boolean('remember'));
        $request->session()->regenerate();

        if (! Auth::guard('customer')->user()->hasVerifiedEmail()) {
            Auth::guard('customer')->user()->sendEmailVerificationNotification();

            return redirect()->route('verification.notice');
        }

        return redirect()->intended(route('customer.portal'));
    }

    public function showRegistration(): View|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('customer.portal');
        }

        return view('customer.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $customer = Customer::create($validated);
        event(new Registered($customer));
        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function verificationNotice(): View
    {
        return view('customer.verify-email');
    }

    public function sendVerification(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        if ($customer->hasVerifiedEmail()) {
            return redirect()->route('customer.portal');
        }

        $customer->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }

    public function verify(Request $request): RedirectResponse
    {
        $customer = Auth::guard('customer')->user();

        abort_unless(
            hash_equals((string) $request->route('id'), (string) $customer->getKey())
                && hash_equals((string) $request->route('hash'), sha1($customer->getEmailForVerification())),
            403
        );

        if (! $customer->hasVerifiedEmail()) {
            $customer->markEmailAsVerified();
            event(new \Illuminate\Auth\Events\Verified($customer));
        }

        return redirect()->route('customer.portal')->with('status', 'email-verified');
    }

    public function portal(PlatformModules $modules, QuotePaymentService $payments): View
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();
        $email = mb_strtolower($customer->email);
        $mapClient = Client::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();

        $projects = $modules->enabled('work_orders') && $mapClient
            ? $mapClient->projects()->with(['workOrder', 'quotes'])->latest()->get()
            : collect();

        $quotes = $modules->enabled('quotes')
            ? Quote::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->with(['items', 'project', 'payments.refunds', 'workOrder'])
                ->latest()
                ->limit(50)
                ->get()
            : collect();
        $quotes->each(fn (Quote $quote) => $quote->setAttribute('payment_summary', $payments->paymentSummary($quote)));

        $orders = $modules->enabled('products')
            ? Order::query()
                ->whereRaw('LOWER(customer_email) = ?', [$email])
                ->latest()
                ->limit(50)
                ->get()
            : collect();

        return view('customer.portal', compact('customer', 'projects', 'quotes', 'orders'));
    }

    public function respondToQuote(Request $request, Quote $quote): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);
        $customerEmail = mb_strtolower(Auth::guard('customer')->user()->email);

        DB::transaction(function () use ($quote, $validated, $customerEmail): void {
            $ownedQuote = Quote::query()
                ->whereKey($quote->getKey())
                ->whereRaw('LOWER(email) = ?', [$customerEmail])
                ->where('status', 'enviado')
                ->where(fn ($query) => $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', today()))
                ->lockForUpdate()
                ->firstOrFail();

            if ($validated['decision'] === 'approved') {
                $ownedQuote->update([
                    'status' => 'aprobado',
                    'approved_at' => now(),
                ]);
            } else {
                $ownedQuote->update([
                    'status' => 'rechazado',
                    'rejected_at' => now(),
                ]);
            }
        });

        return redirect()->route('customer.portal')->with('status', 'quote-response-saved');
    }

    public function downloadQuotePdf(Quote $quote, PlatformModules $modules)
    {
        abort_unless($modules->enabled('quotes'), 404);
        $customerEmail = mb_strtolower(Auth::guard('customer')->user()->email);
        $ownedQuote = Quote::query()
            ->whereKey($quote->getKey())
            ->whereRaw('LOWER(email) = ?', [$customerEmail])
            ->whereIn('status', ['enviado', 'aprobado', 'rechazado', 'expirado'])
            ->firstOrFail();

        return app(\App\Services\QuotePdfService::class)
            ->make($ownedQuote)
            ->download('presupuesto-' . $ownedQuote->formatted_number . '.pdf');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('customer.login');
    }
}
