<?php

namespace App\Http\Middleware;

use App\Models\CareEarthUser;
use App\Support\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CareEarthAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! self::isLoggedIn($request)) {
            return redirect()->route('login', [
                'redirect' => $request->fullUrl(),
            ]);
        }

        return $next($request);
    }

    public static function isLoggedIn(Request $request): bool
    {
        $session = $request->session();

        if (! $session->get('authenticated') || ! $session->get('login_time') || ! $session->get('user_id')) {
            return false;
        }

        $lifetime = (int) config('careearth.session_lifetime', 3600);
        if (time() - (int) $session->get('login_time') > $lifetime) {
            self::logout($request);

            return false;
        }

        $user = CareEarthUser::query()->find($session->get('user_id'));

        if ($user === null) {
            self::logout($request);

            return false;
        }

        $session->put([
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return $session->get('authenticated') === true;
    }

    public static function attemptLogin(Request $request, string $email, string $password): bool
    {
        $user = CareEarthUser::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        if ($user === null || ! $user->verifyPassword($password)) {
            return false;
        }

        $request->session()->regenerate();
        $request->session()->put([
            'authenticated' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'login_time' => time(),
        ]);

        return true;
    }

    public static function logout(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public static function currentRole(Request $request): ?string
    {
        if (! self::isLoggedIn($request)) {
            return null;
        }

        return $request->session()->get('role');
    }

    public static function isKeiri(Request $request): bool
    {
        $role = self::currentRole($request);

        return $role === Role::KEIRI || $role === Role::ADMIN;
    }

    public static function isAdmin(Request $request): bool
    {
        return self::currentRole($request) === Role::ADMIN;
    }

    public static function currentUserId(Request $request): ?int
    {
        if (! self::isLoggedIn($request)) {
            return null;
        }

        return (int) $request->session()->get('user_id');
    }
}
