<?php

namespace App\Support;

use App\Models\PropertyRentalIncome;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

final class PropertyRentalIncomeContract
{
    public static function key(?string $contractor, ?string $propertyName): string
    {
        $payload = "\0".trim((string) $contractor)."\0".trim((string) $propertyName);

        return hash('sha256', $payload);
    }

    /**
     * @param  EloquentCollection<int, PropertyRentalIncome>  $monthRecords
     * @return list<array{
     *     key: string,
     *     contractor: ?string,
     *     property_name: ?string,
     *     record: PropertyRentalIncome,
     *     contract_start_on: ?Carbon,
     *     contract_end_on: ?Carbon,
     * }>
     */
    public static function blocksFromMonthRecords(EloquentCollection $monthRecords): array
    {
        /** @var Collection<string, PropertyRentalIncome> $representatives */
        $representatives = collect();

        foreach ($monthRecords as $record) {
            $key = self::resolveRecordKey($record);

            if (! $representatives->has($key)) {
                $representatives->put($key, $record);

                continue;
            }

            $current = $representatives->get($key);
            $currentSort = self::recordSortValue($current);
            $nextSort = self::recordSortValue($record);

            if ($nextSort > $currentSort) {
                $representatives->put($key, $record);
            }
        }

        return $representatives
            ->map(function (PropertyRentalIncome $record): array {
                $key = self::resolveRecordKey($record);
                $period = ($record->contract_start_on && $record->contract_end_on)
                    ? [
                        'start' => $record->contract_start_on,
                        'end' => $record->contract_end_on,
                    ]
                    : self::resolvePeriodForRecords(self::recordsForContract(
                        $key,
                        $record->contractor,
                        $record->property_name,
                    ));

                return [
                    'key' => $key,
                    'contractor' => $record->contractor,
                    'property_name' => $record->property_name,
                    'record' => $record,
                    'contract_start_on' => $period['start'],
                    'contract_end_on' => $period['end'],
                ];
            })
            ->values()
            ->sortBy(
                fn (array $block): string => mb_strtolower((string) ($block['contractor'] ?? '')),
                SORT_NATURAL,
            )
            ->values()
            ->all();
    }

    /** @return EloquentCollection<int, PropertyRentalIncome> */
    public static function recordsForContract(
        string $contractKey,
        ?string $contractor = null,
        ?string $propertyName = null,
    ): EloquentCollection {
        $query = PropertyRentalIncome::query()
            ->orderBy('payment_month')
            ->orderBy('payment_on')
            ->orderBy('id');

        if (Schema::hasColumn('property_rental_incomes', 'contract_key')) {
            $byKey = (clone $query)->where('contract_key', $contractKey)->get();

            if ($byKey->isNotEmpty()) {
                return $byKey;
            }
        }

        return $query
            ->where('contractor', $contractor ?? '')
            ->where('property_name', $propertyName ?? '')
            ->get();
    }

    /**
     * @param  EloquentCollection<int, PropertyRentalIncome>|Collection<int, PropertyRentalIncome>  $records
     * @return array{start: ?Carbon, end: ?Carbon, is_stored: bool}
     */
    public static function resolvePeriodForRecords(EloquentCollection|Collection $records): array
    {
        $storedStart = $records
            ->pluck('contract_start_on')
            ->filter()
            ->sort()
            ->first();
        $storedEnd = $records
            ->pluck('contract_end_on')
            ->filter()
            ->sort()
            ->last();

        if ($storedStart !== null && $storedEnd !== null) {
            return [
                'start' => Carbon::parse($storedStart),
                'end' => Carbon::parse($storedEnd),
                'is_stored' => true,
            ];
        }

        $months = $records
            ->pluck('payment_month')
            ->filter(static fn ($month): bool => YearMonth::isValid((int) $month))
            ->map(static fn ($month): int => (int) $month)
            ->sort()
            ->values();

        if ($months->isEmpty()) {
            return ['start' => null, 'end' => null, 'is_stored' => false];
        }

        return [
            'start' => Carbon::parse(YearMonth::firstDay($months->first())),
            'end' => Carbon::parse(YearMonth::lastDay($months->last())),
            'is_stored' => false,
        ];
    }

    /**
     * @param  array{start: ?Carbon, end: ?Carbon, is_stored?: bool}  $period
     */
    public static function formatContractPeriodLabel(array $period): ?string
    {
        if (($period['start'] ?? null) === null || ($period['end'] ?? null) === null) {
            return null;
        }

        return $period['start']->format('Y/m/d').' ～ '.$period['end']->format('Y/m/d');
    }

    public static function syncContractMetadata(PropertyRentalIncome $record): void
    {
        if (! Schema::hasColumn($record->getTable(), 'contract_key')) {
            return;
        }

        $record->contract_key = self::key($record->contractor, $record->property_name);
    }

    public static function recordKey(PropertyRentalIncome $record): string
    {
        return self::resolveRecordKey($record);
    }

    private static function resolveRecordKey(PropertyRentalIncome $record): string
    {
        if (is_string($record->contract_key) && $record->contract_key !== '') {
            return $record->contract_key;
        }

        return self::key($record->contractor, $record->property_name);
    }

    private static function recordSortValue(PropertyRentalIncome $record): int
    {
        $paymentMonth = $record->payment_month ?? 0;
        $paymentOn = $record->payment_on?->format('Ymd') ?? '00000000';
        $createdOn = $record->created_on?->format('Ymd') ?? '00000000';

        return (int) ($paymentMonth.$paymentOn.$createdOn.$record->id);
    }
}
