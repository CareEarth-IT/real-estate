<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final class PropertyDealDraftFiscalYear
{
    public static function currentReiwaYear(?CarbonInterface $date = null): int
    {
        $date = $date ?? now();
        $calendarYear = (int) $date->format('Y');
        $month = (int) $date->format('n');

        if ($month >= 4) {
            return $calendarYear - 2018;
        }

        return $calendarYear - 2019;
    }

    public static function hasFiscalYearStarted(int $reiwaYear, ?CarbonInterface $date = null): bool
    {
        $date = $date ?? now();
        $startYear = 2018 + $reiwaYear;

        return $date->greaterThanOrEqualTo(Carbon::create($startYear, 4, 1)->startOfDay());
    }

    /** @return list<int> */
    public static function visibleYears(?CarbonInterface $date = null): array
    {
        $date = $date ?? now();
        $current = self::currentReiwaYear($date);
        $years = [];

        if ($current > 1) {
            $years[] = $current - 1;
        }

        $years[] = $current;

        $next = $current + 1;

        if (self::hasFiscalYearStarted($next, $date)) {
            $years[] = $next;
        }

        return $years;
    }

    public static function label(int $reiwaYear): string
    {
        $next = $reiwaYear + 1;

        return "R{$reiwaYear}年度（R{$reiwaYear}/4/1-R{$next}/3/31）";
    }
}
