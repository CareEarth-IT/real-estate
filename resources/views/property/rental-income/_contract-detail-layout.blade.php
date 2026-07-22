@php
    use App\Support\PropertyRentalIncomeContractDetailDisplay;
    use App\Support\YearMonth;

    $paymentStatusClass = fn (?string $status): string => 'rental-income-status-' . ($status ?: 'unpaid');
    $layout = PropertyRentalIncomeContractDetailDisplay::layout(
        $records,
        $termination ?? null,
        $contractStartOn ?? null,
    );
    $isTerminatedPeriod = $layout['isTerminatedPeriod'];
    $periodRecords = $layout['periodRecords'];
    $paidRecords = $layout['paidRecords'];
    $nextPaymentRecord = $layout['nextPaymentRecord'];
    $remainingUnpaidRecords = $layout['remainingUnpaidRecords'];

    $periodMonthRange = function () use ($periodRecords): ?string {
        if ($periodRecords->isEmpty()) {
            return null;
        }

        $months = $periodRecords
            ->map(fn ($record) => PropertyRentalIncomeContractDetailDisplay::recordMonth($record))
            ->filter(fn (int $month): bool => $month > 0)
            ->sort()
            ->values();

        if ($months->isEmpty()) {
            return null;
        }

        if ($months->count() === 1) {
            return YearMonth::formatShort($months->first());
        }

        return YearMonth::formatShort($months->first()).' ～ '.YearMonth::formatShort($months->last());
    };

    $paidMonthRange = function () use ($paidRecords): ?string {
        if ($paidRecords->isEmpty()) {
            return null;
        }

        $months = $paidRecords
            ->map(fn ($record) => PropertyRentalIncomeContractDetailDisplay::recordMonth($record))
            ->filter(fn (int $month): bool => $month > 0)
            ->sort()
            ->values();

        if ($months->isEmpty()) {
            return null;
        }

        return YearMonth::formatShort($months->first()).' ～ '.YearMonth::formatShort($months->last());
    };
@endphp

<div class="rental-income-contract-detail">
    @if ($isTerminatedPeriod)
        <section class="rental-income-period-section" aria-label="契約開始月から解約月">
            <h3 class="rental-income-unpaid-section__title">
                契約開始月〜解約月（{{ $periodRecords->count() }} 件）
                @if ($range = $periodMonthRange())
                    <span class="font-normal text-slate-500">{{ $range }}</span>
                @endif
            </h3>
            <p class="mb-3 text-xs text-slate-500">未納・納金済など入金状況に関係なく、契約開始月から解約月までのデータを表示しています。同一支払い月は1件にまとめています。</p>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="admin-table-scroll overflow-x-auto">
                    <table class="admin-table-sticky rental-income-table min-w-full text-sm text-left">
                        @include('property.rental-income._contract-detail-table-head', ['size' => 'default'])
                        <tbody>
                            @forelse ($periodRecords as $record)
                                @include('property.rental-income._contract-detail-row', [
                                    'record' => $record,
                                    'size' => 'default',
                                    'paymentStatusClass' => $paymentStatusClass,
                                    'termination' => $termination ?? null,
                                    'showTerminationDateNote' => true,
                                ])
                            @empty
                                <tr>
                                    <td colspan="{{ ($canEdit ?? false) ? 9 : 8 }}" class="px-3 py-6 text-center text-slate-500">
                                        表示するデータがありません
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @else
        @if ($paidRecords->isNotEmpty())
            <details class="rental-income-paid-fold">
                <summary class="rental-income-paid-fold__summary">
                    <span class="rental-income-paid-fold__label">納金済み {{ $paidRecords->count() }} 件</span>
                    @if ($range = $paidMonthRange())
                        <span class="rental-income-paid-fold__range">{{ $range }}</span>
                    @endif
                    <span class="rental-income-paid-fold__hint">クリックで展開</span>
                </summary>
                <div class="rental-income-paid-fold__body bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="admin-table-scroll overflow-x-auto">
                        <table class="admin-table-sticky rental-income-table rental-income-table--compact min-w-full text-left">
                            @include('property.rental-income._contract-detail-table-head', ['size' => 'compact'])
                            <tbody>
                                @foreach ($paidRecords as $record)
                                    @include('property.rental-income._contract-detail-row', [
                                        'record' => $record,
                                        'size' => 'compact',
                                        'paymentStatusClass' => $paymentStatusClass,
                                        'termination' => $termination ?? null,
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </details>
        @endif

        @if ($nextPaymentRecord)
            <section class="rental-income-next-payment-section" aria-label="次回支払い">
                <h3 class="rental-income-next-payment-section__title">次回支払い</h3>
                <div class="rental-income-next-payment-section__card bg-white rounded-xl border-2 border-primary-300 shadow-md overflow-hidden">
                    <div class="admin-table-scroll overflow-x-auto">
                        <table class="admin-table-sticky rental-income-table rental-income-table--featured min-w-full text-left">
                            @include('property.rental-income._contract-detail-table-head', ['size' => 'featured'])
                            <tbody>
                                @include('property.rental-income._contract-detail-row', [
                                    'record' => $nextPaymentRecord,
                                    'size' => 'featured',
                                    'paymentStatusClass' => $paymentStatusClass,
                                    'termination' => $termination ?? null,
                                    'showTerminateAction' => true,
                                ])
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        @if ($remainingUnpaidRecords->isNotEmpty())
            <section class="rental-income-unpaid-section" aria-label="未納・その他">
                <h3 class="rental-income-unpaid-section__title">未納・その他（{{ $remainingUnpaidRecords->count() }} 件）</h3>
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="admin-table-scroll overflow-x-auto">
                        <table class="admin-table-sticky rental-income-table min-w-full text-sm text-left">
                            @include('property.rental-income._contract-detail-table-head', ['size' => 'default'])
                            <tbody>
                                @foreach ($remainingUnpaidRecords as $record)
                                    @include('property.rental-income._contract-detail-row', [
                                        'record' => $record,
                                        'size' => 'default',
                                        'paymentStatusClass' => $paymentStatusClass,
                                        'termination' => $termination ?? null,
                                        'showTerminateAction' => true,
                                    ])
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif
    @endif
</div>
