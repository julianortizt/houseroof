<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuotePayment;
use App\Models\QuotePaymentRefund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Stripe\Exception\ApiErrorException;

class QuotePaymentService
{
    public function __construct(
        private readonly StripeCredentials $credentials,
        private readonly PlatformModules $modules,
        private readonly StripeGateway $stripe,
    ) {}

    public function paymentSummary(Quote $quote): array
    {
        $quote->loadMissing(['payments.refunds', 'workOrder']);
        $financials = $this->financials($quote);
        $servicePaymentsReady = $this->modules->enabled('quotes')
            && $this->modules->enabled('payments')
            && $this->modules->enabled('invoicing')
            && $this->credentials->servicePaymentsEnabled();
        $approved = $quote->status === 'aprobado' && $quote->total > 0;
        $completed = in_array($quote->workOrder?->status, ['finalizada', 'entregada'], true);

        return [
            ...$financials,
            'ready' => $servicePaymentsReady,
            'can_pay_deposit' => $servicePaymentsReady
                && $approved
                && $financials['total_cents'] >= 100
                && $financials['deposit_due_cents'] >= 50,
            'can_pay_balance' => $servicePaymentsReady
                && $approved
                && $financials['total_cents'] >= 100
                && $completed
                && $financials['deposit_due_cents'] === 0
                && $financials['balance_due_cents'] >= 50,
        ];
    }

