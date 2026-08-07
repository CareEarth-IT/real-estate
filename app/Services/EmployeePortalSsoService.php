<?php

namespace App\Services;

use App\Models\CareEarthUser;
use App\Support\Role;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class EmployeePortalSsoService
{
    private const HANDOFF_CACHE_PREFIX = 'employee_portal_sso_handoff:';

    public function __construct(
        private readonly EmployeePortalDirectoryClient $directoryClient,
        private readonly UserService $userService,
    ) {}

    public function isSsoEnabled(): bool
    {
        return (bool) config('employee-portal.sso_enabled');
    }

    public function isLocalLoginAllowed(): bool
    {
        return (bool) config('employee-portal.local_login_fallback_enabled');
    }

    public function portalLoginUrl(): ?string
    {
        $url = trim((string) config('employee-portal.login_url'));

        return $url !== '' ? $url : null;
    }

    /**
     * サーバ間 handoff を受け付け、ブラウザ用ワンタイムコード付き callback URL を返す。
     *
     * @param  array{email?: string, employee_id?: string, name?: string, department?: string, nonce?: string}  $payload
     */
    public function createHandoff(Request $request, array $payload): string
    {
        if (! $this->isSsoEnabled()) {
            throw new RuntimeException('社員ポータル SSO は無効です。');
        }

        $this->assertInternalAuth($request);

        $email = strtolower(trim((string) ($payload['email'] ?? '')));
        $employeeId = trim((string) ($payload['employee_id'] ?? ''));
        $name = trim((string) ($payload['name'] ?? ''));

        if ($email === '' && $employeeId === '') {
            throw new RuntimeException('email または employee_id が必要です。');
        }

        if ($this->directoryClient->isConfigured()) {
            $employee = $this->directoryClient->findActiveEmployee(
                $email !== '' ? $email : null,
                $employeeId !== '' ? $employeeId : null,
            );

            if ($employee === null) {
                throw new RuntimeException('在籍中の社員情報が見つかりません。');
            }

            $email = strtolower(trim((string) ($employee['email'] ?? $email)));
            $employeeId = trim((string) ($employee['employee_id'] ?? $employeeId));
            if ($name === '') {
                $name = trim((string) ($employee['name'] ?? ''));
            }
            $employmentStatus = trim((string) ($employee['employment_status'] ?? config('employee-portal.default_status')));
        } else {
            $employmentStatus = (string) config('employee-portal.default_status');
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('有効なメールアドレスが必要です。');
        }

        if ($name === '') {
            $name = Str::before($email, '@');
        }

        $user = $this->userService->findOrCreateFromEmployeePortal(
            $name,
            $email,
            $employeeId !== '' ? $employeeId : null,
            $employmentStatus,
        );

        $code = Str::random(64);
        $ttl = max(30, (int) config('employee-portal.handoff_ttl_seconds', 120));

        Cache::put(self::HANDOFF_CACHE_PREFIX.$code, [
            'user_id' => $user->id,
            'nonce' => (string) ($payload['nonce'] ?? ''),
            'created_at' => now()->timestamp,
        ], $ttl);

        return route('auth.portal.callback', ['code' => $code]);
    }

    public function consumeHandoffCode(string $code): CareEarthUser
    {
        $key = self::HANDOFF_CACHE_PREFIX.$code;
        $payload = Cache::pull($key);

        if (! is_array($payload) || ! isset($payload['user_id'])) {
            throw new RuntimeException('ログイン用コードが無効か、有効期限切れです。');
        }

        $user = CareEarthUser::query()->find((int) $payload['user_id']);
        if ($user === null) {
            throw new RuntimeException('ユーザーが見つかりません。');
        }

        return $user;
    }

    public function assertInternalAuth(Request $request): void
    {
        $proxySecret = (string) config('employee-portal.proxy_secret');
        $syncSecret = (string) config('employee-portal.sync_secret');
        $providedProxy = (string) $request->header('X-Employee-Portal-Proxy-Secret', '');
        $providedSync = (string) $request->header('X-Employee-Site-Sync-Secret', '');

        $secretOk = ($proxySecret !== '' && hash_equals($proxySecret, $providedProxy))
            || ($syncSecret !== '' && hash_equals($syncSecret, $providedSync));

        $bearer = $request->bearerToken();
        $tokenOk = false;

        if (is_string($bearer) && $bearer !== '') {
            $tokenOk = $this->verifyGoogleIdentityToken($bearer);
        }

        if (! $secretOk && ! $tokenOk) {
            throw new RuntimeException('内部 API の認証に失敗しました。');
        }
    }

    private function verifyGoogleIdentityToken(string $jwt): bool
    {
        $audience = rtrim((string) config('employee-portal.audience'), '/');
        if ($audience === '') {
            return false;
        }

        try {
            $certsResponse = Http::timeout(10)->get('https://www.googleapis.com/oauth2/v3/certs');
            if (! $certsResponse->successful()) {
                return false;
            }

            $keys = JWK::parseKeySet($certsResponse->json());
            $decoded = JWT::decode($jwt, $keys);
            $aud = $decoded->aud ?? null;

            if (is_array($aud)) {
                return in_array($audience, $aud, true)
                    || in_array(rtrim($audience, '/'), array_map(
                        static fn ($value) => is_string($value) ? rtrim($value, '/') : $value,
                        $aud,
                    ), true);
            }

            if (! is_string($aud)) {
                return false;
            }

            return rtrim($aud, '/') === $audience;
        } catch (Throwable) {
            return false;
        }
    }
}
