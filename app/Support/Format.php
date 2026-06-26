<?php

namespace App\Support;

use DateTime;
use DateTimeInterface;

class Format
{
    public static function formatYen(int $amount): string
    {
        return '¥'.number_format($amount);
    }

    public static function formatDateTime(DateTimeInterface|string|null $datetime): string
    {
        if (! $datetime) {
            return '—';
        }

        if ($datetime instanceof DateTimeInterface) {
            return $datetime->format('Y年n月j日 H:i');
        }

        return (new DateTime($datetime))->format('Y年n月j日 H:i');
    }
}
