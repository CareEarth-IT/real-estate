<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CareEarthAuth;
use App\Services\EmployeePortalSsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(
        private readonly EmployeePortalSsoService $ssoService,
    ) {}

    public function showLogin(Request $request): View|RedirectResponse
    {
        if (CareEarthAuth::isLoggedIn($request)) {
            return redirect()->route(
                CareEarthAuth::homeRouteForRole(CareEarthAuth::currentRole($request))
            );
        }

        return view('auth.login', [
            'redirect' => $request->query('redirect'),
            'ssoEnabled' => $this->ssoService->isSsoEnabled(),
            'portalLoginUrl' => $this->ssoService->portalLoginUrl(),
            'localLoginAllowed' => $this->ssoService->isLocalLoginAllowed(),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        if (! $this->ssoService->isLocalLoginAllowed()) {
            return back()->withErrors([
                'email' => 'ローカルログインは無効です。社員ポータルからサインインしてください。',
            ]);
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'password.required' => 'パスワードを入力してください。',
        ]);

        if (! CareEarthAuth::attemptLogin($request, $validated['email'], $validated['password'])) {
            return redirect()
                ->route('login')
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

    /**
     * ログイン画面から社員ポータルへ誘導。
     */
    public function redirectToPortal(Request $request): RedirectResponse
    {
        $url = $this->ssoService->portalLoginUrl();
        if ($url === null || ! $this->ssoService->isSsoEnabled()) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => '社員ポータル連携が未設定、または無効です。']);
        }

        return redirect()->away($url);
    }

    /**
     * 社員ポータル handoff 後のブラウザ callback。ワンタイムコードでセッション発行。
     */
    public function portalCallback(Request $request): RedirectResponse
    {
        $code = (string) $request->query('code', '');
        if ($code === '') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'ログイン用コードがありません。']);
        }

        try {
            $user = $this->ssoService->consumeHandoffCode($code);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => $e->getMessage()]);
        }

        CareEarthAuth::loginAsUser($request, $user);

        $home = route(CareEarthAuth::homeRouteForRole(CareEarthAuth::currentRole($request)));

        return redirect()
            ->intended($home)
            ->with('success', '社員ポータル経由でログインしました。');
    }

    public function logout(Request $request): RedirectResponse
    {
        CareEarthAuth::logout($request);

        return redirect()
            ->route('login')
            ->with('success', 'ログアウトしました。');
    }
}
