<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministrativeUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $isAdministrator = $user !== null
            && ($user->hasRole(['super_admin', 'admin', 'editor', 'manager', 'site_admin'])
                || (filled(config('platform.developer_email')) && $user->email === config('platform.developer_email')));

        abort_unless($isAdministrator, 403);

        return $next($request);
    }
}
