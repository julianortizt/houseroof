<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartPage extends Component
{
    public array $items = [];

    protected $listeners = ['cartUpdated' => '$refresh'];

    public function mount(): void
    {
        $this->refreshCart();
    }

    public function refreshCart(): void
    {
        $cart = app(CartService::class);
        $this->items = $cart->getItems();
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $cart = app(CartService::class);
        $cart->updateQuantity($key, $quantity);
        $this->refreshCart();
        $this->dispatch('cartUpdated');
    }

    public function removeItem(string $key): void
    {
        $cart = app(CartService::class);
        $cart->removeItem($key);
        $this->refreshCart();
        $this->dispatch('cartUpdated');
    }

    public function getSubtotalProperty(): float
    {
        return app(CartService::class)->getTotal();
    }

    public function getShippingCostProperty(): float
    {
        return (float) config('commerce.shipping_cost', 15.00);
    }

    public function getTaxRateProperty(): float
    {
        return (float) config('commerce.tax_rate', 0.21);
    }

    public function getTaxProperty(): float
    {
        return round(($this->subtotal + $this->shippingCost) * $this->taxRate, 2);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->shippingCost + $this->getTaxProperty();
    }

    public function render()
    {
        return view('livewire.cart-page');
    }
}
