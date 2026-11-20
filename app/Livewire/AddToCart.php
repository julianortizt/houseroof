<?php

namespace App\Livewire;

use App\Services\CartService;
use App\Services\PlatformModules;
use Livewire\Component;

class AddToCart extends Component
{
    public int $productId;
    public ?int $selectedVariantId = null;
    public int $quantity = 1;

    public function addToCart(): void
    {
        abort_unless(app(PlatformModules::class)->enabled('products'), 404);

        $cart = app(CartService::class);
        $cart->addItem($this->productId, $this->quantity, $this->selectedVariantId);

        $this->dispatch('cartUpdated');
        session()->flash('success', 'Producto añadido al carrito correctamente.');
    }

    public function render()
    {
        return view('livewire.add-to-cart');
    }
}
