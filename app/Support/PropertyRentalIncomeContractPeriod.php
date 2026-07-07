<?php

namespace App\Support;

use Carbon\Carbon;

final class PropertyRentalIncomeContractPeriod
{
    public static function enabled(): bool
    {
        return (bool) config('property-rental-income.contract_period_bulk_register', false);
    }

    public static function maxMonths(): int
    {
        return (int) config('property-rental-income.contract_period_max_months', 120);
    }

    /**
     * @return list<int> YYYYMM values from start through end (inclusive by month)
     */
    public static function monthsBetween(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->startOfMonth();

        if ($start->gt($end)) {
            return [];
        }

        $months = [];
        $current = $start->copy();

        while ($current->lte($end)) {
            $months[] = (int) $current->format('Ym');
            $current->addMonth();
        }

        return $months;
    }

    public static function paymentOnForMonth(string $contractStartDate, int $paymentMonth): string
    {
        $start = Carbon::parse($contractStartDate);
        $year = intdiv($paymentMonth, 100);
        $month = $paymentMonth % 100;
        $day = min($start->day, Carbon::create($year, $month, 1)->daysInMonth);

        return Carbon::create($year, $month, $day)->toDateString();
    }
}