    public function createCheckout(Quote $quote, Customer $customer, string $paymentType): array
    {
        $this->assertServicePaymentsReady();
        abort_unless($customer->hasVerifiedEmail(), 403, 'A verified customer account is required for payments.');
        $secretKey = $this->requireSecretKey();
        $payment = $this->reserveCheckout($quote, $customer, $paymentType);

        if ($payment->stripe_checkout_session_id) {
            $session = $this->stripe->retrieveCheckoutSession($secretKey, $payment->stripe_checkout_session_id);

            if (($session['status'] ?? null) === 'open'
                && (int) ($session['expires_at'] ?? 0) > now()->timestamp
                && filled($session['url'] ?? null)) {
                return ['url' => $session['url'], 'payment' => $payment];
            }

            if (($session['status'] ?? null) !== 'expired'
                && !(($session['status'] ?? null) === 'open' && (int) ($session['expires_at'] ?? 0) <= now()->timestamp)) {
                throw ValidationException::withMessages([
                    'payment' => 'El pago está en proceso de confirmación. Actualiza el portal en unos instantes.',
                ]);
            }

            $payment->update(['status' => 'expired']);
            return $this->createCheckout($quote->fresh(), $customer, $paymentType);
        }

        try {
            $session = $this->stripe->createCheckoutSession(
                $secretKey,
                [
                    'mode' => 'payment',
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'eur',
                            'unit_amount' => $payment->amount_cents,
                            'product_data' => [
                                'name' => $payment->payment_type === 'deposit'
                                    ? 'Anticipo de cotización ' . $quote->formatted_number
                                    : 'Saldo final de cotización ' . $quote->formatted_number,
                            ],
                        ],
                        'quantity' => 1,
                    ]],
                    'customer_email' => $customer->email,
                    'client_reference_id' => (string) $payment->id,
                    'metadata' => [
                        'quote_payment_id' => (string) $payment->id,
                        'quote_id' => (string) $quote->id,
                        'payment_type' => $payment->payment_type,
                    ],
                    'success_url' => route('customer.portal', ['payment' => 'success']),
                    'cancel_url' => route('customer.portal', ['payment' => 'cancelled']),
                    'expires_at' => now()->addMinutes(30)->timestamp,
                ],
                $payment->checkout_idempotency_key,
            );
        } catch (ApiErrorException $exception) {
            report($exception);
            throw ValidationException::withMessages([
                'payment' => 'Stripe no pudo iniciar el pago. No se realizó ningún cobro; vuelve a intentarlo o contacta con soporte.',
            ]);
        }

        if (blank($session['id'] ?? null) || blank($session['url'] ?? null)) {
            throw new \RuntimeException('Stripe returned a checkout session without an identifier or URL.');
        }

        $payment->update([
            'stripe_checkout_session_id' => $session['id'],
        ]);

        return ['url' => $session['url'], 'payment' => $payment->fresh()];
    }

    public function handleCheckoutCompleted(array $session): void
    {
        $paymentId = $session['metadata']['quote_payment_id'] ?? null;
        if (! $paymentId) {
            return;
        }

        DB::transaction(function () use ($paymentId, $session): void {
            $payment = QuotePayment::query()
                ->whereKey($paymentId)
                ->where('stripe_checkout_session_id', $session['id'] ?? '')
                ->lockForUpdate()
                ->first();

            if (! $payment || $payment->status !== 'pending') {
                return;
            }

            $paymentIntent = $session['payment_intent'] ?? null;
            $paymentIntentId = is_array($paymentIntent) ? ($paymentIntent['id'] ?? null) : $paymentIntent;
            if (($session['payment_status'] ?? null) !== 'paid'
                || (int) ($session['amount_total'] ?? 0) !== $payment->amount_cents
                || strtolower((string) ($session['currency'] ?? '')) !== $payment->currency
                || (string) ($session['metadata']['quote_id'] ?? '') !== (string) $payment->quote_id
                || ($session['metadata']['payment_type'] ?? null) !== $payment->payment_type
                || ! is_string($paymentIntentId)
                || $paymentIntentId === '') {
                return;
            }

            $payment->update([
                'status' => 'paid',
                'stripe_payment_intent_id' => $paymentIntentId,
                'paid_at' => now(),
            ]);

            app(AuditLogger::class)->recordQuotePaymentReceived($payment);
        });
    }

    public function handleCheckoutClosed(array $session, string $status = 'expired'): void
    {
        $paymentId = $session['metadata']['quote_payment_id'] ?? null;
        if (! $paymentId) {
            return;
        }

        DB::transaction(function () use ($paymentId, $session, $status): void {
            $payment = QuotePayment::query()
                ->whereKey($paymentId)
                ->where('stripe_checkout_session_id', $session['id'] ?? '')
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $payment) {
                return;
            }

            $payment->update(['status' => $status]);
            app(AuditLogger::class)->recordQuotePaymentStatusChanged($payment, 'pending');
        });
    }

    public function handleRefundUpdated(array $refundData): void
    {
        $refund = QuotePaymentRefund::query()
            ->where('stripe_refund_id', $refundData['id'] ?? '')
            ->when(
                isset($refundData['metadata']['quote_payment_refund_id']),
                fn ($query) => $query->orWhereKey($refundData['metadata']['quote_payment_refund_id']),
            )
            ->first();

        if (! $refund) {
            return;
        }

        $status = $this->refundStatus($refundData['status'] ?? null);
        if ($refund->status !== $status) {
            $oldStatus = $refund->status;
            $refund->update([
                'stripe_refund_id' => $refundData['id'] ?? $refund->stripe_refund_id,
                'status' => $status,
            ]);
            app(AuditLogger::class)->recordQuoteRefundStatusChanged($refund, $oldStatus);
        }
    }

    public function refund(QuotePayment $payment, int $amountCents): QuotePaymentRefund
    {
        $this->authorizeRefundAdministration();
        $secretKey = $this->requireSecretKey();
        $refund = $this->reserveRefund($payment, $amountCents);

        return $this->submitRefund($refund, $secretKey);
    }

    public function retryRefund(QuotePaymentRefund $refund): QuotePaymentRefund
    {
        $this->authorizeRefundAdministration();
        abort_unless($refund->status === 'pending', 409, 'Only pending refunds can be retried.');

        $secretKey = $this->requireSecretKey();
        if (filled($refund->stripe_refund_id)) {
            $result = $this->stripe->retrieveRefund($secretKey, $refund->stripe_refund_id);
            $oldStatus = $refund->status;
            $refund->update(['status' => $this->refundStatus($result['status'] ?? null)]);
            app(AuditLogger::class)->recordQuoteRefundStatusChanged($refund->fresh(), $oldStatus);

            return $refund->fresh();
        }

        abort_unless(
            $refund->created_at?->greaterThan(now()->subHours(23)),
            409,
            'No se puede reintentar un reembolso sin identificador después de 23 horas; reconcilia el estado en Stripe para evitar duplicarlo.',
        );

        return $this->submitRefund($refund, $secretKey);
    }

    private function reserveCheckout(Quote $quote, Customer $customer, string $paymentType): QuotePayment
    {
        if (! in_array($paymentType, ['deposit', 'balance'], true)) {
            throw ValidationException::withMessages(['payment' => 'Tipo de pago no válido.']);
        }

        return DB::transaction(function () use ($quote, $customer, $paymentType): QuotePayment {
            $lockedQuote = Quote::query()->whereKey($quote->getKey())->lockForUpdate()->firstOrFail();
            if (mb_strtolower($lockedQuote->email) !== mb_strtolower($customer->email)) {
                abort(404);
            }
            if ($lockedQuote->status !== 'aprobado' || (float) $lockedQuote->total <= 0) {
                throw ValidationException::withMessages(['payment' => 'Solo se pueden pagar cotizaciones aprobadas con importe válido.']);
            }

            $pending = $lockedQuote->payments()
                ->where('payment_type', $paymentType)
                ->where('status', 'pending')
                ->latest('id')
                ->first();
            if ($pending) {
                return $pending;
            }

            $financials = $this->financials($lockedQuote);
            if ($financials['total_cents'] < 100) {
                throw ValidationException::withMessages([
                    'payment' => 'El importe total debe ser al menos 1,00 € para dividirlo en anticipo y saldo online.',
                ]);
            }

            if ($paymentType === 'deposit') {
                $amountCents = $financials['deposit_due_cents'];
            } else {
                $workOrderStatus = $lockedQuote->workOrder()->value('status');
                if (! in_array($workOrderStatus, ['finalizada', 'entregada'], true)) {
                    throw ValidationException::withMessages(['payment' => 'El saldo estará disponible cuando la orden de trabajo esté finalizada.']);
                }
                if ($financials['deposit_due_cents'] > 0) {
                    throw ValidationException::withMessages(['payment' => 'El anticipo debe estar pagado antes de cobrar el saldo final.']);
                }
                $amountCents = $financials['balance_due_cents'];
            }

            if ($amountCents < 50) {
                throw ValidationException::withMessages(['payment' => 'El importe pendiente no alcanza el mínimo de pago online de 0,50 €.']);
            }

            $payment = $lockedQuote->payments()->create([
                'customer_id' => $customer->id,
                'payment_type' => $paymentType,
                'amount_cents' => $amountCents,
                'currency' => 'eur',
                'status' => 'pending',
                'checkout_idempotency_key' => (string) Str::uuid(),
            ]);

            app(AuditLogger::class)->recordQuotePaymentCheckoutCreated($payment);

            return $payment;
        });
    }

    private function reserveRefund(QuotePayment $payment, int $amountCents): QuotePaymentRefund
    {
        if ($amountCents < 1) {
            throw ValidationException::withMessages(['amount' => 'El importe del reembolso debe ser mayor que cero.']);
        }

        return DB::transaction(function () use ($payment, $amountCents): QuotePaymentRefund {
            $lockedPayment = QuotePayment::query()
                ->whereKey($payment->getKey())
                ->with('refunds')
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status !== 'paid' || blank($lockedPayment->stripe_payment_intent_id)) {
                throw ValidationException::withMessages(['payment' => 'Solo se pueden reembolsar pagos confirmados por Stripe.']);
            }

            if ($amountCents > $lockedPayment->refundable_amount_cents) {
                throw ValidationException::withMessages(['amount' => 'El reembolso excede el importe todavía reembolsable.']);
            }

            $refund = $lockedPayment->refunds()->create([
                'requested_by' => auth('web')->id(),
                'amount_cents' => $amountCents,
                'status' => 'pending',
                'idempotency_key' => (string) Str::uuid(),
            ]);

            app(AuditLogger::class)->recordQuoteRefundRequested($refund);

            return $refund;
        });
    }

    private function submitRefund(QuotePaymentRefund $refund, string $secretKey): QuotePaymentRefund
    {
        $refund->loadMissing('payment');
        $result = $this->stripe->createRefund(
            $secretKey,
            $refund->payment->stripe_payment_intent_id,
            $refund->amount_cents,
            $refund->idempotency_key,
            ['quote_payment_refund_id' => (string) $refund->id],
        );

        if (blank($result['id'] ?? null)) {
            throw new \RuntimeException('Stripe returned a refund without an identifier.');
        }

        $oldStatus = $refund->status;
        $refund->update([
            'stripe_refund_id' => $result['id'] ?? null,
            'status' => $this->refundStatus($result['status'] ?? null),
        ]);
        app(AuditLogger::class)->recordQuoteRefundStatusChanged($refund->fresh(), $oldStatus);

        return $refund->fresh();
    }

    private function financials(Quote $quote): array
    {
        $totalCents = max(0, (int) round((float) $quote->total * 100));
        $depositCents = $quote->deposit_target_cents;
        $paidByType = ['deposit' => 0, 'balance' => 0];

        foreach ($quote->payments as $payment) {
            if ($payment->status !== 'paid') {
                continue;
            }

            $refundedCents = $payment->refunds
                ->whereIn('status', ['pending', 'succeeded'])
                ->sum('amount_cents');
            if (array_key_exists($payment->payment_type, $paidByType)) {
                $paidByType[$payment->payment_type] += max(0, $payment->amount_cents - $refundedCents);
            }
        }

        $depositDueCents = max(0, $depositCents - $paidByType['deposit']);
        $balanceDueCents = max(0, $totalCents - array_sum($paidByType));

        return [
            'total_cents' => $totalCents,
            'deposit_target_cents' => $depositCents,
            'deposit_paid_cents' => $paidByType['deposit'],
            'deposit_due_cents' => $depositDueCents,
            'balance_due_cents' => $balanceDueCents,
            'paid_cents' => array_sum($paidByType),
        ];
    }

    private function assertServicePaymentsReady(): void
    {
        abort_unless(
            $this->modules->enabled('quotes')
                && $this->modules->enabled('payments')
                && $this->modules->enabled('invoicing')
                && $this->credentials->servicePaymentsEnabled(),
            404,
            'Los cobros de servicios no están habilitados.',
        );
    }

    private function requireSecretKey(): string
    {
        $secret = $this->credentials->secretKey();
        abort_unless(filled($secret), 503, 'Stripe secret key is not configured.');

        return $secret;
    }

    private function authorizeRefundAdministration(): void
    {
        $user = auth('web')->user();
        abort_unless(
            $user instanceof User
                && ($user->hasRole(['admin', 'super_admin']) || $user->email === config('platform.developer_email')),
            403,
        );
    }

    private function refundStatus(?string $status): string
    {
        return in_array($status, ['succeeded', 'failed', 'canceled'], true)
            ? $status
            : 'pending';
    }
}
