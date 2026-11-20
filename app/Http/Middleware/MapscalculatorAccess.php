<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Acceso al módulo MapsCalculator.
 *
 * Antes se llamaba MapscalculatorAdmin y exigía isMapscalculatorAdmin(), así
 * que un comercial o un técnico —los dos roles que el prompt define como
 * usuarios del módulo— recibían un 403 en todas las rutas. Aquí sólo se
 * comprueba que el usuario tenga alguno de los tres roles del módulo; los
 * permisos concretos de cada acción los decide su policy.
 */
class MapscalculatorAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! ($user->isMapscalculatorAdmin() || $user->isComercial() || $user->isTecnico())) {
            abort(403, 'Acceso denegado. Se requiere un rol del módulo MapsCalculator (admin, comercial o técnico).');
        }

        return $next($request);
    }
}
