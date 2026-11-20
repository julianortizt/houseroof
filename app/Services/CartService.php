<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class CartService
{
    protected string $sessionKey = 'cart';

    public function getItems(): array
    {
        $items = Session::get($this->sessionKey, []);

        foreach ($items as $key => $item) {
            $product = Product::query()->active()->find($item['product_id'] ?? null);
            $variantId = $item['variant_id'] ?? null;
            $variant = $variantId && $product
                ? $product->variants()->active()->find($variantId)
                : null;

            if (! $product || ($variantId && ! $variant)) {
                continue;
            }

            $items[$key]['name'] = $product->getTranslation('name', app()->getLocale());
            $items[$key]['variant_name'] = $variant?->getTranslation('name', app()->getLocale());
            $items[$key]['price'] = $variant ? $variant->getEffectivePrice() : $product->price;
            $items[$key]['image'] = $product->getFirstMediaUrl('default', 'thumb');
        }

        return $items;
    }

    public function addItem(int $productId, int $quantity = 1, ?int $variantId = null): void
    {
        if ($quantity < 1 || $quantity > 99) {
            throw ValidationException::withMessages(['quantity' => 'La cantidad debe estar entre 1 y 99.']);
        }

        $product = Product::query()->active()->find($productId);
        if (! $product || ! $product->isPurchasable()) {
            throw ValidationException::withMessages(['cart' => 'Este producto no está disponible para compra.']);
        }

        $variant = $variantId
            ? $product->variants()->active()->find($variantId)
            : null;

        if ($variantId && ! $variant) {
            throw ValidationException::withMessages(['cart' => 'La variante seleccionada no está disponible.']);
        }

        $cart = $this->getItems();
        $key = $this->buildItemKey($productId, $variantId);
        $newQuantity = ($cart[$key]['quantity'] ?? 0) + $quantity;
        $availableStock = $variant?->stock ?? $product->stock;

        if ($newQuantity > $availableStock) {
            throw ValidationException::withMessages(['quantity' => 'La cantidad solicitada supera las existencias disponibles.']);
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'name' => $product->getTranslation('name', app()->getLocale()),
            'variant_name' => $variant?->getTranslation('name', app()->getLocale()),
            'price' => $variant ? $variant->getEffectivePrice() : $product->price,
            'quantity' => $newQuantity,
            'image' => $product->getFirstMediaUrl('default', 'thumb'),
        ];

        Session::put($this->sessionKey, $cart);
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($key);
            return;
        }

        if ($quantity > 99) {
            throw ValidationException::withMessages(['quantity' => 'La cantidad máxima por artículo es 99.']);
        }

        $cart = $this->getItems();

        if (! isset($cart[$key])) {
            return;
        }

        $product = Product::query()->active()->find($cart[$key]['product_id']);
        $variant = ! empty($cart[$key]['variant_id']) && $product
            ? $product->variants()->active()->find($cart[$key]['variant_id'])
            : null;

        if (! $product || ! $product->isPurchasable() || (! empty($cart[$key]['variant_id']) && ! $variant)) {
            throw ValidationException::withMessages(['cart' => 'Este producto o variante ya no está disponible.']);
        }

        if ($quantity > ($variant?->stock ?? $product->stock)) {
            throw ValidationException::withMessages(['quantity' => 'La cantidad solicitada supera las existencias disponibles.']);
        }

        $cart[$key]['quantity'] = $quantity;
        Session::put($this->sessionKey, $cart);
    }

    public function getPurchasableItems(): array
    {
        $items = [];
        $quantitiesByStock = [];

        foreach ($this->getItems() as $item) {
            $product = Product::query()
                ->active()
                ->lockForUpdate()
                ->find($item['product_id'] ?? null);

            $variantId = $item['variant_id'] ?? null;
            $variant = $variantId && $product
                ? $product->variants()->active()->lockForUpdate()->find($variantId)
                : null;
            $quantity = filter_var($item['quantity'] ?? null, FILTER_VALIDATE_INT);

            if (
                ! $product
                || ! $product->isPurchasable()
                || ($variantId && ! $variant)
                || $quantity === false
                || $quantity < 1
                || $quantity > 99
            ) {
                throw ValidationException::withMessages(['cart' => 'Revisa tu carrito: hay productos que ya no están disponibles.']);
            }

            $stockKey = $variant ? 'variant:' . $variant->id : 'product:' . $product->id;
            $quantitiesByStock[$stockKey] = ($quantitiesByStock[$stockKey] ?? 0) + $quantity;
            $availableStock = $variant?->stock ?? $product->stock;

            if ($quantitiesByStock[$stockKey] > $availableStock) {
                throw ValidationException::withMessages(['cart' => 'Las existencias han cambiado. Ajusta la cantidad de tu carrito.']);
            }

            $items[] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->getTranslation('name', app()->getLocale()),
                'variant_name' => $variant?->getTranslation('name', app()->getLocale()),
                'price' => (float) ($variant ? $variant->getEffectivePrice() : $product->price),
                'quantity' => $quantity,
            ];
        }

        if ($items === []) {
            throw ValidationException::withMessages(['cart' => 'El carrito está vacío.']);
        }

        return $items;
    }

    public function removeItem(string $key): void
    {
        $cart = $this->getItems();
        unset($cart[$key]);
        Session::put($this->sessionKey, $cart);
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    public function getTotal(): float
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getTotalFormatted(): string
    {
        return number_format($this->getTotal(), 2, ',', '.') . ' €';
    }

    public function getItemCount(): int
    {
        $count = 0;
        foreach ($this->getItems() as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }

    public function getSummary(): array
    {
        $items = $this->getItems();
        $subtotal = 0.0;

        foreach ($items as $item) {
            $subtotal += (float) ($item['price'] ?? 0) * (int) ($item['quantity'] ?? 0);
        }

        $shipping = $this->getShippingCost();
        $tax = round(($subtotal + $shipping) * $this->getTaxRate(), 2);
        $total = round($subtotal + $shipping + $tax, 2);

        return [
            'item_count' => $this->getItemCount(),
            'subtotal' => round($subtotal, 2),
            'shipping' => round($shipping, 2),
            'tax' => round($tax, 2),
            'total' => round($total, 2),
            'is_empty' => $items === [],
        ];
    }

    public function getShippingCost(): float
    {
        return (float) config('commerce.shipping_cost', 15.00);
    }

    public function getTaxRate(): float
    {
        return (float) config('commerce.tax_rate', 0.21);
    }

    protected function buildItemKey(int $productId, ?int $variantId): string
    {
        return $productId . '_' . ($variantId ?? 'default');
    }
}
