<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CartService;
use App\Services\StripeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Stripe\Exception\ApiErrorException;
use Livewire\Component;

class Checkout extends Component
{
    private const SHIPPING_COUNTRIES = [
        'AT' => 'Austria',
        'BE' => 'Belgium',
        'BG' => 'Bulgaria',
        'HR' => 'Croatia',
        'CY' => 'Cyprus',
        'CZ' => 'Czechia',
        'DK' => 'Denmark',
        'EE' => 'Estonia',
        'FI' => 'Finland',
        'FR' => 'France',
        'DE' => 'Germany',
        'GR' => 'Greece',
        'HU' => 'Hungary',
        'IE' => 'Ireland',
        'IT' => 'Italy',
        'LV' => 'Latvia',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MT' => 'Malta',
        'NL' => 'Netherlands',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'RO' => 'Romania',
        'SK' => 'Slovakia',
        'SI' => 'Slovenia',
        'ES' => 'Spain',
        'SE' => 'Sweden',
    ];

    public string $customer_name = '';
    public string $customer_email = '';
    public string $customer_phone = '';
    public string $company_name = '';
    public string $tax_id = '';

    public string $shipping_address_line_1 = '';
    public string $shipping_address_line_2 = '';
    public string $shipping_city = '';
    public string $shipping_state = '';
    public string $shipping_postal_code = '';
    public string $shipping_country = 'ES';

    public string $notes = '';

    public bool $use_different_billing = false;
    public string $billing_address_line_1 = '';
    public string $billing_address_line_2 = '';
    public string $billing_city = '';
    public string $billing_state = '';
    public string $billing_postal_code = '';
    public string $billing_country = 'ES';

    public bool $acceptTerms = false;
    public array $cartItems = [];

    public function mount(): void
    {
        $this->cartItems = app(CartService::class)->getItems();
    }

    protected function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'tax_id' => 'nullable|string|max:50',
            'shipping_address_line_1' => 'required|string|max:255',
            'shipping_address_line_2' => 'nullable|string|max:255',
            'shipping_city' => 'required|string|max:255',
            'shipping_state' => 'nullable|string|max:255',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => ['required', 'string', Rule::in(array_keys(self::SHIPPING_COUNTRIES))],
            'notes' => 'nullable|string|max:2000',
            'use_different_billing' => 'boolean',
            'billing_address_line_1' => 'nullable|required_if:use_different_billing,true|string|max:255',
            'billing_address_line_2' => 'nullable|string|max:255',
            'billing_city' => 'nullable|required_if:use_different_billing,true|string|max:255',
            'billing_state' => 'nullable|string|max:255',
            'billing_postal_code' => 'nullable|required_if:use_different_billing,true|string|max:20',
            'billing_country' => ['required_if:use_different_billing,true', 'nullable', 'string', Rule::in(array_keys(self::SHIPPING_COUNTRIES))],
            'acceptTerms' => 'accepted',
        ];
    }

    public function getShippingCostProperty(): float
    {
        return (float) config('commerce.shipping_cost', 15.00);
    }

    public function getTaxRateProperty(): float
    {
        return (float) config('commerce.tax_rate', 0.21);
    }

    public function getSubtotalProperty(): float
    {
        return app(CartService::class)->getTotal();
    }

    public function getTaxProperty(): float
    {
        return round(($this->subtotal + $this->shippingCost) * $this->taxRate, 2);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->shippingCost + $this->getTaxProperty();
    }

    public function placeOrder(): void
    {
        $credentials = app(\App\Services\StripeCredentials::class);
        abort_unless($credentials->productCheckoutReady(), 404);
        $this->validate();

        $cart = app(CartService::class);
        $order = DB::transaction(function () use ($cart): Order {
            $cartItems = $cart->getPurchasableItems();
            $subtotal = array_sum(array_map(
                fn (array $item): float => $item['price'] * $item['quantity'],
                $cartItems,
            ));
            $tax = round(($subtotal + $this->shippingCost) * $this->taxRate, 2);

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'customer_email' => $this->customer_email,
                'customer_name' => $this->customer_name,
                'customer_phone' => $this->customer_phone,
                'company_name' => $this->company_name ?: null,
                'tax_id' => $this->tax_id ?: null,
                'shipping_address_line_1' => $this->shipping_address_line_1,
                'shipping_address_line_2' => $this->shipping_address_line_2 ?: null,
                'shipping_city' => $this->shipping_city,
                'shipping_state' => $this->shipping_state ?: null,
                'shipping_postal_code' => $this->shipping_postal_code,
                'shipping_country' => $this->shipping_country,
                'billing_address_line_1' => $this->use_different_billing ? $this->billing_address_line_1 : $this->shipping_address_line_1,
                'billing_address_line_2' => $this->use_different_billing ? $this->billing_address_line_2 : $this->shipping_address_line_2,
                'billing_city' => $this->use_different_billing ? $this->billing_city : $this->shipping_city,
                'billing_state' => $this->use_different_billing ? $this->billing_state : $this->shipping_state,
                'billing_postal_code' => $this->use_different_billing ? $this->billing_postal_code : $this->shipping_postal_code,
                'billing_country' => $this->use_different_billing ? $this->billing_country : $this->shipping_country,
                'subtotal' => $subtotal,
                'shipping_cost' => $this->shippingCost,
                'tax' => $tax,
                'total' => $subtotal + $this->shippingCost + $tax,
                'notes' => $this->notes ?: null,
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name' => $item['name'],
                    'variant_name' => $item['variant_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total_price' => $item['price'] * $item['quantity'],
                ]);
            }

            return $order;
        });

        try {
            $session = app(StripeService::class)->createCheckoutSession(
                $order->load('items'),
                route('checkout.success'),
                route('cart.index'),
            );
        } catch (ApiErrorException $exception) {
            $order->update(['payment_status' => 'failed']);
            Log::error('Stripe product checkout session could not be created.', [
                'order_id' => $order->id,
                'exception' => $exception->getMessage(),
            ]);
            session()->flash('error', 'No se pudo iniciar el pago. No se realizó ningún cobro; contacta con nosotros para recibir ayuda.');

            return;
        }

        $order->update(['stripe_checkout_session_id' => $session->id]);
        $cart->clear();
        $this->redirect($session->url);
    }

    public function render()
    {
        return view('livewire.checkout', [
            'shippingCountries' => self::SHIPPING_COUNTRIES,
            'shippingCost' => $this->shippingCost,
        ]);
    }
}
