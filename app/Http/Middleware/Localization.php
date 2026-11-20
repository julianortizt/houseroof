<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class Localization
{
    /**
     * Frontend uses all 7 languages.
     * Stored in session('locale') — completely separate from admin.
     * Skips admin routes (handled by AdminLocalization middleware).
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip admin routes — admin uses AdminLocalization middleware
        if ($request->is('admin*')) {
            return $next($request);
        }

        // Available frontend locales
        $availableLocales = ['en', 'es', 'de', 'it', 'fr', 'pt', 'nl'];

        // Read frontend locale from session
        $locale = Session::get('locale');

        // Explicit ?lang= override (deterministic language URLs for crawlers / hreflang)
        $langParam = $request->query('lang');
        if ($langParam && in_array($langParam, $availableLocales)) {
            $locale = $langParam;
            Session::put('locale', $locale);
        }

        // Default to the application's configured primary language, with English as the default.
        $defaultLocale = config('app.locale', 'en');
        if (!$locale || !in_array($locale, $availableLocales)) {
            $locale = $defaultLocale;
            Session::put('locale', $locale);
        }

        // Set application locale
        App::setLocale($locale);

        // Share locale with all views
        \Illuminate\Support\Facades\View::share('currentLocale', $locale);

        $response = $next($request);

        // Prevent proxy/host-level page caches (e.g. Hostinger Cache Manager)
        // from serving a language-specific page to the wrong locale. The locale
        // lives in the session cookie, not the URL, so the page must not be cached.
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');

        return $response;
    }
}
