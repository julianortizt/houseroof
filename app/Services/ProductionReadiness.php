<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionReadiness
{
    /**
     * Evaluate whether the application is ready for production deployment or a
     * business-critical preview run.
     *
     * @return array{ready: bool, checks: array<int, array{name: string, passed: bool, message: string}>, errors: array<int, string>, warnings: array<int, string>}
     */
    public function check(): array
    {
        $checks = [];
        $errors = [];
        $warnings = [];

        $checks[] = $this->checkItem(
            'Application key configured',
            filled(config('app.key')),
            filled(config('app.key')) ? 'APP_KEY is set.' : 'Set APP_KEY before deploy.'
        );

        $checks[] = $this->checkItem(
            'Application environment',
            app()->environment(['production', 'local', 'testing']),
            'Current environment: ' . app()->environment()
        );

        $databaseReady = $this->databaseReady();
        $checks[] = $this->checkItem(
            'Database connectivity',
            $databaseReady,
            $databaseReady ? 'Database connection is reachable.' : 'Database is unreachable or not configured.'
        );

        if (! $databaseReady) {
            $errors[] = 'Database connection is unavailable. Fix DB_HOST, DB_DATABASE and credentials before deploying.';
        }

        $appUrl = rtrim((string) config('app.url'), '/');
        $checks[] = $this->checkItem(
            'Public URL configured',
            filled($appUrl) && str_contains($appUrl, 'http'),
            $appUrl !== '' ? 'URL configured: ' . $appUrl : 'Set APP_URL.'
        );

        $legalPagesReady = $this->legalPagesReady();
        $checks[] = $this->checkItem(
            'Legal pages exist',
            $legalPagesReady,
            $legalPagesReady ? 'Required legal pages are present.' : 'Missing one or more legal pages: privacy-policy, cookie-policy, terms-of-sale, legal-notice.'
        );

        if (! $legalPagesReady) {
            $errors[] = 'Required legal pages are missing in the database.';
        }

        $modules = app(PlatformModules::class);
        $moduleRegistryReady = $this->moduleRegistryReady($modules);
        $checks[] = $this->checkItem(
            'Module registry',
            $moduleRegistryReady,
            $moduleRegistryReady ? 'Module registry is valid.' : 'Module registry is disabled or invalid.'
        );

        if (! $moduleRegistryReady) {
            $errors[] = 'Platform module registry could not be read.';
        }

        if ($databaseReady && $modules->enabled('payments')) {
            $stripeReady = ! empty((new StripeCredentials())->secretKey())
                && ! empty((new StripeCredentials())->publishableKey())
                && ! empty((new StripeCredentials())->webhookSecret());

            $checks[] = $this->checkItem(
                'Stripe credentials',
                $stripeReady,
                $stripeReady ? 'Stripe credentials are configured.' : 'Enable payments only after completing Stripe live configuration.'
            );

            if (! $stripeReady) {
                $errors[] = 'Payments are enabled but Stripe credentials are incomplete.';
            }
        }

        if ($databaseReady && $modules->enabled('invoicing')) {
            $fiscalProfile = app(FiscalProfile::class)->get();
            $fiscalReady = filled($fiscalProfile['legal_name'] ?? null) && filled($fiscalProfile['tax_id'] ?? null);

            $checks[] = $this->checkItem(
                'Fiscal profile',
                $fiscalReady,
                $fiscalReady ? 'Fiscal profile is available for draft invoicing setup.' : 'Set the legal business profile before enabling fiscal workflows.'
            );

            if (! $fiscalReady) {
                $warnings[] = 'Invoicing is enabled without a complete legal profile.';
            }
        }

        $this->pushChecksForProduction($checks, $errors, $warnings, $modules, $databaseReady);

        return [
            'ready' => empty($errors),
            'checks' => $checks,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    protected function checkItem(string $name, bool $passed, string $message): array
    {
        return [
            'name' => $name,
            'passed' => $passed,
            'message' => $message,
        ];
    }

    protected function databaseReady(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function legalPagesReady(): bool
    {
        try {
            if (! Schema::hasTable('pages')) {
                return false;
            }

            $required = ['privacy-policy', 'terms-of-sale', 'legal-notice'];
            $cookieSlugs = ['cookie-policy', 'cookies'];

            foreach ($required as $slug) {
                if (! Page::query()->where('slug', $slug)->whereNull('deleted_at')->exists()) {
                    return false;
                }
            }

            if (! Page::query()->whereIn('slug', $cookieSlugs)->whereNull('deleted_at')->exists()) {
                return false;
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function moduleRegistryReady(PlatformModules $modules): bool
    {
        try {
            foreach (['products', 'inventory', 'quotes', 'work_orders', 'invoicing', 'accounting', 'payments', 'simulator', 'administration', 'customer_portal'] as $module) {
                $modules->enabled($module);
            }

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function pushChecksForProduction(array &$checks, array &$errors, array &$warnings, PlatformModules $modules, bool $databaseReady): void
    {
        if (app()->environment('production')) {
            $checks[] = $this->checkItem(
                'Production mode',
                config('app.env') === 'production' && ! config('app.debug'),
                'Production defaults are active.'
            );

            if (config('app.env') !== 'production' || config('app.debug')) {
                $errors[] = 'Production mode is not fully hardened.';
            }
        }

        if ($databaseReady && $modules->enabled('payments') && ! $modules->enabled('invoicing')) {
            $errors[] = 'Payments require invoicing to remain enabled.';
        }

        if (! empty(config('mail.mailer')) && config('mail.mailer') === 'smtp' && empty(config('mail.from.address'))) {
            $warnings[] = 'SMTP is configured but sender email is empty.';
        }
    }
}
