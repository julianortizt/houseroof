<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();

        if (! $plain) {
            return response()->json(['message' => 'Token de autenticación requerido.'], 401);
        }

        $apiToken = ApiToken::where('token', hash('sha256', $plain))->first();

        if (! $apiToken || ($apiToken->expires_at && $apiToken->expires_at->isPast())) {
            return response()->json(['message' => 'Sesión inválida o expirada.'], 401);
        }

        $user = $apiToken->tokenable;

        if (! $user) {
            return response()->json(['message' => 'Sesión inválida.'], 401);
        }

        Auth::setUser($user);
        $request->attributes->set('api_token', $apiToken);

        return $next($request);
    }
}
