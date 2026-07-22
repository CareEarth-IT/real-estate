<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CareEarthAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (CareEarthAuth::isLoggedIn($request)) {
            return redirect()->route(
                CareEarthAuth::homeRouteForRole(CareEarthAuth::currentRole($request))
            );
        }

        return view('auth.login', [
            'redirect' => $request->query('redirect'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'password.required' => 'パスワードを入力してください。',
        ]);

        if (! CareEarthAuth::attemptLogin($request, $validated['email'], $validated['password'])) {
            return back()
                ->withInput($request->only('email', 'redirect'))
                ->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。']);
        }

        $home = route(CareEarthAuth::homeRouteForRole(CareEarthAuth::currentRole($request)));
        $redirect = $request->input('redirect');

        if (is_string($redirect) && $redirect !== '' && str_starts_with($redirect, '/')) {
            return redirect()->to($redirect);
        }

        return redirect()->intended($home);
    }

    public function logout(Request $request): RedirectResponse
    {
        CareEarthAuth::logout($request);

        return redirect()
            ->route('login')
            ->with('success', 'ログアウトしました。');
    }
}
