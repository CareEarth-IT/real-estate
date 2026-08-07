<?php

namespace App\Http\Controllers;

use App\Models\CareEarthUser;
use App\Services\EmployeePortalDirectoryClient;
use App\Services\UserService;
use App\Support\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly EmployeePortalDirectoryClient $employeePortalDirectoryClient,
    ) {}

    public function index(): View
    {
        return view('users.index', [
            'users' => $this->userService->getAll(),
            'roles' => Role::assignableLabels(),
            'pageTitle' => 'ユーザー管理',
            'currentPage' => 'users',
            'employeePortalConfigured' => $this->employeePortalDirectoryClient->isConfigured(),
            'employeePortalDefaults' => [
                'department' => (string) (
                    config('employee-portal.default_department')
                    ?: config('careearth.employee_portal.default_department', '不動産')
                ),
                'status' => (string) (
                    config('employee-portal.default_status')
                    ?: config('careearth.employee_portal.default_status', '在籍')
                ),
            ],
        ]);
    }

    public function employeeDirectory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:20'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $result = $this->employeePortalDirectoryClient->search([
                'keyword' => $validated['keyword'] ?? '',
                'status' => $validated['status'] ?? '',
                'department' => $validated['department'] ?? '',
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 502);
        }

        $emails = [];
        $employeeIds = [];
        foreach ($result['employees'] as $employee) {
            $email = strtolower(trim((string) ($employee['email'] ?? '')));
            if ($email !== '') {
                $emails[] = $email;
            }
            $employeeId = trim((string) ($employee['employee_id'] ?? ''));
            if ($employeeId !== '') {
                $employeeIds[] = $employeeId;
            }
        }

        $registeredEmails = [];
        if ($emails !== []) {
            $registeredEmails = CareEarthUser::query()
                ->whereIn('email', array_values(array_unique($emails)))
                ->pluck('email')
                ->map(fn (string $email): string => strtolower($email))
                ->all();
        }
        $registeredEmailSet = array_fill_keys($registeredEmails, true);

        $registeredEmployeeIds = [];
        if ($employeeIds !== []) {
            $registeredEmployeeIds = CareEarthUser::query()
                ->whereIn('employee_id', array_values(array_unique($employeeIds)))
                ->pluck('employee_id')
                ->filter()
                ->map(fn ($id): string => (string) $id)
                ->all();
        }
        $registeredEmployeeIdSet = array_fill_keys($registeredEmployeeIds, true);

        $employees = array_map(function (array $employee) use ($registeredEmailSet, $registeredEmployeeIdSet): array {
            $email = strtolower(trim((string) ($employee['email'] ?? '')));
            $employeeId = trim((string) ($employee['employee_id'] ?? ''));
            $alreadyRegistered = ($email !== '' && isset($registeredEmailSet[$email]))
                || ($employeeId !== '' && isset($registeredEmployeeIdSet[$employeeId]));

            return [
                'id' => $employee['id'] ?? null,
                'employee_id' => $employee['employee_id'] ?? null,
                'name' => $employee['name'] ?? '',
                'email' => $employee['email'] ?? '',
                'employment_status' => $employee['employment_status'] ?? '',
                'company' => $employee['company'] ?? '',
                'department' => $employee['department'] ?? '',
                'section' => $employee['section'] ?? '',
                'position' => $employee['position'] ?? '',
                'already_registered' => $alreadyRegistered,
            ];
        }, $result['employees']);

        return response()->json([
            'employees' => $employees,
            'meta' => $result['meta'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:'.implode(',', Role::assignableValues())],
            'show_performance' => ['nullable', 'boolean'],
            'employee_id' => ['nullable', 'string', 'max:64'],
        ], [
            'name.required' => '名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'role.required' => 'ロールを選択してください。',
        ]);

        $showPerformance = $request->boolean('show_performance');
        $employeeId = $request->input('employee_id');

        try {
            $this->userService->create(
                $request->input('name', ''),
                $request->input('email', ''),
                $request->input('password', ''),
                $request->input('role', Role::EDITOR),
                $showPerformance,
                is_string($employeeId) ? $employeeId : null,
            );
        } catch (RuntimeException $e) {
            return back()
                ->withInput($request->only('name', 'email', 'role', 'show_performance', 'employee_id'))
                ->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'ユーザーを追加しました。登録したメールアドレスとパスワードでログインできます。');
    }

    public function update(Request $request, CareEarthUser $user): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'in:'.implode(',', Role::assignableValues())],
            'show_performance' => ['nullable', 'boolean'],
        ], [
            'name.required' => '名前を入力してください。',
            'name.max' => '名前は100文字以内で入力してください。',
            'role.required' => 'ロールを選択してください。',
        ]);

        $showPerformance = $request->boolean('show_performance');

        try {
            $this->userService->update(
                $user,
                $request->input('name', ''),
                $request->input('role', Role::EDITOR),
                $showPerformance,
            );
        } catch (RuntimeException $e) {
            return back()->withErrors(['form' => $e->getMessage()]);
        }

        if ((int) $request->session()->get('user_id') === (int) $user->id) {
            $request->session()->put('name', $user->fresh()?->name);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'ユーザー情報を更新しました。');
    }
}
