<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Patches EncryptCookies to skip encryption when openssl is unavailable.
 * ONLY for local Windows development.
 */
class DisableCookieEncryption
{
    public function handle(Request $request, Closure $next): Response
    {
        // If openssl is not available, skip encryption entirely
        if (!function_exists('openssl_cipher_iv_length')) {
            // Decrypt cookies manually (they're base64 encoded by Laravel)
            $decrypted = [];
            foreach ($request->cookies->all() as $key => $value) {
                $decrypted[$key] = $value;
            }
            $request->cookies = new \Symfony\Component\HttpFoundation\InputBag($decrypted);
        }

        return $next($request);
    }
}
