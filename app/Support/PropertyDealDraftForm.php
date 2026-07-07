<?php

namespace App\Support;

final class PropertyDealDraftForm
{
    /** @return list<array<string, mixed>> */
    public static function rows(): array
    {
        return config('property-deal-draft.rows', []);
    }

    /** @return list<string> */
    public static function integerKeys(): array
    {
        return [
            'property_price',
            'registration_license_tax',
            'judicial_scrivener_fee',
            'postage',
            'pre_registration_info_fee',
            'post_registration_certificate_fee',
            'withholding_income_tax',
            'purchase_brokerage_fee',
            'building_consumption_tax',
            'real_estate_acquisition_tax',
            'renovation_cost',
            'contingency_fund',
            'expected_selling_price',
            'sale_brokerage_fee',
            'contract_stamp_duty',
            'receipt_stamp_duty',
            'expected_rent',
        ];
    }

    /** @return list<string> */
    public static function percentKeys(): array
    {
        return [];
    }

    /** @return list<string> */
    public static function textKeys(): array
    {
        return ['case_number', 'location', 'usage', 'nationality'];
    }

    /** @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        foreach (self::integerKeys() as $key) {
            $input[$key] = self::parseInteger($input[$key] ?? null);
        }

        foreach (self::percentKeys() as $key) {
            $input[$key] = self::parseDecimal($input[$key] ?? null);
        }

        foreach (self::textKeys() as $key) {
            if ($key === 'case_number') {
                continue;
            }

            $value = trim((string) ($input[$key] ?? ''));
            $input[$key] = $value !== '' ? $value : null;
        }

        $input['case_number'] = trim((string) ($input['case_number'] ?? ''));

        return $input;
    }

    private static function parseInteger(mixed $value): ?int
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

    private static function parseDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = str_replace(['%', ',', ' '], '', (string) $value);

        if ($normalized === '') {
            return null;
        }

        return round((float) $normalized, 1);
    }
}
