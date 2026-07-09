<?php

namespace App\Support;

use App\Models\PropertyRentalIncome;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class PropertyRentalIncomeContractDetailDisplay
{
    /**
     * @param  EloquentCollection<int, PropertyRentalIncome>  $records
     * @return array{
     *     paidRecords: EloquentCollection<int, PropertyRentalIncome>,
     *     nextPaymentRecord: ?PropertyRentalIncome,
     *     remainingUnpaidRecords: EloquentCollection<int, PropertyRentalIncome>,
     * }
     */
    public static function layout(EloquentCollection $records): array
    {
        $sorted = $records
            ->sortBy(fn (PropertyRentalIncome $record): string => self::sortKey($record))
            ->values();

        $paid = $sorted->filter(
            fn (PropertyRentalIncome $record): bool => ($record->payment_status ?? 'unpaid') === 'paid',
        )->values();

        $unpaid = $sorted->filter(
            fn (PropertyRentalIncome $record): bool => ($record->payment_status ?? 'unpaid') !== 'paid',
        )->values();

        $nextPayment = $unpaid->first();

        $remainingUnpaid = $nextPayment !== null
            ? $unpaid->filter(fn (PropertyRentalIncome $record): bool => $record->id !== $nextPayment->id)->values()
            : new EloquentCollection;

        return [
            'paidRecords' => new EloquentCollection($paid->all()),
            'nextPaymentRecord' => $nextPayment,
            'remainingUnpaidRecords' => new EloquentCollection($remainingUnpaid->all()),
        ];
    }

    public static function recordMonth(PropertyRentalIncome $record): int
    {
        if ($record->payment_month) {
            return (int) $record->payment_month;
        }

        if ($record->payment_on !== null) {
            return (int) $record->payment_on->format('Ym');
        }

        return 0;
    }

    private static function sortKey(PropertyRentalIncome $record): string
    {
        $month = self::recordMonth($record);
        $paymentOn = $record->payment_on?->format('Ymd') ?? '00000000';

        return sprintf('%06d-%s-%06d', $month, $paymentOn, $record->id);
    }
}
