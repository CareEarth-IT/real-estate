<?php

namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Controller;
use App\Services\EmployeePortalSsoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Throwable;

class PortalSsoController extends Controller
{
    public function __construct(
        private readonly EmployeePortalSsoService $ssoService,
    ) {}

    /**
     * 社員ポータル → 不動産の SSO handoff。
     * Identity Token または共有秘密で認証し、ブラウザ用 callback URL を返す。
     */
    public function handoff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'employee_id' => ['nullable', 'string', 'max:64'],
            'name' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:255'],
            'nonce' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $redirectUrl = $this->ssoService->createHandoff($request, $validated);
        } catch (RuntimeException $e) {
            $status = str_contains($e->getMessage(), '認証') ? 401 : 422;

            return response()->json(['message' => $e->getMessage()], $status);
        } catch (Throwable $e) {
            report($e);

            return response()->json(['message' => 'SSO handoff に失敗しました。'], 500);
        }

        return response()->json([
            'success' => true,
            'redirect_url' => $redirectUrl,
        ]);
    }
}
