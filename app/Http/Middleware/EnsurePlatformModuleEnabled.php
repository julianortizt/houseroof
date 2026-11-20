<?php

namespace App\Http\Middleware;

use App\Services\PlatformModules;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        abort_unless(app(PlatformModules::class)->enabled($module), 404);

        return $next($request);
    }
}
