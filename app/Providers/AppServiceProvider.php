<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use App\Models\ContentBlock;
use App\Models\Quote;
use App\Observers\ContentBlockObserver;
use App\Observers\QuoteObserver;
use App\Observers\AuditObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Only load the libSQL driver when Turso is the configured connection.
        // The upstream provider is excluded from discovery because it replaces
        // Laravel's global PDO factory unconditionally.
        if (config('database.default') === 'libsql') {
            $this->app->register(TursoServiceProvider::class);
        }

        // Register AI Assistant Service as singleton
        $this->app->singleton(\App\Services\AiAssistantService::class, function () {
            return new \App\Services\AiAssistantService();
        });

        // Register Cart Service
        $this->app->singleton(\App\Services\CartService::class, function () {
            return new \App\Services\CartService();
        });

        // Register Stripe Service
        $this->app->singleton(\App\Services\StripeService::class, function () {
            return new \App\Services\StripeService();
        });
    }

    public function boot(): void
    {
        // Use simple pagination (works without JS)
        Paginator::useBootstrapFive();

        // Register Model Observers
        Quote::observe(QuoteObserver::class);
        ContentBlock::observe(ContentBlockObserver::class);
        foreach ([
            \App\Models\User::class,
            \App\Models\Quote::class,
            \App\Models\MapsCalculator\Project::class,
            \App\Models\MapsCalculator\WorkOrder::class,
            \App\Models\MapsCalculator\WorkLog::class,
            \App\Models\InventoryItem::class,
            \App\Models\InventoryLocation::class,
            \App\Models\InventoryMovement::class,
            \App\Models\InventoryReservation::class,
            \App\Models\InventorySupplier::class,
            \App\Models\InventoryPurchaseOrder::class,
            \App\Models\InventoryPurchaseOrderItem::class,
            \App\Models\InventoryReceipt::class,
            \App\Models\InventoryReceiptItem::class,
            \App\Models\InventoryValuationBalance::class,
            \App\Models\Invoice::class,
            \App\Models\AccountingAccount::class,
            \App\Models\JournalEntry::class,
            \App\Models\Order::class,
        ] as $model) {
            $model::observe(AuditObserver::class);
        }

        // Policies del módulo MapsCalculator. Los modelos viven en
        // App\Models\MapsCalculator, así que la autodetección de Laravel
        // no los enlaza con App\Policies y hay que registrarlos a mano.
        $this->registerMapscalculatorPolicies();

        // Locale is handled by Localization middleware (frontend)
        // and AdminLocalization middleware (admin panel)
        // No ViewComposer needed here.

        // Monkey-patch EncryptCookies when openssl is unavailable (Windows dev)
        if (!function_exists('openssl_cipher_iv_length')) {
            $this->patchEncryptCookies();
        }
    }

    /**
     * Enlaza cada modelo del módulo con su policy (autorización por rol:
     * admin / comercial / técnico).
     */
    protected function registerMapscalculatorPolicies(): void
    {
        $policies = [
            \App\Models\MapsCalculator\Project::class => \App\Policies\MapsCalculator\ProjectPolicy::class,
            \App\Models\MapsCalculator\Measurement::class => \App\Policies\MapsCalculator\MeasurementPolicy::class,
            \App\Models\MapsCalculator\Client::class => \App\Policies\MapsCalculator\ClientPolicy::class,
            \App\Models\MapsCalculator\WorkOrder::class => \App\Policies\MapsCalculator\WorkOrderPolicy::class,
            \App\Models\Quote::class => \App\Policies\MapsCalculator\QuotePolicy::class,
        ];

        foreach ($policies as $model => $policy) {
            \Illuminate\Support\Facades\Gate::policy($model, $policy);
        }
    }

    /**
     * Replace EncryptCookies with a no-op when openssl is unavailable.
     * This allows the site to run on Windows PHP built-in server.
     */
    protected function patchEncryptCookies(): void
    {
        $this->app->bind(\Illuminate\Cookie\Middleware\EncryptCookies::class, function () {
            return new class {
                public function handle($request, \Closure $next)
                {
                    return $next($request);
                }
            };
        });

        // Also patch the session encryption
        config(['session.encrypt' => false]);
    }
}
