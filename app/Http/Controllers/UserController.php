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
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:'.implode(',', Role::assignableValues())],
        ]);

        try {
            $this->userService->create(
                $request->input('email', ''),
                $request->input('password', ''),
                $request->input('role', Role::FUDOSAN),
            );
        } catch (RuntimeException $e) {
            return back()
                ->withInput($request->only('email', 'role'))
                ->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'ユーザーを追加しました。');
    }

    public function update(Request $request, CareEarthUser $user): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'in:'.implode(',', Role::assignableValues())],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        try {
            $this->userService->updateRole($user, $request->input('role', Role::FUDOSAN));

            if ($request->filled('password')) {
                $this->userService->updatePassword($user, $request->input('password', ''));
            }
        } catch (RuntimeException $e) {
            return back()->withErrors(['form' => $e->getMessage()]);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'ユーザー情報を更新しました。');
    }
}
