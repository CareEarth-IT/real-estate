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

    /**
     * 支払い月プルダウン用の候補月（表示タブ・登録済み月・データがある月を統合）。
     *
     * @return list<int>
     */
    public static function pickerMonths(int $activePaymentMonth): array
    {
        if (! YearMonth::isValid($activePaymentMonth)) {
            $activePaymentMonth = (int) now()->format('Ym');
        }

        $months = collect(self::visibleTabs($activePaymentMonth));

        $registered = PropertyRentalIncomeMonth::query()
            ->orderByDesc('payment_month')
            ->pluck('payment_month');

        $fromData = PropertyRentalIncome::query()
            ->whereNotNull('payment_month')
            ->distinct()
            ->orderByDesc('payment_month')
            ->pluck('payment_month');

        return $months
            ->merge($registered)
            ->merge($fromData)
            ->push($activePaymentMonth)
            ->map(static fn ($month): int => (int) $month)
            ->filter(static fn (int $month): bool => YearMonth::isValid($month))
            ->unique()
            ->sort()
            ->values()
            ->all();
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
