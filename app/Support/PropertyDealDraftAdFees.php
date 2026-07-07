<?php

namespace App\Support;

use App\Models\PropertyDealDraft;

final class PropertyDealDraftAdFees
{
    /** @param array<int, array<string, mixed>> $items */
    public static function sync(PropertyDealDraft $draft, array $items): void
    {
        $draft->adFees()->delete();

        $sortOrder = 0;

        foreach ($items as $item) {
            $agencyName = trim((string) ($item['agency_name'] ?? ''));
            $amount = self::parseAmount($item['amount'] ?? null);

            if ($agencyName === '') {
                continue;
            }

            $draft->adFees()->create([
                'agency_name' => $agencyName,
                'amount' => $amount,
                'sort_order' => $sortOrder,
            ]);

            $sortOrder++;
        }
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
