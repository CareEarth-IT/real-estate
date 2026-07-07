<?php

namespace App\Support;

use App\Models\PropertyRentalIncome;
use App\Models\PropertyRentalIncomeMonth;

final class PropertyRentalIncomeMonths
{
    public static function ensure(int $paymentMonth): void
    {
        if ($paymentMonth < 101) {
            return;
        }

        PropertyRentalIncomeMonth::query()->firstOrCreate([
            'payment_month' => $paymentMonth,
        ]);
    }

    /**
     * 選択中の支払い月を基準に、最大6ヶ月の連続したプルダウン候補を返す。
     * 1ヶ月前〜4ヶ月先（計6ヶ月）。2ヶ月以上前は非表示。
     *
     * @return list<int>
     */
    public static function visibleTabs(int $activePaymentMonth): array
    {
        if (! YearMonth::isValid($activePaymentMonth)) {
            $activePaymentMonth = (int) now()->format('Ym');
        }

        $maxTabs = max(1, (int) config('property-rental-income.max_visible_month_tabs', 6));
        $monthsBefore = 1;
        $months = [];

        for ($offset = -$monthsBefore; count($months) < $maxTabs; $offset++) {
            $months[] = YearMonth::addMonths($activePaymentMonth, $offset);
        }

        return $months;
    }

    public static function latestPaymentMonthWithData(): ?int
    {
        $latest = PropertyRentalIncome::query()
            ->whereNotNull('payment_month')
            ->max('payment_month');

        if ($latest === null) {
            return null;
        }

        $month = (int) $latest;

        return YearMonth::isValid($month) ? $month : null;
    }

    public static function exists(int $paymentMonth): bool
    {
        if ($paymentMonth < 101) {
            return false;
        }

        return PropertyRentalIncome::query()
            ->where('payment_month', $paymentMonth)
            ->exists();
    }
}
