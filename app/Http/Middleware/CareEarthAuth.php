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
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('login'));
        }

        return $next($request);
    }

    /**
     * 後方互換: ログイン済みか確認するのみ（自動ログインは行わない）。
     */
    public static function ensureSession(Request $request): bool
    {
        return self::isLoggedIn($request);
    }

    public static function isLoggedIn(Request $request): bool
    {
        $session = $request->session();

        if (! $session->get('authenticated') || ! $session->get('user_id')) {
            return false;
        }

        $lifetime = (int) config('careearth.session_lifetime', 7200);
        $lastActivity = (int) ($session->get('last_activity') ?: $session->get('login_time') ?: 0);

        // 放置（無操作）が lifetime を超えた場合のみ再ログインを要求
        if ($lastActivity > 0 && (time() - $lastActivity) > $lifetime) {
            self::logout($request);

            return false;
        }

        $user = CareEarthUser::query()->find($session->get('user_id'));

        if ($user === null) {
            self::logout($request);

            return false;
        }

        // 操作があるたびに最終アクティビティを更新（動作中は切れない）
        $session->put([
            'email' => $user->email,
            'name' => $user->name,
            'role' => Role::normalize($user->role),
            'last_activity' => time(),
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

        $now = time();
        $request->session()->regenerate();
        $request->session()->put([
            'authenticated' => true,
            'user_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => Role::normalize($user->role),
            'login_time' => $now,
            'last_activity' => $now,
        ]);

        return true;
    }

    public static function logout(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public static function homeRouteForRole(?string $role): string
    {
        return 'home';
    }

    public static function currentRole(Request $request): ?string
    {
        if (! self::isLoggedIn($request)) {
            return null;
        }

        $role = $request->session()->get('role');

        return is_string($role) ? Role::normalize($role) : null;
    }

    public static function canEdit(Request $request): bool
    {
        $role = self::currentRole($request);

        return $role !== null && Role::canEdit($role);
    }

    public static function canManageUsers(Request $request): bool
    {
        $role = self::currentRole($request);

        return $role !== null && Role::canManageUsers($role);
    }

    public static function canAccessPropertyMaster(Request $request): bool
    {
        $role = self::currentRole($request);

        return $role !== null && Role::canAccessPropertyMaster($role);
    }

    /** 管理者ロールか（開発用） */
    public static function isAdmin(Request $request): bool
    {
        $role = self::currentRole($request);

        return $role !== null && Role::isAdmin($role);
    }

    public static function currentUserId(Request $request): ?int
    {
        if (! self::isLoggedIn($request)) {
            return null;
        }

        return (int) $request->session()->get('user_id');
    }
}
