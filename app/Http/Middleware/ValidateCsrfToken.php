<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;
use Symfony\Component\HttpFoundation\Cookie;

/**
 * 社員サイト（path=/）の XSRF-TOKEN と衝突しない Cookie 名を使う。
 */
class ValidateCsrfToken extends Middleware
{
    public const XSRF_COOKIE = 'real_estate_portal_xsrf';

    protected function newCookie($request, $config)
    {
        return new Cookie(
            self::XSRF_COOKIE,
            $request->session()->token(),
            $this->availableAt(60 * $config['lifetime']),
            $config['path'],
            $config['domain'],
            $config['secure'],
            false,
            false,
            $config['same_site'] ?? null,
            $config['partitioned'] ?? false
        );
    }

    public static function serialized()
    {
        return EncryptCookies::serialized(self::XSRF_COOKIE);
    }
}
