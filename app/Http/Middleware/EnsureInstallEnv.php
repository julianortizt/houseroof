<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

/**
 * Ensures a valid .env (with APP_KEY) exists before any web request is handled.
 *
 * This is required so that the visual web installer at /install can run even on
 * a completely fresh upload where no .env exists yet. Without an APP_KEY, the
 * EncryptCookies / session middleware would throw on the very first request.
 *
 * Runs as global middleware (before the web/route middleware stack).
 */
class EnsureInstallEnv
{
    public function handle(Request $request, Closure $next)
    {
        // If the application is already installed we don't touch anything.
        if (file_exists(base_path('.installed'))) {
            return $next($request);
        }

        $this->ensureEnvFile();

        return $next($request);
    }

    protected function ensureEnvFile(): void
    {
        $envPath = base_path('.env');

        // No .env yet: create it from the example template with a fresh key.
        if (!file_exists($envPath)) {
            $example = base_path('.env.example');

            if (file_exists($example)) {
                $contents = file_get_contents($example);
            } else {
                $contents = "APP_KEY=\n";
            }

            $key = $this->generateKey();
            $contents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $contents);

            if (!str_contains($contents, 'APP_KEY=')) {
                $contents .= "\nAPP_KEY=".$key."\n";
            }

            file_put_contents($envPath, $contents);
            config(['app.key' => $key]);

            return;
        }

        // .env exists but is missing a key: inject one.
        $contents = file_get_contents($envPath);
        if (!preg_match('/^APP_KEY=\S+/m', $contents)) {
            $key = $this->generateKey();
            $contents = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $contents);

            if (!str_contains($contents, 'APP_KEY=')) {
                $contents .= "\nAPP_KEY=".$key."\n";
            }

            file_put_contents($envPath, $contents);
            config(['app.key' => $key]);
        }
    }

    protected function generateKey(): string
    {
        return 'base64:'.base64_encode(random_bytes(32));
    }
}
