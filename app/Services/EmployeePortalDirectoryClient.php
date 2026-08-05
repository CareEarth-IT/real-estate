<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class EmployeePortalDirectoryClient
{
    /**
     * @param  array{keyword?: string, status?: string, department?: string}  $filters
     * @return array{employees: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function search(array $filters = []): array
    {
        $baseUrl = (string) config('careearth.employee_portal.api_url', '');
        $secret = (string) config('careearth.employee_portal.proxy_secret', '');

        if ($baseUrl === '' || $secret === '') {
            throw new RuntimeException('社員ポータル連携が設定されていません。.env に EMPLOYEE_PORTAL_API_URL と EMPLOYEE_PORTAL_PROXY_SECRET を設定してください。');
        }

        $query = array_filter([
            'keyword' => trim((string) ($filters['keyword'] ?? '')),
            'status' => trim((string) ($filters['status'] ?? '')),
            'department' => trim((string) ($filters['department'] ?? '')),
        ], fn (string $value): bool => $value !== '');

        $timeout = max(1, (int) config('careearth.employee_portal.timeout', 10));
        $url = $baseUrl.'/internal/portal/employee-directory';

        try {
            $response = Http::acceptJson()
                ->timeout($timeout)
                ->withHeaders([
                    'X-Employee-Portal-Proxy-Secret' => $secret,
                ])
                ->get($url, $query)
                ->throw();
        } catch (ConnectionException $e) {
            throw new RuntimeException('社員ポータルへ接続できませんでした。しばらくしてから再度お試しください。', 0, $e);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $message = match (true) {
                $status === 401, $status === 403 => '社員ポータル認証に失敗しました。共有秘密鍵を確認してください。',
                $status === 404 => '社員ポータルの社員一覧 API が見つかりません。URL を確認してください。',
                default => '社員ポータルからの取得に失敗しました。',
            };

            throw new RuntimeException($message, 0, $e);
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new RuntimeException('社員ポータルの応答形式が正しくありません。');
        }

        $employees = $payload['employees'] ?? [];
        if (! is_array($employees)) {
            $employees = [];
        }

        $meta = $payload['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        return [
            'employees' => array_values(array_filter($employees, 'is_array')),
            'meta' => $meta,
        ];
    }

    public function isConfigured(): bool
    {
        return (string) config('careearth.employee_portal.api_url', '') !== ''
            && (string) config('careearth.employee_portal.proxy_secret', '') !== '';
    }
}
