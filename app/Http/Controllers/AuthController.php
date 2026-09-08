<?php

namespace App\Http\Controllers;

use App\Http\Middleware\CareEarthAuth;
use App\Services\EmployeePortalSsoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

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

        return $this->leaveLoginScreen();
    }

    public function login(): Response
    {
        // ローカルメール＋パスワードログインは停止
        return response()->view('auth.unavailable', [
            'portalLoginUrl' => $this->ssoService->portalLoginUrl(),
        ], 403);
    }

    /**
     * ログイン画面から社員ポータルへ誘導。
     */
    public function redirectToPortal(): RedirectResponse|View
    {
        $url = $this->ssoService->portalLoginUrl();
        if ($url === null || ! $this->ssoService->isSsoEnabled()) {
            return $this->leaveLoginScreen();
        }

        return redirect()->away($url);
    }

    /**
     * 社員ポータル handoff 後のブラウザ callback。ワンタイムコードでセッション発行。
     */
    public function portalCallback(Request $request): RedirectResponse|View
    {
        $code = (string) $request->query('code', '');
        if ($code === '') {
            return $this->leaveLoginScreen();
        }

        try {
            $user = $this->ssoService->consumeHandoffCode($code);
        } catch (RuntimeException) {
            return $this->leaveLoginScreen();
        }

        CareEarthAuth::loginAsUser($request, $user);

        $home = route(CareEarthAuth::homeRouteForRole(CareEarthAuth::currentRole($request)));

        return redirect()->intended($home);
    }

    public function logout(Request $request): RedirectResponse|View
    {
        CareEarthAuth::logout($request);

        return $this->leaveLoginScreen();
    }

    private function leaveLoginScreen(): RedirectResponse|View
    {
        $url = $this->ssoService->portalLoginUrl();
        if ($url !== null) {
            return redirect()->away($url);
        }

        return view('auth.unavailable', [
            'portalLoginUrl' => null,
        ]);
    }
}
