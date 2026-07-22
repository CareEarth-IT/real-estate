<?php

namespace App\Support;

use App\Models\PropertyRentalIncome;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class PropertyRentalIncomeAllDisplay
{
    /** @var list<string> */
    private const OUTSTANDING_STATUSES = ['unpaid', 'temporary', 'overdue'];

    private const ADVANCE_DAYS = 15;

    /**
     * @return array{
     *     contractBlocks: list<array<string, mixed>>,
     *     upcomingPaymentCount: int,
     *     referenceDate: Carbon,
     *     displayMonth: int,
     * }
     */
    public static function resolve(
        string $search = '',
        ?string $paymentStatusFilter = null,
        ?Carbon $today = null,
    ): array {
        $today = ($today ?? now())->copy()->startOfDay();
        $currentMonth = (int) $today->format('Ym');

        /** @var Collection<string, EloquentCollection<int, PropertyRentalIncome>> $grouped */
        $grouped = PropertyRentalIncome::query()
            ->orderBy('payment_on')
            ->orderBy('payment_month')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (PropertyRentalIncome $record): string => PropertyRentalIncomeContract::recordKey($record));

        $blocks = [];
        $upcomingPaymentCount = 0;

        foreach ($grouped as $contractKey => $records) {
            $resolution = self::resolveDisplayRecord($records, $today, $currentMonth);
            $record = $resolution['record'];

            if ($record === null) {
                continue;
            }

            if ($resolution['in_advance_window']) {
                $upcomingPaymentCount++;
            }

            if ($search !== '' && ! self::matchesSearch($record, $search)) {
                continue;
            }

            if ($paymentStatusFilter !== null && ($record->payment_status ?? 'unpaid') !== $paymentStatusFilter) {
                continue;
            }

            $period = ($record->contract_start_on && $record->contract_end_on)
                ? [
                    'start' => $record->contract_start_on,
                    'end' => $record->contract_end_on,
                ]
                : PropertyRentalIncomeContract::resolvePeriodForRecords(
                    PropertyRentalIncomeContract::recordsForContract(
                        $contractKey,
                        $record->contractor,
                        $record->property_name,
                    ),
                );

            $termination = PropertyRentalIncomeContract::terminationForContract($contractKey);

            if ($termination?->terminated_on) {
                $period['end'] = $termination->terminated_on;
            }

            $blocks[] = [
                'key' => $contractKey,
                'contractor' => $record->contractor,
                'property_name' => $record->property_name,
                'record' => $record,
                'contract_start_on' => $period['start'],
                'contract_end_on' => $period['end'],
                'termination' => $termination,
                'is_terminated' => $termination !== null,
                'showing_next_payment' => $resolution['showing_next_payment'],
                'in_advance_window' => $resolution['in_advance_window'],
            ];
        }

        usort($blocks, static fn (array $a, array $b): int => strnatcasecmp(
            (string) ($a['contractor'] ?? ''),
            (string) ($b['contractor'] ?? ''),
        ));

        return [
            'contractBlocks' => $blocks,
            'upcomingPaymentCount' => $upcomingPaymentCount,
            'referenceDate' => $today,
            'displayMonth' => $currentMonth,
        ];
    }

    /**
     * @param  EloquentCollection<int, PropertyRentalIncome>|Collection<int, PropertyRentalIncome>  $records
     * @return array{
     *     record: ?PropertyRentalIncome,
     *     in_advance_window: bool,
     *     showing_next_payment: bool,
     * }
     */
    private static function resolveDisplayRecord(
        EloquentCollection|Collection $records,
        Carbon $today,
        int $currentMonth,
    ): array {
        $sorted = $records
            ->sortBy(fn (PropertyRentalIncome $record): string => self::sortKey($record))
            ->values();

        if ($sorted->isEmpty()) {
            return [
                'record' => null,
                'in_advance_window' => false,
                'showing_next_payment' => false,
            ];
        }

        $startOfMonth = $today->copy()->startOfMonth();

        $oldestOutstanding = $sorted->first(
            fn (PropertyRentalIncome $record): bool => self::isOutstanding($record)
                && self::isOlderThanCurrentMonth($record, $startOfMonth, $currentMonth),
        );

        if ($oldestOutstanding !== null) {
            return [
                'record' => $oldestOutstanding,
                'in_advance_window' => false,
                'showing_next_payment' => false,
            ];
        }

        $candidate = self::recordForMonth($sorted, $currentMonth)
            ?? $sorted
                ->filter(fn (PropertyRentalIncome $record): bool => self::recordMonth($record) <= $currentMonth)
                ->last()
            ?? $sorted->first();

        $showingNextPayment = false;

        if ($candidate !== null && ($candidate->payment_status ?? 'unpaid') === 'paid') {
            $next = self::nextRecordAfter($sorted, $candidate);
            if ($next !== null) {
                $candidate = $next;
                $showingNextPayment = true;
            }
        }

        $inAdvanceWindow = false;

        if ($candidate?->payment_on !== null) {
            $paymentOn = $candidate->payment_on->copy()->startOfDay();
            $daysUntilPayment = $today->diffInDays($paymentOn, false);

            if ($daysUntilPayment >= 0 && $daysUntilPayment <= self::ADVANCE_DAYS) {
                $inAdvanceWindow = true;
                $next = self::nextRecordAfter($sorted, $candidate);

                if ($next !== null) {
                    $candidate = $next;
                    $showingNextPayment = true;
                }
            }
        }

        return [
            'record' => $candidate,
            'in_advance_window' => $inAdvanceWindow,
            'showing_next_payment' => $showingNextPayment,
        ];
    }

    /**
     * @param  Collection<int, PropertyRentalIncome>  $records
     */
    private static function recordForMonth(Collection $records, int $month): ?PropertyRentalIncome
    {
        return $records->first(
            fn (PropertyRentalIncome $record): bool => self::recordMonth($record) === $month,
        );
    }

    /**
     * @param  Collection<int, PropertyRentalIncome>  $records
     */
    private static function nextRecordAfter(Collection $records, PropertyRentalIncome $current): ?PropertyRentalIncome
    {
        $passed = false;

        foreach ($records as $record) {
            if ($passed) {
                return $record;
            }

            if ($record->id === $current->id) {
                $passed = true;
            }
        }

        return null;
    }

    private static function isOutstanding(PropertyRentalIncome $record): bool
    {
        return in_array($record->payment_status ?? 'unpaid', self::OUTSTANDING_STATUSES, true);
    }

    private static function isOlderThanCurrentMonth(
        PropertyRentalIncome $record,
        Carbon $startOfMonth,
        int $currentMonth,
    ): bool {
        if ($record->payment_on !== null && $record->payment_on->lt($startOfMonth)) {
            return true;
        }

        $recordMonth = self::recordMonth($record);

        return $recordMonth > 0 && $recordMonth < $currentMonth;
    }

    private static function recordMonth(PropertyRentalIncome $record): int
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

    private static function matchesSearch(PropertyRentalIncome $record, string $search): bool
    {
        $needle = mb_strtolower($search);

        return str_contains(mb_strtolower((string) $record->contractor), $needle)
            || str_contains(mb_strtolower((string) $record->property_name), $needle);
    }
}
