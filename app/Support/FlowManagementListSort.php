<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FlowManagementListSort
{
    public const SORT_MOVE_IN = 'move_in_date';

    public const SORT_DOCUMENT_DEADLINE = 'document_deadline';

    public const SORT_SCHEDULED_VISIT = 'scheduled_visit_date';

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            self::SORT_MOVE_IN => '入居日',
            self::SORT_DOCUMENT_DEADLINE => '書類期日',
            self::SORT_SCHEDULED_VISIT => '来社予定日',
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

    /**
     * 本日を基準にした相対日ラベル。
     * 期限内（未来）は「N日前」（期限のN日前）、当日は「本日」、過去は「N日前」。
     */
    public static function relativeDayLabel(mixed $date, ?CarbonInterface $today = null): ?string
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            $target = \Illuminate\Support\Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        $today = ($today ?? now())->copy()->startOfDay();
        $target = $target->copy()->startOfDay();

        if ($target->equalTo($today)) {
            return '本日';
        }

        $diff = (int) $today->diffInDays($target);

        // 未来・過去とも「N日前」（未来は期限のN日前）
        return $diff.'日前';
    }

    public static function isWithinWeekWindow(mixed $date, ?CarbonInterface $today = null): bool
    {
        if ($date === null || $date === '') {
            return false;
        }

        try {
            $value = \Illuminate\Support\Carbon::parse($date)->toDateString();
        } catch (\Throwable) {
            return false;
        }

        [$weekFrom, $weekTo] = self::weekWindow($today);

        return $value >= $weekFrom && $value <= $weekTo;
    }

    /**
     * 本日〜7日後（両端含む）の期間。
     *
     * @return array{0: string, 1: string} [from, to] Y-m-d
     */
    public static function weekWindow(?CarbonInterface $today = null): array
    {
        $today = ($today ?? now())->copy()->startOfDay();

        return [
            $today->toDateString(),
            $today->copy()->addDays(7)->toDateString(),
        ];
    }

    public static function apply(Builder $query, string $sort, string $direction): Builder
    {
        $sort = self::normalizeSort($sort);
        $direction = self::normalizeDirection($direction);

        if ($sort === '') {
            return $query->orderByDesc('applications.created_at');
        }

        [$weekFrom, $weekTo] = self::weekWindow();
        $column = 'flow_managements.'.$sort;

        return $query
            ->orderByRaw(
                "CASE WHEN {$column} IS NOT NULL AND {$column} BETWEEN ? AND ? THEN 0 ELSE 1 END",
                [$weekFrom, $weekTo]
            )
            ->orderByRaw("CASE WHEN {$column} IS NULL THEN 1 ELSE 0 END")
            ->orderBy($column, $direction)
            ->orderByDesc('applications.created_at');
    }

    /**
     * @return array<string, int>
     */
    public static function withinWeekCounts(Builder $baseQuery): array
    {
        [$weekFrom, $weekTo] = self::weekWindow();
        $counts = [];

        foreach (array_keys(self::options()) as $field) {
            $counts[$field] = (clone $baseQuery)
                ->whereBetween('flow_managements.'.$field, [$weekFrom, $weekTo])
                ->count();
        }

        return $counts;
    }

    /** @return array<string, string|null> */
    public static function queryParams(Request $request, ?string $sort = null, ?string $direction = null): array
    {
        $params = [];
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $params['search'] = $search;
        }

        $sort = self::normalizeSort($sort ?? $request->input('sort'));
        if ($sort !== '') {
            $params['sort'] = $sort;
            $params['direction'] = self::normalizeDirection($direction ?? $request->input('direction'));
        }

        return $params;
    }
}
