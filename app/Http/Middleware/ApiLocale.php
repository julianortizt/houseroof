<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiLocale
{
    protected array $supported = ['es', 'en', 'de', 'it', 'fr', 'pt', 'nl'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang')
            ?? $request->header('X-Locale')
            ?? $request->header('Accept-Language');

        if ($locale) {
            $locale = strtolower(substr((string) $locale, 0, 2));
        }

        if (! in_array($locale, $this->supported, true)) {
            $locale = 'es';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
