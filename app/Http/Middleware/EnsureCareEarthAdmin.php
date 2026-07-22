<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCareEarthAdmin
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! CareEarthAuth::isLoggedIn($request)) {
            return redirect()->guest(route('login'));
        }

        if (! CareEarthAuth::canManageUsers($request)) {
            abort(403, 'このページは部長または管理者のみ利用できます。');
        }

        return $next($request);
    }
}
