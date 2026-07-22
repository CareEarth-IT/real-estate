<?php

namespace App\Support;

use App\Models\PropertyRentalIncome;
use App\Models\PropertyRentalIncomeTermination;
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
                $termination = self::terminationForContract($key);
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

                if ($termination?->terminated_on) {
                    $period['end'] = $termination->terminated_on;
                }

                return [
                    'key' => $key,
                    'contractor' => $record->contractor,
                    'property_name' => $record->property_name,
                    'record' => $record,
                    'contract_start_on' => $period['start'],
                    'contract_end_on' => $period['end'],
                    'termination' => $termination,
                    'is_terminated' => $termination !== null,
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

    public static function terminationForContract(string $contractKey): ?PropertyRentalIncomeTermination
    {
        if ($contractKey === '') {
            return null;
        }

        return PropertyRentalIncomeTermination::query()
            ->where('contract_key', $contractKey)
            ->first();
    }

    /**
     * 解約月（YYYYMM）。terminated_on が無い場合は terminated_at から補完する。
     */
    public static function terminationCutoffMonth(?PropertyRentalIncomeTermination $termination): ?int
    {
        if ($termination === null) {
            return null;
        }

        $date = $termination->terminated_on ?? $termination->terminated_at;

        if ($date === null) {
            return null;
        }

        return (int) $date->format('Ym');
    }

    /**
     * 支払い月から契約開始月（YYYYMM）を推定する。
     *
     * @param  EloquentCollection<int, PropertyRentalIncome>  $records
     */
    public static function contractStartMonth(
        EloquentCollection $records,
        ?Carbon $contractStartOn = null,
    ): ?int {
        if ($contractStartOn !== null) {
            return (int) $contractStartOn->format('Ym');
        }

        $months = $records
            ->map(static fn (PropertyRentalIncome $record): int => (int) ($record->payment_month ?? 0))
            ->filter(static fn (int $month): bool => $month > 0)
            ->sort()
            ->values();

        return $months->isNotEmpty() ? (int) $months->first() : null;
    }

    /**
     * 契約開始月〜解約月までの月次を、入金状況に関係なく残す。
     * 同一支払い月が複数ある場合は1件にまとめる。
     *
     * @param  EloquentCollection<int, PropertyRentalIncome>  $records
     * @return EloquentCollection<int, PropertyRentalIncome>
     */
    public static function filterRecordsThroughTerminationMonth(
        EloquentCollection $records,
        ?PropertyRentalIncomeTermination $termination,
        ?Carbon $contractStartOn = null,
        bool $deleteDuplicateMonths = false,
    ): EloquentCollection {
        $cutoffMonth = self::terminationCutoffMonth($termination);

        if ($cutoffMonth === null) {
            return $records;
        }

        $startMonth = self::contractStartMonth($records, $contractStartOn);

        $filtered = $records
            ->filter(static function (PropertyRentalIncome $record) use ($startMonth, $cutoffMonth): bool {
                $month = (int) ($record->payment_month ?? 0);

                if ($month <= 0 || $month > $cutoffMonth) {
                    return false;
                }

                if ($startMonth !== null && $month < $startMonth) {
                    return false;
                }

                return true;
            })
            ->values();

        return self::dedupeRecordsByPaymentMonth($filtered, $deleteDuplicateMonths);
    }

    /**
     * 同一支払い月は1件にまとめる（解約日など注記の二重表示を防ぐ）。
     * 優先: 納金済 > 値の充実度 > 新しいID。
     *
     * @param  EloquentCollection<int, PropertyRentalIncome>  $records
     * @return EloquentCollection<int, PropertyRentalIncome>
     */
    public static function dedupeRecordsByPaymentMonth(
        EloquentCollection $records,
        bool $deleteDuplicates = false,
    ): EloquentCollection {
        $grouped = $records->groupBy(
            static fn (PropertyRentalIncome $record): int => (int) ($record->payment_month ?? 0),
        );

        $keepers = collect();
        $duplicateIds = [];

        foreach ($grouped as $month => $group) {
            if ((int) $month <= 0) {
                continue;
            }

            /** @var EloquentCollection<int, PropertyRentalIncome> $group */
            $sorted = $group
                ->sortByDesc(static fn (PropertyRentalIncome $record): string => self::dedupePreferenceKey($record))
                ->values();

            $keeper = $sorted->first();
            if ($keeper === null) {
                continue;
            }

            $keepers->push($keeper);

            foreach ($sorted->slice(1) as $duplicate) {
                if ($duplicate->id) {
                    $duplicateIds[] = $duplicate->id;
                }
            }
        }

        if ($deleteDuplicates && $duplicateIds !== []) {
            PropertyRentalIncome::query()->whereIn('id', $duplicateIds)->delete();
        }

        return new EloquentCollection(
            $keepers
                ->sortBy(static fn (PropertyRentalIncome $record): int => (int) ($record->payment_month ?? 0))
                ->values()
                ->all()
        );
    }

    private static function dedupePreferenceKey(PropertyRentalIncome $record): string
    {
        $statusRank = match ($record->payment_status ?? 'unpaid') {
            'paid' => 4,
            'temporary' => 3,
            'overdue' => 2,
            default => 1,
        };

        $filled = collect([
            $record->rent_amount,
            $record->payment_method,
            $record->occupant_count,
            $record->deposit_amount,
            $record->payment_on,
            $record->rent_year_month,
        ])->filter(static fn ($value): bool => $value !== null && $value !== '')->count();

        return sprintf('%d-%02d-%010d', $statusRank, $filled, (int) $record->id);
    }

    /**
     * 解約月より後の月次のみ削除する（契約開始月〜解約月・入金状況不問で残す）。
     *
     * @param  EloquentCollection<int, PropertyRentalIncome>  $records
     */
    public static function deleteMonthsAfterTermination(
        EloquentCollection $records,
        int $cutoffMonth,
    ): int {
        $idsToDelete = $records
            ->filter(static function (PropertyRentalIncome $record) use ($cutoffMonth): bool {
                $month = (int) ($record->payment_month ?? 0);

                return $month === 0 || $month > $cutoffMonth;
            })
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        if ($idsToDelete === []) {
            return 0;
        }

        return PropertyRentalIncome::query()->whereIn('id', $idsToDelete)->delete();
    }

    /**
     * 既存の解約済み契約について、解約月より後が残っていれば削除する。
     */
    public static function pruneAllTerminatedContracts(): int
    {
        $deleted = 0;

        foreach (PropertyRentalIncomeTermination::query()->orderBy('id')->get() as $termination) {
            if ($termination->terminated_on === null && $termination->terminated_at !== null) {
                $termination->terminated_on = $termination->terminated_at->toDateString();
                $termination->save();
            }

            $cutoffMonth = self::terminationCutoffMonth($termination);

            if ($cutoffMonth === null) {
                continue;
            }

            $records = self::recordsForContract(
                $termination->contract_key,
                $termination->contractor,
                $termination->property_name,
            );

            $deleted += self::deleteMonthsAfterTermination($records, $cutoffMonth);

            $records = self::recordsForContract(
                $termination->contract_key,
                $termination->contractor,
                $termination->property_name,
            );

            // 同一支払い月の重複を1件に整理
            $beforeIds = $records->pluck('id')->filter()->all();
            $kept = self::dedupeRecordsByPaymentMonth($records, true);
            $keptIds = $kept->pluck('id')->filter()->all();
            $deleted += max(0, count($beforeIds) - count($keptIds));
        }

        return $deleted;
    }

    /**
     * @return list<string>
     */
    public static function terminatedContractKeys(): array
    {
        return PropertyRentalIncomeTermination::query()
            ->pluck('contract_key')
            ->filter(static fn ($key): bool => is_string($key) && $key !== '')
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $blocks
     * @return list<array<string, mixed>>
     */
    public static function excludeTerminatedBlocks(array $blocks): array
    {
        $terminatedKeys = array_flip(self::terminatedContractKeys());

        if ($terminatedKeys === []) {
            return $blocks;
        }

        return array_values(array_filter(
            $blocks,
            static fn (array $block): bool => ! isset($terminatedKeys[$block['key'] ?? '']),
        ));
    }

    /**
     * @return list<array{
     *     key: string,
     *     contractor: ?string,
     *     property_name: ?string,
     *     record: PropertyRentalIncome,
     *     contract_start_on: ?Carbon,
     *     contract_end_on: ?Carbon,
     *     termination: PropertyRentalIncomeTermination,
     *     is_terminated: true,
     * }>
     */
    public static function terminatedBlocks(string $search = ''): array
    {
        $terminations = PropertyRentalIncomeTermination::query()
            ->orderByDesc('terminated_on')
            ->orderByDesc('id')
            ->get();

        $blocks = [];

        foreach ($terminations as $termination) {
            $contractor = $termination->contractor;
            $propertyName = $termination->property_name;

            if ($search !== '') {
                $haystack = mb_strtolower(($contractor ?? '').' '.($propertyName ?? ''));
                if (! str_contains($haystack, mb_strtolower($search))) {
                    continue;
                }
            }

            $records = self::recordsForContract(
                $termination->contract_key,
                $contractor,
                $propertyName,
            );

            $cutoffMonth = self::terminationCutoffMonth($termination);
            if ($cutoffMonth !== null) {
                self::deleteMonthsAfterTermination($records, $cutoffMonth);
                $records = self::recordsForContract(
                    $termination->contract_key,
                    $contractor,
                    $propertyName,
                );
            }

            $period = $records->isNotEmpty()
                ? self::resolvePeriodForRecords($records)
                : [
                    'start' => null,
                    'end' => $termination->terminated_on ?? $termination->terminated_at,
                ];

            $terminationEnd = $termination->terminated_on ?? $termination->terminated_at;
            if ($terminationEnd !== null) {
                $period['end'] = $terminationEnd instanceof Carbon
                    ? $terminationEnd->copy()->startOfDay()
                    : Carbon::parse($terminationEnd)->startOfDay();
            }

            // 契約開始月〜解約月（入金状況不問）
            $records = self::filterRecordsThroughTerminationMonth(
                $records,
                $termination,
                $period['start'] ?? null,
            );

            if ($records->isNotEmpty()) {
                $period = self::resolvePeriodForRecords($records);
                if ($terminationEnd !== null) {
                    $period['end'] = $terminationEnd instanceof Carbon
                        ? $terminationEnd->copy()->startOfDay()
                        : Carbon::parse($terminationEnd)->startOfDay();
                }
            }

            $record = null;
            if ($cutoffMonth !== null) {
                $record = $records->first(
                    static fn (PropertyRentalIncome $item): bool => (int) ($item->payment_month ?? 0) === $cutoffMonth,
                );
            }
            $record ??= $records
                ->sortByDesc(static fn (PropertyRentalIncome $item): int => self::recordSortValue($item))
                ->first();

            if ($record === null) {
                $record = new PropertyRentalIncome([
                    'contractor' => $contractor,
                    'property_name' => $propertyName,
                    'payment_status' => 'unpaid',
                    'payment_month' => $cutoffMonth,
                ]);
            }

            $blocks[] = [
                'key' => $termination->contract_key,
                'contractor' => $contractor,
                'property_name' => $propertyName,
                'record' => $record,
                'contract_start_on' => $period['start'],
                'contract_end_on' => $period['end'],
                'termination' => $termination,
                'is_terminated' => true,
            ];
        }

        return $blocks;
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
