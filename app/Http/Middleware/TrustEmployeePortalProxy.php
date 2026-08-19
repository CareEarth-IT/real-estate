<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * employee.careearth.net/realestate-portal 経由アクセス時の URL / セッション Cookie 補正。
 */
class TrustEmployeePortalProxy
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-Employee-Portal') === '1') {
            $publicUrl = rtrim((string) env(
                'PORTAL_PUBLIC_URL',
                'https://employee.careearth.net/realestate-portal',
            ), '/');
            $publicPath = parse_url($publicUrl, PHP_URL_PATH) ?: '/realestate-portal';

            URL::forceRootUrl($publicUrl);
            config([
                'session.path' => $publicPath,
                'session.cookie' => env('SESSION_COOKIE', 'real_estate_portal_session'),
            ]);
        }

        return $next($request);
    }
}
