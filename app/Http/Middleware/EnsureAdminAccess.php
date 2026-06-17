<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null) {
            return redirect()->guest(route('admin.login'));
        }

        $allowedEmails = array_map(
            static fn (string $email): string => strtolower(trim($email)),
            config('admin.allowed_emails', [])
        );

        if (! in_array(strtolower(trim($user->email)), $allowedEmails, true)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('admin.login')
                ->with('error', 'このアカウントでは管理画面にアクセスできません。');
        }

        return $next($request);
    }
}
