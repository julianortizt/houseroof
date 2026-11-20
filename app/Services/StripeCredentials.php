<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StripeCredentials
{
    private const SECRET_SETTING_KEYS = [
        'publishable_key' => 'stripe_publishable_key_encrypted',
        'secret_key' => 'stripe_secret_key_encrypted',
        'webhook_secret' => 'stripe_webhook_secret_encrypted',
    ];

    public function publishableKey(): ?string
    {
        return $this->getCredential('publishable_key', config('services.stripe.key'));
    }

    public function secretKey(): ?string
    {
        return $this->getCredential('secret_key', config('services.stripe.secret'));
    }

    public function webhookSecret(): ?string
    {
        return $this->getCredential('webhook_secret', config('services.stripe.webhook_secret'));
    }

    public function checkoutEnabled(): bool
    {
        return (bool) Setting::safeGet('stripe_checkout_enabled', config('services.stripe.checkout_enabled', false))
            && (bool) Setting::safeGet('stripe_product_fiscal_review_confirmed', false);
    }

    public function productCheckoutReady(): bool
    {
        return app(PlatformModules::class)->enabled('products')
            && app(PlatformModules::class)->enabled('payments')
            && $this->checkoutEnabled()
            && ! in_array(false, $this->configured(), true);
    }

    public function servicePaymentsEnabled(): bool
    {
        return (bool) Setting::safeGet('stripe_service_payments_enabled', false)
            && (bool) Setting::safeGet('stripe_service_payments_fiscal_review_confirmed', false);
    }

    public function configured(): array
    {
        return [
            'publishable_key' => filled($this->publishableKey()),
            'secret_key' => filled($this->secretKey()),
            'webhook_secret' => filled($this->webhookSecret()),
        ];
    }

    public function operationalStatus(): array
    {
        $configured = $this->configured();
        $moduleReady = app(PlatformModules::class)->enabled('products')
            && app(PlatformModules::class)->enabled('payments');
        $productCheckoutEnabled = $this->checkoutEnabled();
        $servicePaymentsEnabled = $this->servicePaymentsEnabled();
        $fiscalReady = app(FiscalProfile::class)->canIssue();

        $enabledFlows = [];
        if ($productCheckoutEnabled) {
            $enabledFlows[] = 'product_checkout';
        }
        if ($servicePaymentsEnabled) {
            $enabledFlows[] = 'service_payments';
        }

        $missing = collect($configured)
            ->filter(fn (bool $ready): bool => ! $ready)
            ->keys()
            ->all();

        $ready = $moduleReady
            && $configured['publishable_key']
            && $configured['secret_key']
            && $configured['webhook_secret']
            && ($productCheckoutEnabled || $servicePaymentsEnabled)
            && $fiscalReady;

        return [
            'ready' => $ready,
            'configured' => ! empty(array_filter($configured, fn (bool $value): bool => $value)),
            'module_ready' => $moduleReady,
            'fiscal_ready' => $fiscalReady,
            'checkout_enabled' => $productCheckoutEnabled,
            'service_payments_enabled' => $servicePaymentsEnabled,
            'enabled_flows' => $enabledFlows,
            'missing_keys' => $missing,
        ];
    }

    public function save(array $data): void
    {
        $developerEmail = config('platform.developer_email');
        abort_unless(
            filled($developerEmail) && auth('web')->user()?->email === $developerEmail,
            403,
        );

        $validated = Validator::make($data, [
            'publishable_key' => ['nullable', 'string', 'max:255', 'regex:/^pk_live_[A-Za-z0-9_-]+$/'],
            'secret_key' => ['nullable', 'string', 'max:255', 'regex:/^sk_live_[A-Za-z0-9_-]+$/'],
            'webhook_secret' => ['nullable', 'string', 'max:255', 'regex:/^whsec_[A-Za-z0-9_-]+$/'],
            'enable_product_checkout' => ['required', 'boolean'],
            'product_checkout_fiscal_review_confirmed' => ['nullable', 'boolean'],
            'enable_service_payments' => ['nullable', 'boolean'],
            'service_payment_fiscal_review_confirmed' => ['nullable', 'boolean'],
        ])->validate();

        $enableProductCheckout = (bool) $validated['enable_product_checkout'];
        $productCheckoutFiscalReviewConfirmed = (bool) ($validated['product_checkout_fiscal_review_confirmed'] ?? false);
        $enableServicePayments = (bool) ($validated['enable_service_payments'] ?? false);
        $fiscalReviewConfirmed = (bool) ($validated['service_payment_fiscal_review_confirmed'] ?? false);

        if ($enableProductCheckout && ! $productCheckoutFiscalReviewConfirmed) {
            throw ValidationException::withMessages([
                'product_checkout_fiscal_review_confirmed' => 'Confirma la revisión fiscal antes de habilitar el checkout de productos.',
            ]);
        }

        if ($enableProductCheckout || $enableServicePayments) {
            $environmentKeys = [
                'publishable_key' => config('services.stripe.key'),
                'secret_key' => config('services.stripe.secret'),
                'webhook_secret' => config('services.stripe.webhook_secret'),
            ];
            $missing = [];

            foreach (self::SECRET_SETTING_KEYS as $field => $settingKey) {
                if (blank($validated[$field] ?? '') && blank($this->getCredential($field, $environmentKeys[$field]))) {
                    $missing[] = $field;
                }
            }

            if ($missing !== []) {
                throw ValidationException::withMessages([
                    'enable_product_checkout' => 'Configura la clave pública, la clave privada y el secreto de webhook antes de habilitar el checkout.',
                ]);
            }

            if (! app(PlatformModules::class)->enabled('products') || ! app(PlatformModules::class)->enabled('payments')) {
                if ($enableProductCheckout) {
                    throw ValidationException::withMessages([
                        'enable_product_checkout' => 'Activa los módulos de productos y pagos desde el Super Panel antes de habilitar el checkout.',
                    ]);
                }
            }

            if ($enableServicePayments && (! app(PlatformModules::class)->enabled('quotes')
                || ! app(PlatformModules::class)->enabled('payments')
                || ! app(PlatformModules::class)->enabled('invoicing'))) {
                throw ValidationException::withMessages([
                    'enable_service_payments' => 'Activa los módulos de cotizaciones, facturación y pagos desde el Super Panel antes de habilitar cobros de servicios.',
                ]);
            }

            if ($enableServicePayments && ! $fiscalReviewConfirmed) {
                throw ValidationException::withMessages([
                    'service_payment_fiscal_review_confirmed' => 'Confirma la revisión fiscal antes de habilitar cobros reales de servicios.',
                ]);
            }
        }

        $oldStatus = $this->configured() + [
            'checkout_enabled' => $this->checkoutEnabled(),
            'service_payments_enabled' => $this->servicePaymentsEnabled(),
        ];
        $updatedCredentials = [];
        foreach (array_keys(self::SECRET_SETTING_KEYS) as $field) {
            if (filled($validated[$field] ?? null)) {
                $updatedCredentials[] = $field;
            }
        }

        DB::transaction(function () use ($validated, $oldStatus, $updatedCredentials, $enableProductCheckout, $productCheckoutFiscalReviewConfirmed, $enableServicePayments, $fiscalReviewConfirmed): void {
            foreach (self::SECRET_SETTING_KEYS as $field => $settingKey) {
                $value = trim((string) ($validated[$field] ?? ''));
                if ($value !== '') {
                    Setting::set($settingKey, Crypt::encryptString($value), 'encrypted', 'payments');
                }
            }

            Setting::set(
                'stripe_checkout_enabled',
                $validated['enable_product_checkout'] ? '1' : '0',
                'boolean',
                'payments',
            );
            Setting::set(
                'stripe_product_fiscal_review_confirmed',
                $productCheckoutFiscalReviewConfirmed ? '1' : '0',
                'boolean',
                'payments',
            );
            Setting::set(
                'stripe_service_payments_enabled',
                $enableServicePayments ? '1' : '0',
                'boolean',
                'payments',
            );
            Setting::set(
                'stripe_service_payments_fiscal_review_confirmed',
                $fiscalReviewConfirmed ? '1' : '0',
                'boolean',
                'payments',
            );

            $newStatus = $this->configured() + [
                'checkout_enabled' => $this->checkoutEnabled(),
                'service_payments_enabled' => $this->servicePaymentsEnabled(),
            ];
            app(AuditLogger::class)->recordStripeConfigurationChange($oldStatus, $newStatus, $updatedCredentials);
        });
    }

    private function getCredential(string $field, mixed $environmentValue): ?string
    {
        $encrypted = Setting::safeGet(self::SECRET_SETTING_KEYS[$field]);
        if (filled($encrypted)) {
            try {
                return Crypt::decryptString($encrypted);
            } catch (\Throwable) {
                return null;
            }
        }

        return filled($environmentValue) ? (string) $environmentValue : null;
    }
}
