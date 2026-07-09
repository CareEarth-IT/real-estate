@php
    $paymentStatusClass = fn (?string $status): string => 'rental-income-block--' . ($status ?: 'unpaid');
    $showAllDisplayHints = $showAllDisplayHints ?? false;
@endphp

<div class="rental-income-blocks-grid">
    @foreach ($contractBlocks as $block)
        @php
            $record = $block['record'];
            $status = $record->payment_status ?? 'unpaid';
            $detailUrl = route('property.rental-income.contract.show', array_filter([
                'contract' => $block['key'],
                'contractor' => $block['contractor'],
                'property_name' => $block['property_name'],
                'month' => $activePaymentMonth ?? $record->payment_month ?? null,
            ]));
        @endphp
        <a
            href="{{ $detailUrl }}"
            class="rental-income-block {{ $paymentStatusClass($status) }}"
            aria-label="{{ ($block['contractor'] ?: '契約者未設定') }}の契約詳細を表示"
        >
            <div class="rental-income-block__header">
                <h3 class="rental-income-block__contractor">{{ $block['contractor'] ?: '（契約者未設定）' }}</h3>
                <span class="rental-income-block__status">
                    {{ $paymentStatusLabels[$status] ?? $status }}
                </span>
            </div>

            @if ($showAllDisplayHints && ($block['showing_next_payment'] ?? false))
                <p class="rental-income-block__next-payment-badge">次回支払い分を表示中</p>
            @endif

            <dl class="rental-income-block__details">
                <div class="rental-income-block__row">
                    <dt>物件</dt>
                    <dd>{{ $block['property_name'] ?: '—' }}</dd>
                </div>
                <div class="rental-income-block__row">
                    <dt>家賃</dt>
                    <dd>{{ $record->rent_amount !== null ? number_format($record->rent_amount).'円' : '—' }}</dd>
                </div>
                <div class="rental-income-block__row">
                    <dt>支払日</dt>
                    <dd>{{ $record->payment_on?->format('Y/m/d') ?? '—' }}</dd>
                </div>
                <div class="rental-income-block__row">
                    <dt>入居者</dt>
                    <dd>{{ $record->occupant_count !== null ? $record->occupant_count.'人' : '—' }}</dd>
                </div>
                @if ($block['contract_start_on'] && $block['contract_end_on'])
                <div class="rental-income-block__row">
                    <dt>契約開始</dt>
                    <dd>{{ $block['contract_start_on']->format('Y/m/d') }}</dd>
                </div>
                <div class="rental-income-block__row">
                    <dt>契約満了</dt>
                    <dd>{{ $block['contract_end_on']->format('Y/m/d') }}</dd>
                </div>
                @endif
            </dl>

            <p class="rental-income-block__hint">クリックで契約期間の一覧を表示</p>
        </a>
    @endforeach
</div>
