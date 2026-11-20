<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockInstallerWhenInstalled
{
    /**
     * The installer is only reachable on a fresh, unconfigured install.
     *
     * This guard lives in middleware rather than in the controller constructor:
     * Artisan instantiates controllers while inspecting routes, so a constructor
     * guard made `php artisan route:list` abort with a 404.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // The completion page stays reachable so it can be shown right after installing.
        if (! $request->routeIs('install.complete') && file_exists(base_path('.installed'))) {
            abort(404);
        }

        return $next($request);
    }
}
