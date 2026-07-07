<?php

namespace App\Support;

use Carbon\Carbon;

final class YearMonth
{
    public static function fromInput(?string $value): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $normalized = str_replace('/', '-', trim($value));

        if (preg_match('/^(\d{4})-(\d{1,2})$/', $normalized, $matches) === 1) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];

            if ($month >= 1 && $month <= 12) {
                return $year * 100 + $month;
            }
        }

        if (preg_match('/^(\d{6})$/', $normalized, $matches) === 1) {
            $value = (int) $matches[1];
            $month = $value % 100;

            if ($month >= 1 && $month <= 12) {
                return $value;
            }
        }

        return null;
    }

    public static function fromDate(?string $value): ?int
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            $date = Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }

        return (int) $date->format('Ym');
    }

    public static function toInputValue(?int $value): string
    {
        if ($value === null || $value < 101) {
            return '';
        }

        $year = intdiv($value, 100);
        $month = $value % 100;

        if ($month < 1 || $month > 12) {
            return '';
        }

        return sprintf('%04d-%02d', $year, $month);
    }

    public static function format(?int $value): string
    {
        if ($value === null || $value < 101) {
            return '—';
        }

        $year = intdiv($value, 100);
        $month = $value % 100;

        if ($month < 1 || $month > 12) {
            return '—';
        }

        return sprintf('%04d/%02d', $year, $month);
    }

    public static function formatShort(?int $value): string
    {
        if ($value === null || $value < 101) {
            return '—';
        }

        $year = intdiv($value, 100) % 100;
        $month = $value % 100;

        if ($month < 1 || $month > 12) {
            return '—';
        }

        return sprintf('%d/%d', $year, $month);
    }

    public static function isValid(?int $value): bool
    {
        if ($value === null || $value < 101) {
            return false;
        }

        $month = $value % 100;

        return $month >= 1 && $month <= 12;
    }

    public static function addMonths(int $value, int $months): int
    {
        if (! self::isValid($value)) {
            return $value;
        }

        $year = intdiv($value, 100);
        $month = $value % 100;

        return (int) Carbon::create($year, $month, 1)->addMonths($months)->format('Ym');
    }
}
