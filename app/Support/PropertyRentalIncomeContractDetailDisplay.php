<?php

namespace App\Support;

use App\Models\PropertyRentalIncome;
use App\Models\PropertyRentalIncomeTermination;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final class PropertyRentalIncomeContractDetailDisplay
{
    /**
     * @param  EloquentCollection<int, PropertyRentalIncome>  $records
     * @return array{
     *     isTerminatedPeriod: bool,
     *     periodRecords: EloquentCollection<int, PropertyRentalIncome>,
     *     paidRecords: EloquentCollection<int, PropertyRentalIncome>,
     *     nextPaymentRecord: ?PropertyRentalIncome,
     *     remainingUnpaidRecords: EloquentCollection<int, PropertyRentalIncome>,
     * }
     */
    public static function layout(
        EloquentCollection $records,
        ?PropertyRentalIncomeTermination $termination = null,
        $contractStartOn = null,
    ): array {
        // 解約済み: 入金状況で分けず、契約開始月〜解約月を支払い月の降順で返す（同一月は1件）
        if ($termination !== null) {
            $periodRecords = PropertyRentalIncomeContract::filterRecordsThroughTerminationMonth(
                $records,
                $termination,
                $contractStartOn,
                true,
            )
                ->sortByDesc(fn (PropertyRentalIncome $record): string => self::sortKey($record))
                ->values();

            return [
                'isTerminatedPeriod' => true,
                'periodRecords' => new EloquentCollection($periodRecords->all()),
                'paidRecords' => new EloquentCollection,
                'nextPaymentRecord' => null,
                'remainingUnpaidRecords' => new EloquentCollection,
            ];
        }

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
            'isTerminatedPeriod' => false,
            'periodRecords' => new EloquentCollection($sorted->all()),
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
