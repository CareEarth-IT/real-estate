<?php

namespace App\Support;

final class Role
{
    /** 開発用。本番前に削除予定。全画面の閲覧・編集が可能。 */
    public const ADMIN = 'admin';

    public const BUCHO = 'bucho';

    public const EDITOR = 'editor';

    public const VIEWER = 'viewer';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::ADMIN => '管理者',
            self::BUCHO => '部長',
            self::EDITOR => '編集者',
            self::VIEWER => '閲覧者',
        ];
    }

    /** @return array<string, string> */
    public static function assignableLabels(): array
    {
        return self::labels();
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_keys(self::labels());
    }

    /** @return list<string> */
    public static function assignableValues(): array
    {
        return self::values();
    }

    public static function label(string $role): string
    {
        return self::labels()[self::normalize($role)] ?? $role;
    }

    public static function isValid(string $role): bool
    {
        return isset(self::labels()[$role]);
    }

    public static function isAssignable(string $role): bool
    {
        return self::isValid($role);
    }

    public static function normalize(string $role): string
    {
        return match ($role) {
            'fudosan', 'keiri' => self::EDITOR,
            default => self::isValid($role) ? $role : self::VIEWER,
        };
    }

    public static function isAdmin(string $role): bool
    {
        return self::normalize($role) === self::ADMIN;
    }

    public static function isBucho(string $role): bool
    {
        return self::normalize($role) === self::BUCHO;
    }

    public static function isEditor(string $role): bool
    {
        return self::normalize($role) === self::EDITOR;
    }

    public static function isViewer(string $role): bool
    {
        return self::normalize($role) === self::VIEWER;
    }

    public static function canManageUsers(string $role): bool
    {
        return self::isAdmin($role) || self::isBucho($role);
    }

    public static function canAccessPropertyMaster(string $role): bool
    {
        return self::isAdmin($role) || self::isBucho($role);
    }

    public static function canEdit(string $role): bool
    {
        return self::isAdmin($role) || self::isBucho($role) || self::isEditor($role);
    }
}
