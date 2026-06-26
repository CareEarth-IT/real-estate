<?php

namespace App\Support;

final class Role
{
    public const FUDOSAN = 'fudosan';

    public const KEIRI = 'keiri';

    public const ADMIN = 'admin';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::FUDOSAN => '不動産',
            self::KEIRI => '経理',
            self::ADMIN => '管理者',
        ];
    }

    /** @return array<string, string> ユーザー管理で割り当て可能なロール */
    public static function assignableLabels(): array
    {
        return [
            self::FUDOSAN => self::labels()[self::FUDOSAN],
            self::KEIRI => self::labels()[self::KEIRI],
        ];
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_keys(self::labels());
    }

    /** @return list<string> */
    public static function assignableValues(): array
    {
        return array_keys(self::assignableLabels());
    }

    public static function label(string $role): string
    {
        return self::labels()[$role] ?? $role;
    }

    public static function isValid(string $role): bool
    {
        return isset(self::labels()[$role]);
    }

    public static function isAssignable(string $role): bool
    {
        return isset(self::assignableLabels()[$role]);
    }

    public static function isAdmin(string $role): bool
    {
        return $role === self::ADMIN;
    }
}
