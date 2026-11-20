<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/simulator.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Ensures a valid .env (with APP_KEY) exists so the visual installer
        // at /install can run on a fresh upload before configuration.
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->append(\App\Http\Middleware\EnsureInstallEnv::class);

        $middleware->web(append: [
            \App\Http\Middleware\Localization::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhook/stripe',
        ]);

        // Keep the admin panel reachable while the site is in maintenance mode
        // so administrators can deactivate it. Livewire (used by Filament
        // forms/buttons) posts to /livewire/*, so it must also be excluded
        // or the deactivate action would be blocked with a 503.
        $middleware->preventRequestsDuringMaintenance([
            'admin',
            'admin/*',
            'super-panel',
            'super-panel/*',
            'livewire',
            'livewire/*',
        ]);

        // Aliases for the mobile app API (routes/api.php).
        $middleware->alias([
            'api.auth' => \App\Http\Middleware\ApiAuth::class,
            'api.locale' => \App\Http\Middleware\ApiLocale::class,
            'module.enabled' => \App\Http\Middleware\EnsurePlatformModuleEnabled::class,
            'admin.user' => \App\Http\Middleware\EnsureAdministrativeUser::class,
            'customer.auth' => \App\Http\Middleware\EnsureCustomerAuthenticated::class,
            'customer.verified' => \App\Http\Middleware\EnsureCustomerEmailVerified::class,
            // El módulo lo usan admin, comercial y técnico (los permisos finos
            // los aplican las policies del módulo).
            'mapscalculator.access' => \App\Http\Middleware\MapscalculatorAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
