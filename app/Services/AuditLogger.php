<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\QuotePayment;
use App\Models\QuotePaymentRefund;
use App\Models\User;

class AuditLogger
{
    public function recordInventoryCostingEnabled(): void
    {
        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'inventory.costing_enabled',
            'subject_type' => InventoryValuationService::class,
            'subject_id' => 'weighted-average',
            'old_values' => ['enabled' => false],
            'new_values' => ['enabled' => true],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordModuleChanges(array $oldModules, array $newModules): void
    {
        if ($oldModules === [] || $newModules === []) {
            return;
        }

        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'platform.modules_changed',
            'subject_type' => PlatformModules::class,
            'subject_id' => 'module-settings',
            'old_values' => ['modules' => $oldModules],
            'new_values' => ['modules' => $newModules],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordRoleChange(User $user, array $oldRoles, array $newRoles): void
    {
        sort($oldRoles);
        sort($newRoles);

        if ($oldRoles === $newRoles) {
            return;
        }

        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'user.roles_changed',
            'subject_type' => User::class,
            'subject_id' => (string) $user->getKey(),
            'old_values' => ['roles' => $oldRoles],
            'new_values' => ['roles' => $newRoles],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordStripeConfigurationChange(array $oldStatus, array $newStatus, array $updatedCredentials): void
    {
        if ($oldStatus === $newStatus && $updatedCredentials === []) {
            return;
        }

        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'platform.stripe_configuration_changed',
            'subject_type' => StripeCredentials::class,
            'subject_id' => 'stripe',
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => $newStatus,
                'updated_credentials' => $updatedCredentials,
            ],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordFiscalProfileChange(array $oldProfile, array $newProfile): void
    {
        $changedFields = [];
        foreach ($oldProfile as $field => $oldValue) {
            if ($oldValue !== ($newProfile[$field] ?? null)) {
                $changedFields[] = $field;
            }
        }

        if ($changedFields === []) {
            return;
        }

        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'platform.fiscal_profile_changed',
            'subject_type' => FiscalProfile::class,
            'subject_id' => 'business-profile',
            'old_values' => ['changed_fields' => $changedFields],
            'new_values' => ['changed_fields' => $changedFields],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordQuotePaymentCheckoutCreated(QuotePayment $payment): void
    {
        $request = app()->bound('request') ? request() : null;
        $customer = auth('customer')->user();

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'actor_label' => $customer instanceof \App\Models\Customer ? 'Cliente: ' . $customer->email : null,
            'event' => 'quote_payment.checkout_created',
            'subject_type' => QuotePayment::class,
            'subject_id' => (string) $payment->getKey(),
            'new_values' => [
                'quote_id' => $payment->quote_id,
                'payment_type' => $payment->payment_type,
                'amount_cents' => $payment->amount_cents,
                'currency' => $payment->currency,
            ],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordQuotePaymentReceived(QuotePayment $payment): void
    {
        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'quote_payment.received',
            'subject_type' => QuotePayment::class,
            'subject_id' => (string) $payment->getKey(),
            'new_values' => [
                'quote_id' => $payment->quote_id,
                'payment_type' => $payment->payment_type,
                'amount_cents' => $payment->amount_cents,
                'currency' => $payment->currency,
            ],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordQuotePaymentStatusChanged(QuotePayment $payment, string $oldStatus): void
    {
        if ($oldStatus === $payment->status) {
            return;
        }

        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'quote_payment.status_changed',
            'subject_type' => QuotePayment::class,
            'subject_id' => (string) $payment->getKey(),
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => $payment->status,
                'quote_id' => $payment->quote_id,
                'amount_cents' => $payment->amount_cents,
            ],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordQuoteRefundRequested(QuotePaymentRefund $refund): void
    {
        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'quote_payment.refund_requested',
            'subject_type' => QuotePaymentRefund::class,
            'subject_id' => (string) $refund->getKey(),
            'new_values' => [
                'quote_payment_id' => $refund->quote_payment_id,
                'amount_cents' => $refund->amount_cents,
            ],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }

    public function recordQuoteRefundStatusChanged(QuotePaymentRefund $refund, string $oldStatus): void
    {
        if ($oldStatus === $refund->status) {
            return;
        }

        $request = app()->bound('request') ? request() : null;

        AuditLog::create([
            'actor_id' => auth('web')->id(),
            'event' => 'quote_payment.refund_status_changed',
            'subject_type' => QuotePaymentRefund::class,
            'subject_id' => (string) $refund->getKey(),
            'old_values' => ['status' => $oldStatus],
            'new_values' => [
                'status' => $refund->status,
                'quote_payment_id' => $refund->quote_payment_id,
                'amount_cents' => $refund->amount_cents,
            ],
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent() ? mb_substr($request->userAgent(), 0, 1000) : null,
            'created_at' => now(),
        ]);
    }
}
