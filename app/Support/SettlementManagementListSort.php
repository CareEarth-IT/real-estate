<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class SettlementManagementListSort
{
    public const SORT_TRANSFER_DATE = 'settlement_transfer_date';

    public const SORT_CONTRACT_DATE = 'contract_date';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::SORT_TRANSFER_DATE => '決済振込日',
            self::SORT_CONTRACT_DATE => '契約日',
        ];
    }

    public static function normalizeSort(?string $sort): string
    {
        $sort = (string) $sort;

        return array_key_exists($sort, self::options()) ? $sort : '';
    }

    public static function normalizeDirection(?string $direction): string
    {
        return strtolower((string) $direction) === 'desc' ? 'desc' : 'asc';
    }

    public static function normalizeTransferDate(?string $date): ?string
    {
        $date = trim((string) $date);
        if ($date === '') {
            return null;
        }

        try {
            return Carbon::parse($date)->toDateString();
        } catch (Throwable) {
            return null;
        }
    }

    public static function apply(Builder $query, string $sort, string $direction): Builder
    {
        $sort = self::normalizeSort($sort);
        $direction = self::normalizeDirection($direction);

        if ($sort === '') {
            return $query->orderByDesc('applications.created_at');
        }

        $column = 'settlement_managements.'.$sort;

        return $query
            ->orderByRaw("CASE WHEN {$column} IS NULL THEN 1 ELSE 0 END")
            ->orderBy($column, $direction)
            ->orderByDesc('applications.created_at');
    }
}
