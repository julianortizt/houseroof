<?php

namespace App\Providers;

use App\Services\JsonLdService;
use Illuminate\Support\ServiceProvider;

class JsonLdServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(JsonLdService::class, function ($app) {
            return new JsonLdService();
        });
    }

    public function boot(): void
    {
        // JSON-LD directives are handled directly in Blade views
    }
}
