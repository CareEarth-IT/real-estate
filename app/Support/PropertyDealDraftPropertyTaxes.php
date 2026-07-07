<?php

namespace App\Support;

use App\Models\PropertyDealDraft;

final class PropertyDealDraftPropertyTaxes
{
    /** @param array<int, array<string, mixed>> $items */
    public static function sync(PropertyDealDraft $draft, array $items): void
    {
        $visibleYears = PropertyDealDraftFiscalYear::visibleYears();
        $draft->propertyTaxes()->whereNotIn('fiscal_year', $visibleYears)->delete();

        foreach ($visibleYears as $fiscalYear) {
            $amount = null;

            foreach ($items as $item) {
                if ((int) ($item['fiscal_year'] ?? 0) !== $fiscalYear) {
                    continue;
                }

                $amount = self::parseAmount($item['amount'] ?? null);
                break;
            }

            $draft->propertyTaxes()->updateOrCreate(
                ['fiscal_year' => $fiscalYear],
                ['amount' => $amount],
            );
        }
    }

    public static function amountForYear(PropertyDealDraft $draft, int $fiscalYear): ?int
    {
        $tax = $draft->propertyTaxes->firstWhere('fiscal_year', $fiscalYear);

        return $tax?->amount;
    }

    public static function parseAmount(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace([',', '，', '¥', ' '], '', (string) $value);

        if ($normalized === '' || $normalized === '-') {
            return null;
        }

        return (int) $normalized;
    }
}
