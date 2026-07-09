@php
    use App\Support\PropertyRentalIncomeContractDetailDisplay;
    use App\Support\YearMonth;

    $paymentStatusClass = fn (?string $status): string => 'rental-income-status-' . ($status ?: 'unpaid');
    $layout = PropertyRentalIncomeContractDetailDisplay::layout($records);
    $paidRecords = $layout['paidRecords'];
    $nextPaymentRecord = $layout['nextPaymentRecord'];
    $remainingUnpaidRecords = $layout['remainingUnpaidRecords'];

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
                                ])
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @endif
</div>
