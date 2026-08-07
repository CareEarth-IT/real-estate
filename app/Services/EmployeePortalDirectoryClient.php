<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class EmployeePortalDirectoryClient
{
    public function isConfigured(): bool
    {
        return $this->apiUrl() !== '' && $this->proxySecret() !== '';
    }

    /**
     * @param  array{keyword?: string, status?: string, department?: string}|string|null  $filtersOrKeyword
     * @return array{employees: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public function search(
        array|string|null $filtersOrKeyword = null,
        ?string $status = null,
        ?string $department = null,
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('社員ポータル連携が設定されていません。.env に EMPLOYEE_PORTAL_API_URL と EMPLOYEE_PORTAL_PROXY_SECRET を設定してください。');
        }

        if (is_array($filtersOrKeyword)) {
            $keyword = trim((string) ($filtersOrKeyword['keyword'] ?? ''));
            $status = trim((string) ($filtersOrKeyword['status'] ?? $status ?? ''));
            $department = trim((string) ($filtersOrKeyword['department'] ?? $department ?? ''));
        } else {
            $keyword = trim((string) ($filtersOrKeyword ?? ''));
            $status = trim((string) ($status ?? ''));
            $department = trim((string) ($department ?? ''));
        }

        if ($status === '') {
            $status = $this->defaultStatus();
        }
        if ($department === '') {
            $department = $this->defaultDepartment();
        }

        $query = array_filter([
            'keyword' => $keyword !== '' ? $keyword : null,
            'status' => $status !== '' ? $status : null,
            'department' => $department !== '' ? $department : null,
        ], static fn ($value) => $value !== null && $value !== '');

        $timeout = max(1, $this->timeout());
        $url = $this->apiUrl().'/internal/portal/employee-directory';

        try {
            $response = Http::acceptJson()
                ->timeout($timeout)
                ->withHeaders([
                    'X-Employee-Portal-Proxy-Secret' => $this->proxySecret(),
                ])
                ->get($url, $query)
                ->throw();
        } catch (ConnectionException $e) {
            throw new RuntimeException('社員ポータルへ接続できませんでした。しばらくしてから再度お試しください。', 0, $e);
        } catch (RequestException $e) {
            $httpStatus = $e->response?->status();
            $message = match (true) {
                $httpStatus === 401, $httpStatus === 403 => '社員ポータル認証に失敗しました。共有秘密鍵を確認してください。',
                $httpStatus === 404 => '社員ポータルの社員一覧 API が見つかりません。URL を確認してください。',
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

    /**
     * メールまたは社員IDで在籍社員を1件取得。見つからなければ null。
     *
     * @return array<string, mixed>|null
     */
    public function findActiveEmployee(?string $email = null, ?string $employeeId = null): ?array
    {
        $email = $email !== null ? strtolower(trim($email)) : null;
        $employeeId = $employeeId !== null ? trim($employeeId) : null;

        if (($email === null || $email === '') && ($employeeId === null || $employeeId === '')) {
            return null;
        }

        $keyword = $email ?: $employeeId;
        $result = $this->search($keyword, $this->defaultStatus());

        foreach ($result['employees'] as $employee) {
            $rowEmail = strtolower(trim((string) ($employee['email'] ?? '')));
            $rowId = trim((string) ($employee['employee_id'] ?? ''));
            $rowStatus = trim((string) ($employee['employment_status'] ?? ''));

            if ($rowStatus !== '' && $rowStatus !== $this->defaultStatus()) {
                continue;
            }

            if ($email !== null && $email !== '' && $rowEmail === $email) {
                return $employee;
            }

            if ($employeeId !== null && $employeeId !== '' && $rowId === $employeeId) {
                return $employee;
            }
        }

        return null;
    }

    private function apiUrl(): string
    {
        $url = (string) config('employee-portal.api_url', '');
        if ($url === '') {
            $url = (string) config('careearth.employee_portal.api_url', '');
        }

        return rtrim($url, '/');
    }

    private function proxySecret(): string
    {
        $secret = (string) config('employee-portal.proxy_secret', '');
        if ($secret === '') {
            $secret = (string) config('careearth.employee_portal.proxy_secret', '');
        }

        return $secret;
    }

    private function defaultStatus(): string
    {
        return (string) (
            config('employee-portal.default_status')
            ?: config('careearth.employee_portal.default_status', '在籍')
        );
    }

    private function defaultDepartment(): string
    {
        return (string) (
            config('employee-portal.default_department')
            ?: config('careearth.employee_portal.default_department', '不動産')
        );
    }

    private function timeout(): int
    {
        return (int) (
            config('employee-portal.http_timeout')
            ?: config('careearth.employee_portal.timeout', 10)
        );
    }
}
