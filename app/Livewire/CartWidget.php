<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class CartWidget extends Component
{
    public int $itemCount = 0;
    public bool $isOpen = false;

    protected $listeners = ['cartUpdated' => '$refresh'];

    public function mount(): void
    {
        $this->refreshCount();
    }

    public function refreshCount(): void
    {
        $cart = app(CartService::class);
        $this->itemCount = $cart->getItemCount();
    }

    public function toggleDropdown(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function render()
    {
        $cart = app(CartService::class);
        $items = $cart->getItems();
        $total = $cart->getTotalFormatted();

        return view('livewire.cart-widget', compact('items', 'total'));
    }
}
