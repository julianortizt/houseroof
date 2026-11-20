<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StripeCredentials;
use App\Services\StripeGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class OrderStockResolutionService
{
    public function __construct(
        private readonly StripeCredentials $credentials,
        private readonly StripeGateway $stripe,
    ) {}

    public function fulfillAfterRestock(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->status !== 'stock_review' || $lockedOrder->payment_status !== 'paid') {
                throw ValidationException::withMessages([
                    'order' => 'Solo se pueden resolver pedidos pagados en revisión de existencias.',
                ]);
            }

            $requiredStock = [];
            foreach ($lockedOrder->items()->lockForUpdate()->get() as $item) {
                $type = $item->product_variant_id ? 'variant' : 'product';
                $id = $item->product_variant_id ?: $item->product_id;
                if (! $id) {
                    throw ValidationException::withMessages([
                        'order' => 'Un artículo del pedido ya no tiene un producto asociado; requiere revisión manual.',
                    ]);
                }
                $key = $type . ':' . $id;
                $requiredStock[$key] = [
                    'type' => $type,
                    'id' => (int) $id,
                    'quantity' => ($requiredStock[$key]['quantity'] ?? 0) + (int) $item->quantity,
                ];
            }

            ksort($requiredStock);
            $stockItems = [];
            foreach ($requiredStock as $key => $requirement) {
                $model = $requirement['type'] === 'variant' ? ProductVariant::class : Product::class;
                $stockItem = $model::query()->lockForUpdate()->find($requirement['id']);
                if (! $stockItem || (int) $stockItem->stock < $requirement['quantity']) {
                    throw ValidationException::withMessages([
                        'order' => 'El stock todavía es insuficiente. No se modificó ninguna existencia.',
                    ]);
                }
                $stockItems[$key] = [$stockItem, $requirement['quantity']];
            }

            foreach ($stockItems as [$stockItem, $quantity]) {
                $stockItem->decrement('stock', $quantity);
            }

            $lockedOrder->update(['status' => 'processing']);

            return $lockedOrder->fresh();
        });
    }

    /**
     * @throws ApiErrorException
     */
    public function refundStockReview(Order $order): Order
    {
        $secretKey = $this->credentials->secretKey();
        abort_unless(filled($secretKey), 503, 'Stripe secret key is not configured.');

        $lockedOrder = DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if (
                $lockedOrder->stock_review_refund_status === 'succeeded'
                && $lockedOrder->payment_status === 'refunded'
                && $lockedOrder->status === 'cancelled'
            ) {
                return $lockedOrder;
            }
            if (
                $lockedOrder->status !== 'stock_review'
                || $lockedOrder->payment_status !== 'paid'
                || blank($lockedOrder->stripe_payment_intent_id)
            ) {
                throw ValidationException::withMessages([
                    'order' => 'Solo se pueden reembolsar pedidos pagados en revisión con un pago Stripe confirmado.',
                ]);
            }
            if ($lockedOrder->stock_review_refund_status === 'failed') {
                throw ValidationException::withMessages([
                    'order' => 'Stripe marcó fallido el reembolso anterior. Reconcilia el caso en Stripe antes de solicitar otra operación.',
                ]);
            }
            if (
                $lockedOrder->stock_review_refund_status === 'pending'
                && blank($lockedOrder->stripe_refund_id)
                && $lockedOrder->stock_review_refund_requested_at?->lessThanOrEqualTo(now()->subHours(23))
            ) {
                abort(409, 'El reembolso no tiene identificador después de 23 horas; reconcilia el estado en Stripe antes de reintentar.');
            }

            $lockedOrder->update([
                'stock_review_refund_status' => 'pending',
                'stock_review_refund_idempotency_key' => $lockedOrder->stock_review_refund_idempotency_key ?: (string) Str::uuid(),
                'stock_review_refund_amount_cents' => $lockedOrder->stock_review_refund_amount_cents
                    ?: (int) round((float) $lockedOrder->total * 100),
                'stock_review_refund_requested_at' => $lockedOrder->stock_review_refund_requested_at ?: now(),
            ]);

            return $lockedOrder->fresh();
        });

        if ($lockedOrder->stock_review_refund_status === 'succeeded') {
            return $lockedOrder;
        }

        $result = filled($lockedOrder->stripe_refund_id)
            ? $this->stripe->retrieveRefund($secretKey, $lockedOrder->stripe_refund_id)
            : $this->stripe->createRefund(
                $secretKey,
                $lockedOrder->stripe_payment_intent_id,
                $lockedOrder->stock_review_refund_amount_cents,
                $lockedOrder->stock_review_refund_idempotency_key,
                [
                    'order_id' => (string) $lockedOrder->id,
                    'resolution' => 'stock_review',
                ],
            );

        if (blank($result['id'] ?? null)) {
            throw new \RuntimeException('Stripe returned a refund without an identifier.');
        }

        return $this->applyRefundResult($lockedOrder, $result);
    }

    public function handleRefundUpdated(array $refundData): void
    {
        $orderId = $refundData['metadata']['order_id'] ?? null;
        if (! $orderId || ($refundData['metadata']['resolution'] ?? null) !== 'stock_review') {
            return;
        }

        DB::transaction(function () use ($orderId, $refundData): void {
            $order = Order::query()->whereKey($orderId)->lockForUpdate()->first();
            if (
                ! $order
                || $order->status !== 'stock_review'
                || $order->payment_status !== 'paid'
                || $order->stripe_payment_intent_id !== ($refundData['payment_intent'] ?? null)
                || (filled($order->stripe_refund_id) && $order->stripe_refund_id !== ($refundData['id'] ?? null))
                || (int) ($refundData['amount'] ?? 0) !== (int) $order->stock_review_refund_amount_cents
            ) {
                return;
            }

            $this->updateRefundStatus($order, $refundData);
        });
    }

    private function applyRefundResult(Order $order, array $result): Order
    {
        return DB::transaction(function () use ($order, $result): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->status !== 'stock_review' || $lockedOrder->payment_status !== 'paid') {
                return $lockedOrder;
            }
            $this->updateRefundStatus($lockedOrder, $result);

            return $lockedOrder->fresh();
        });
    }

    private function updateRefundStatus(Order $order, array $refund): void
    {
        $refundStatus = match ($refund['status'] ?? null) {
            'succeeded' => 'succeeded',
            'failed', 'canceled' => 'failed',
            default => 'pending',
        };
        $updates = [
            'stripe_refund_id' => $refund['id'] ?? $order->stripe_refund_id,
            'stock_review_refund_status' => $refundStatus,
        ];
        if ($refundStatus === 'succeeded') {
            $updates['payment_status'] = 'refunded';
            $updates['status'] = 'cancelled';
        }
        $order->update($updates);
    }
}
