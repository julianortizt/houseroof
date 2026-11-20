<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Switch locale — detects frontend vs admin from referer or URL.
     * Frontend: session('locale') — 7 languages
     * Admin: session('admin_locale') — EN/ES only
     */
    public function switch(string $locale): RedirectResponse
    {
        $referer = request()->headers->get('referer', '/');
        $isAdmin = str_contains($referer, '/admin') || request()->is('admin*');

        if ($isAdmin) {
            // Admin: only EN/ES
            $availableLocales = ['en', 'es'];
            if (in_array($locale, $availableLocales)) {
                Session::put('admin_locale', $locale);
                app()->setLocale($locale);
            }
        } else {
            // Frontend: all 7 languages
            $availableLocales = ['en', 'es', 'de', 'it', 'fr', 'pt', 'nl'];
            if (in_array($locale, $availableLocales)) {
                Session::put('locale', $locale);
                app()->setLocale($locale);
            }
        }

        // Redirect back
        if ($isAdmin) {
            return redirect()->back();
        }

        return redirect()->back();
    }

    /**
     * Explicit admin locale switch route
     */
    public function switchAdmin(string $locale): RedirectResponse
    {
        $availableLocales = ['en', 'es'];

        if (in_array($locale, $availableLocales)) {
            Session::put('admin_locale', $locale);
            app()->setLocale($locale);
        }

        return redirect()->back();
    }
}
