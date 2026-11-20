<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class AdminLocalization
{
    /**
     * Admin panel uses ONLY English and Spanish.
     * Stored in session('admin_locale') — completely separate from frontend.
     */
    public function handle(Request $request, Closure $next)
    {
        $availableLocales = ['en', 'es'];

        // Read admin locale from session (separate from frontend 'locale')
        $locale = Session::get('admin_locale');

        // Default to English; Spanish remains available as the secondary language.
        if (!$locale || !in_array($locale, $availableLocales)) {
            $locale = 'en';
            Session::put('admin_locale', $locale);
        }

        // Set application locale for admin
        App::setLocale($locale);

        return $next($request);
    }
}
