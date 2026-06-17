<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check() && $this->isAllowedAdmin(Auth::user()->email)) {
            return redirect()->route('admin.applications.index');
        }

        return view('admin.auth.login');
    }

    public function redirectToGoogle(): SymfonyRedirectResponse
    {
        $driver = Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email']);

        $hostedDomain = config('admin.google_hosted_domain');
        if ($hostedDomain) {
            $driver->with(['hd' => $hostedDomain]);
        }

        return $driver->redirect();
    }

    public function handleGoogleCallback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'Googleログインに失敗しました。もう一度お試しください。');
        }

        $email = strtolower(trim((string) $googleUser->getEmail()));

        if (! $this->isAllowedAdmin($email)) {
            return redirect()
                ->route('admin.login')
                ->with('error', 'このGoogleアカウントでは管理画面にアクセスできません。許可されたアカウントでChromeにログインしているかご確認ください。');
        }

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $googleUser->getName() ?: Str::before($email, '@'),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(64)),
            ]
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('admin.applications.index'));
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    protected function isAllowedAdmin(?string $email): bool
    {
        if ($email === null || $email === '') {
            return false;
        }

        $allowedEmails = array_map(
            static fn (string $allowedEmail): string => strtolower(trim($allowedEmail)),
            config('admin.allowed_emails', [])
        );

        return in_array(strtolower(trim($email)), $allowedEmails, true);
    }
}
