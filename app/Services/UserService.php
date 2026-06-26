<?php

namespace App\Services;

use App\Models\CareEarthUser;
use App\Support\Role;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;

class UserService
{
    /** @return Collection<int, CareEarthUser> */
    public function getAll(): Collection
    {
        return CareEarthUser::query()
            ->orderBy('email')
            ->get();
    }

    public function create(string $email, string $password, string $role): CareEarthUser
    {
        $email = strtolower(trim($email));

        if ($email === '') {
            throw new RuntimeException('メールアドレスを入力してください。');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('メールアドレスの形式が正しくありません。');
        }

        if ($password === '') {
            throw new RuntimeException('パスワードを入力してください。');
        }

        if (! Role::isAssignable($role)) {
            throw new RuntimeException('ロールが正しくありません。');
        }

        if (CareEarthUser::query()->where('email', $email)->exists()) {
            throw new RuntimeException('このメールアドレスは既に登録されています。');
        }

        $user = new CareEarthUser([
            'email' => $email,
            'role' => $role,
        ]);
        $user->setPassword($password);
        $user->save();

        return $user;
    }

    public function updateRole(CareEarthUser $user, string $role): void
    {
        if (Role::isAdmin($user->role)) {
            throw new RuntimeException('管理者ロールは変更できません。');
        }

        if (! Role::isAssignable($role)) {
            throw new RuntimeException('ロールが正しくありません。');
        }

        $user->update(['role' => $role]);
    }

    public function updatePassword(CareEarthUser $user, string $password): void
    {
        if ($password === '') {
            throw new RuntimeException('パスワードを入力してください。');
        }

        $user->setPassword($password);
        $user->save();
    }

    public function findByEmail(string $email): ?CareEarthUser
    {
        return CareEarthUser::query()
            ->where('email', strtolower(trim($email)))
            ->first();
    }
}
