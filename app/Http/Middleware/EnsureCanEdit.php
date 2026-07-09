<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanEdit
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! CareEarthAuth::ensureSession($request)) {
            abort(403, 'アクセス可能なユーザーが設定されていません。');
        }

        if (! CareEarthAuth::canEdit($request)) {
            abort(403, 'この操作を行う権限がありません。');
        }

        return $next($request);
    }
}
