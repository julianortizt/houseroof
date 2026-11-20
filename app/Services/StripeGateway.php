<?php

namespace App\Services;

use Stripe\StripeClient;

class StripeGateway
{
    public function createCheckoutSession(string $secretKey, array $parameters, string $idempotencyKey): array
    {
        return (new StripeClient($secretKey))
            ->checkout
            ->sessions
            ->create($parameters, ['idempotency_key' => $idempotencyKey])
            ->toArray();
    }

    public function retrieveCheckoutSession(string $secretKey, string $sessionId): array
    {
        return (new StripeClient($secretKey))
            ->checkout
            ->sessions
            ->retrieve($sessionId, [])
            ->toArray();
    }

    public function createRefund(
        string $secretKey,
        string $paymentIntentId,
        int $amountCents,
        string $idempotencyKey,
        array $metadata,
    ): array {
        return (new StripeClient($secretKey))
            ->refunds
            ->create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amountCents,
                'metadata' => $metadata,
            ], ['idempotency_key' => $idempotencyKey])
            ->toArray();
    }

    public function retrieveRefund(string $secretKey, string $refundId): array
    {
        return (new StripeClient($secretKey))
            ->refunds
            ->retrieve($refundId, [])
            ->toArray();
    }
}
