<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replaces EncryptCookies - handles cookies without encryption on Windows dev.
 */
class PatchEncryptCookies
{
    public function handle(Request $request, Closure $next): Response
    {
        // Simply pass through without any cookie encryption/decryption
        return $next($request);
    }
}
