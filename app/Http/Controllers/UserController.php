<?php

namespace App\Http\Controllers;

use App\Models\CareEarthUser;
use App\Services\UserService;
use App\Support\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {}

    public function index(): View
    {
        return view('users.index', [
            'users' => $this->userService->getAll(),
            'roles' => Role::assignableLabels(),
            'pageTitle' => 'ユーザー管理',
            'currentPage' => 'users',
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
        ], [
            'name.required' => '名前を入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスの形式が正しくありません。',
            'password.required' => 'パスワードを入力してください。',
            'password.min' => 'パスワードは8文字以上で入力してください。',
            'role.required' => 'ロールを選択してください。',
        ]);

        $showPerformance = $request->boolean('show_performance');

        try {
            $this->userService->create(
                $request->input('name', ''),
                $request->input('email', ''),
                $request->input('password', ''),
                $request->input('role', Role::EDITOR),
                $showPerformance,
            );
        } catch (RuntimeException $e) {
            return back()
                ->withInput($request->only('name', 'email', 'role', 'show_performance'))
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
