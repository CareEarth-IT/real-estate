<?php

namespace App\Services;

use App\Models\CareEarthUser;
use App\Support\Role;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class UserService
{
    /** @return Collection<int, CareEarthUser> */
    public function getAll(): Collection
    {
        return CareEarthUser::query()
            ->whereNotIn('email', self::hiddenManagementEmails())
            ->orderBy('id')
            ->get();
    }

    public static function hiddenManagementEmails(): array
    {
        $emails = config('careearth.hidden_management_emails', ['tomoya_hayashi@careearth.info']);

        return array_values(array_filter(array_map(
            fn ($email): string => strtolower(trim((string) $email)),
            is_array($emails) ? $emails : []
        )));
    }

    public function isHiddenFromManagement(CareEarthUser $user): bool
    {
        return in_array(strtolower(trim((string) $user->email)), self::hiddenManagementEmails(), true);
    }

    public function create(
        string $name,
        string $email,
        string $password,
        string $role,
        bool $showPerformance = true,
        ?string $employeeId = null,
    ): CareEarthUser {
        $name = trim($name);
        $email = strtolower(trim($email));
        $employeeId = $employeeId !== null ? trim($employeeId) : null;
        if ($employeeId === '') {
            $employeeId = null;
        }

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

        if ($employeeId !== null && CareEarthUser::query()->where('employee_id', $employeeId)->exists()) {
            throw new RuntimeException('この社員IDは既に登録されています。');
        }

        $user = new CareEarthUser([
            'name' => $name,
            'email' => $email,
            'employee_id' => $employeeId,
            'role' => Role::normalize($role),
            'show_performance' => $showPerformance,
        ]);
        $user->setPassword($password);
        $user->save();

        return $user;
    }

    /**
     * 社員ポータル SSO / 同期用。既存ユーザーはメールまたは社員IDで突合し、なければ viewer で作成。
     */
    public function findOrCreateFromEmployeePortal(
        string $name,
        string $email,
        ?string $employeeId = null,
        ?string $employmentStatus = null,
    ): CareEarthUser {
        $name = trim($name);
        $email = strtolower(trim($email));
        $employeeId = $employeeId !== null ? trim($employeeId) : null;
        if ($employeeId === '') {
            $employeeId = null;
        }

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('有効なメールアドレスが必要です。');
        }

        if ($name === '') {
            $name = Str::before($email, '@');
        }

        $user = null;

        if ($employeeId !== null) {
            $user = CareEarthUser::query()->where('employee_id', $employeeId)->first();
        }

        if ($user === null) {
            $user = CareEarthUser::query()->where('email', $email)->first();
        }

        if ($user !== null) {
            $updates = [
                'name' => $name,
                'synced_at' => now(),
            ];

            if ($employeeId !== null && $user->employee_id !== $employeeId) {
                $conflict = CareEarthUser::query()
                    ->where('employee_id', $employeeId)
                    ->where('id', '!=', $user->id)
                    ->exists();
                if ($conflict) {
                    throw new RuntimeException('この社員IDは別ユーザーに既に紐づいています。');
                }
                $updates['employee_id'] = $employeeId;
            }

            if ($employmentStatus !== null && $employmentStatus !== '') {
                $updates['employment_status'] = $employmentStatus;
            }

            if ($user->email !== $email) {
                $emailTaken = CareEarthUser::query()
                    ->where('email', $email)
                    ->where('id', '!=', $user->id)
                    ->exists();
                if ($emailTaken) {
                    throw new RuntimeException('このメールアドレスは別ユーザーに既に登録されています。');
                }
                $updates['email'] = $email;
            }

            $user->update($updates);

            return $user->fresh();
        }

        $defaultRole = Role::normalize((string) config('employee-portal.default_role', Role::VIEWER));

        $user = new CareEarthUser([
            'name' => $name,
            'email' => $email,
            'employee_id' => $employeeId,
            'employment_status' => $employmentStatus,
            'synced_at' => now(),
            'role' => $defaultRole,
            'show_performance' => true,
        ]);
        // SSO 専用ユーザーはローカルパスワードを使わない（ランダム不可逆）
        $user->setPassword(Str::random(64));
        $user->save();

        return $user;
    }

    public function findByEmployeeId(string $employeeId): ?CareEarthUser
    {
        $employeeId = trim($employeeId);
        if ($employeeId === '') {
            return null;
        }

        return CareEarthUser::query()->where('employee_id', $employeeId)->first();
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
        if ($this->isHiddenFromManagement($user)) {
            throw new RuntimeException('このユーザーはユーザー管理から操作できません。');
        }

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

    public function delete(CareEarthUser $user, ?int $currentUserId = null): void
    {
        if ($this->isHiddenFromManagement($user)) {
            throw new RuntimeException('このユーザーはユーザー管理から操作できません。');
        }

        if ($currentUserId !== null && (int) $user->id === $currentUserId) {
            throw new RuntimeException('ログイン中のユーザーは削除できません。');
        }

        $user->delete();
    }
}
