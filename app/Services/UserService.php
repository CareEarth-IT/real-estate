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
            ->orderBy('name')
            ->orderBy('email')
            ->get();
    }

    public function create(
        string $name,
        string $email,
        string $password,
        string $role,
        bool $showPerformance = true,
    ): CareEarthUser {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '') {
            throw new RuntimeException('名前を入力してください。');
        }

        if ($email === '') {
            throw new RuntimeException('メールアドレスを入力してください。');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('メールアドレスの形式が正しくありません。');
        }

        if ($password === '') {
            throw new RuntimeException('パスワードを入力してください。');
        }

        if (mb_strlen($password) < 8) {
            throw new RuntimeException('パスワードは8文字以上で入力してください。');
        }

        if (! Role::isAssignable($role)) {
            throw new RuntimeException('ロールが正しくありません。');
        }

        if (CareEarthUser::query()->where('email', $email)->exists()) {
            throw new RuntimeException('このメールアドレスは既に登録されています。');
        }

        $user = new CareEarthUser([
            'name' => $name,
            'email' => $email,
            'role' => Role::normalize($role),
            'show_performance' => $showPerformance,
        ]);
        $user->setPassword($password);
        $user->save();

        return $user;
    }

    public function updateRole(CareEarthUser $user, string $role): void
    {
        if (! Role::isAssignable($role)) {
            throw new RuntimeException('ロールが正しくありません。');
        }

        $user->update(['role' => Role::normalize($role)]);
    }

    public function update(CareEarthUser $user, string $name, string $role, bool $showPerformance): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new RuntimeException('名前を入力してください。');
        }

        if (mb_strlen($name) > 100) {
            throw new RuntimeException('名前は100文字以内で入力してください。');
        }

        if (! Role::isAssignable($role)) {
            throw new RuntimeException('ロールが正しくありません。');
        }

        $user->update([
            'name' => $name,
            'role' => Role::normalize($role),
            'show_performance' => $showPerformance,
        ]);
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
