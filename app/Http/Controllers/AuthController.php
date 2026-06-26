<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CareEarthAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (CareEarthAuth::isLoggedIn($request)) {
            return redirect()->route('properties.index');
        }

        return view('auth.login', [
            'redirect' => $this->safeRedirectUrl($request->query('redirect', '')),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        if (CareEarthAuth::isLoggedIn($request)) {
            return redirect()->route('properties.index');
        }

        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! CareEarthAuth::attemptLogin(
            $request,
            $request->input('email', ''),
            $request->input('password', ''),
        )) {
            usleep(500000);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。']);
        }

        return redirect()->to($this->safeRedirectUrl($request->input('redirect', '')));
    }

    public function logout(Request $request): RedirectResponse
    {
        CareEarthAuth::logout($request);

        return redirect()->route('login');
    }

    private function safeRedirectUrl(string $url): string
    {
        $fallback = route('properties.index');

        if ($url === '') {
            return $fallback;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $appUrl.$url;
        }

        if (str_starts_with($url, $appUrl.'/') || $url === $appUrl) {
            return $url;
        }

        return $fallback;
    }
}
