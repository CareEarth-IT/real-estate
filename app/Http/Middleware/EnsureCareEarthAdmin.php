<?php

namespace App\Http\Middleware;

use App\Support\Role;
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
            return redirect()->route('login', [
                'redirect' => $request->fullUrl(),
            ]);
        }

        if (! CareEarthAuth::isAdmin($request)) {
            abort(403, 'ユーザー管理は管理者のみ利用できます。');
        }

        return $next($request);
    }
}
