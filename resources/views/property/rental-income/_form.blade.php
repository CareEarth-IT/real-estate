@php
    use App\Support\YearMonth;

    $formAction = $isEdit
        ? route('property.rental-income.update', $record)
        : route('property.rental-income.store');
@endphp

<form method="post" action="{{ $formAction }}" class="entry-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" name="fallback_payment_month" value="{{ old('fallback_payment_month', $activePaymentMonth) }}">

    <section class="form-section form-section-clean">
        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="section-label mb-0 border-0 pb-0">家賃収入データ</h2>
            <p class="text-sm text-slate-500">
                支払日の年月で一覧の月に振り分けられます
            </p>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="created_on">作成日</label>
                <input
                    type="date"
                    id="created_on"
                    name="created_on"
                    value="{{ old('created_on', $record->created_on?->format('Y-m-d')) }}"
                >
            </div>

            <div class="form-group">
                <label for="contractor">契約者</label>
                <input
                    type="text"
                    id="contractor"
                    name="contractor"
                    value="{{ old('contractor', $record->contractor) }}"
                >
            </div>

            <div class="form-group">
                <label for="property_name">物件</label>
                <input
                    type="text"
                    id="property_name"
                    name="property_name"
                    value="{{ old('property_name', $record->property_name) }}"
                >
            </div>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="rent_year_month">家賃年月</label>
                <input
                    type="month"
                    id="rent_year_month"
                    name="rent_year_month"
                    value="{{ old('rent_year_month', YearMonth::toInputValue($record->rent_year_month)) }}"
                >
            </div>

            <div class="form-group">
                <label for="payment_method">入金方法</label>
                <select id="payment_method" name="payment_method">
                    <option value="">選択してください</option>
                    @foreach ($paymentMethods as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', $record->payment_method) === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="rent_amount">家賃</label>
                <input
                    type="number"
                    id="rent_amount"
                    name="rent_amount"
                    value="{{ old('rent_amount', $record->rent_amount) }}"
                >
            </div>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="payment_status">入金状況</label>
                <select id="payment_status" name="payment_status">
                    @foreach ($paymentStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_status', $record->payment_status ?? 'unpaid') === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="occupant_count">入居者人数</label>
                <input
                    type="number"
                    id="occupant_count"
                    name="occupant_count"
                    min="0"
                    max="255"
                    value="{{ old('occupant_count', $record->occupant_count) }}"
                >
            </div>

            <div class="form-group">
                <label for="deposit_amount">預り金</label>
                <input
                    type="number"
                    id="deposit_amount"
                    name="deposit_amount"
                    value="{{ old('deposit_amount', $record->deposit_amount) }}"
                >
            </div>
        </div>

        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="payment_on">支払日</label>
                <input
                    type="date"
                    id="payment_on"
                    name="payment_on"
                    value="{{ old('payment_on', $record->payment_on?->format('Y-m-d')) }}"
                >
            </div>
        </div>

        @if (! $isEdit && ($contractPeriodEnabled ?? false))
            <div class="rental-income-contract-period">
                <h3 class="rental-income-contract-period__title">契約期限 <span class="rental-income-contract-period__badge">お試し</span></h3>
                <p class="rental-income-contract-period__hint">
                    yyyy/mm/dd（契約開始日）～ yyyy/mm/dd（契約満了）を入力すると、期間内の各月分のデータを一括登録します。
                </p>
                <div class="form-row form-row-2 rental-income-contract-period__dates">
                    <div class="form-group">
                        <label for="contract_start_on">契約開始日</label>
                        <input
                            type="date"
                            id="contract_start_on"
                            name="contract_start_on"
                            value="{{ old('contract_start_on') }}"
                        >
                    </div>
                    <div class="form-group rental-income-contract-period__separator-wrap">
                        <span class="rental-income-contract-period__separator" aria-hidden="true">～</span>
                        <label for="contract_end_on">契約満了日</label>
                        <input
                            type="date"
                            id="contract_end_on"
                            name="contract_end_on"
                            value="{{ old('contract_end_on') }}"
                        >
                    </div>
                </div>
            </div>
        @endif

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
            <a href="{{ route('property.rental-income.index', ['month' => $returnMonth]) }}" class="btn btn-ghost">一覧へ</a>
        </div>
    </section>
</form>
