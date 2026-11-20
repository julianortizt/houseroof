<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('customer');

        if (! $guard->check()) {
            return redirect()->guest(route('customer.login'));
        }

        $request->setUserResolver(fn () => $guard->user());

        return $next($request);
    }
}
