<?php

namespace App\Support;

use App\Models\PropertyDealDraft;

final class PropertyDealDraftSheet
{
    /** @return list<array<string, mixed>> */
    public static function rows(): array
    {
        return config('property-deal-draft.rows', []);
    }

    public static function formatCell(PropertyDealDraft $record, array $row): string
    {
        if (($row['type'] ?? null) === 'group' || ($row['type'] ?? null) === 'documents') {
            return '';
        }

        $key = $row['key'] ?? '';
        $value = $record->{$key};

        return match ($row['format'] ?? 'text') {
            'status' => config('property-deal-draft.statuses.'.$value, $value ?: '—'),
            'property_type' => config('property-deal-draft.property_types.'.$value, match ($value) {
                'detached' => '戸建て',
                default => $value ?: '—',
            }),
            'yen' => self::formatYen($value),
            'yen_signed' => self::formatSignedYen($value),
            'percent' => self::formatPercent($value),
            default => $value !== null && $value !== '' ? (string) $value : '',
        };
    }

    private static function formatYen(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        return number_format((int) $value);
    }

    private static function formatSignedYen(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '0';
        }

        $amount = (int) $value;

        if ($amount < 0) {
            return '-'.number_format(abs($amount));
        }

        return number_format($amount);
    }

    private static function formatPercent(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return rtrim(rtrim(number_format((float) $value, 1), '0'), '.').'%';
    }
}
