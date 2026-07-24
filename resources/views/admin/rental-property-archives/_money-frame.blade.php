@php
    $canEditFields = (bool) ($canEdit ?? false);
    $feeChecks = [
        'has_management_fee' => '管理費',
        'has_key_money' => '礼金',
        'has_security_deposit' => '敷金',
        'has_security_deposit_extra' => '敷金積増',
        'has_amortization' => '償却金',
        'has_shikibiki' => '敷引',
        'has_guarantee_deposit' => '保証金',
        'has_initial_cost' => '初期費用',
        'has_other_fees' => 'その他諸費用',
    ];
@endphp

<section class="application-block rental-archive-detail__section rental-archive-money-frame">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">お金・駐車場等</h3>

    <div class="rental-archive-form-grid">
        <div class="rental-archive-form-col">
            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">月額賃料</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <input
                        type="number"
                        min="0"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="monthly_rent_major"
                        value="{{ $archive->monthly_rent_major }}"
                        @readonly(! $canEditFields)
                    >
                    <span>.</span>
                    <input
                        type="number"
                        min="0"
                        max="9"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="monthly_rent_minor"
                        value="{{ $archive->monthly_rent_minor }}"
                        @readonly(! $canEditFields)
                    >
                    <span>万円</span>
                </div>
            </div>

            @foreach ($feeChecks as $field => $label)
                <div class="rental-archive-form-row">
                    <div class="rental-archive-form-label">{{ $label }}</div>
                    <div class="rental-archive-form-value">
                        <label class="rental-archive-check">
                            <input
                                type="checkbox"
                                class="rental-archive-field"
                                data-field="{{ $field }}"
                                value="1"
                                @checked($archive->{$field})
                                @disabled(! $canEditFields)
                            >
                            <span>あり</span>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rental-archive-form-col">
            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">仲介手数料</div>
                <div class="rental-archive-form-value">
                    <div class="rental-archive-radio-group" role="radiogroup" aria-label="仲介手数料">
                        @foreach (\App\Models\RentalPropertyArchive::brokerageFeeOptions() as $option)
                            <label class="rental-archive-radio">
                                <input
                                    type="radio"
                                    class="rental-archive-field"
                                    name="brokerage_fee_{{ $archive->id }}"
                                    data-field="brokerage_fee"
                                    value="{{ $option }}"
                                    @checked($archive->brokerage_fee === $option)
                                    @disabled(! $canEditFields)
                                >
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">担保</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <label class="rental-archive-check">
                        <input
                            type="checkbox"
                            class="rental-archive-field"
                            data-field="collateral_required"
                            value="1"
                            @checked($archive->collateral_required)
                            @disabled(! $canEditFields)
                        >
                        <span>要</span>
                    </label>
                    <input
                        type="number"
                        min="0"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="collateral_amount_major"
                        value="{{ $archive->collateral_amount_major }}"
                        @readonly(! $canEditFields)
                    >
                    <span>.</span>
                    <input
                        type="number"
                        min="0"
                        max="9"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="collateral_amount_minor"
                        value="{{ $archive->collateral_amount_minor }}"
                        @readonly(! $canEditFields)
                    >
                    <span>万円</span>
                    <input
                        type="number"
                        min="0"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="collateral_years"
                        value="{{ $archive->collateral_years }}"
                        @readonly(! $canEditFields)
                    >
                    <span>年</span>
                </div>
            </div>

            <div class="rental-archive-form-row rental-archive-form-row--transit">
                <div class="rental-archive-form-label">契約期間</div>
                <div class="rental-archive-form-value rental-archive-form-value--stack">
                    <div class="rental-archive-radio-group" role="radiogroup" aria-label="契約期間区分">
                        @foreach (\App\Models\RentalPropertyArchive::contractLeaseTypes() as $option)
                            <label class="rental-archive-radio">
                                <input
                                    type="radio"
                                    class="rental-archive-field"
                                    name="contract_lease_type_{{ $archive->id }}"
                                    data-field="contract_lease_type"
                                    value="{{ $option }}"
                                    @checked($archive->contract_lease_type === $option)
                                    @disabled(! $canEditFields)
                                >
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="rental-archive-form-value--wrap">
                        <div class="rental-archive-radio-group" role="radiogroup" aria-label="契約期間指定">
                            @foreach (\App\Models\RentalPropertyArchive::contractPeriodTypes() as $option)
                                <label class="rental-archive-radio">
                                    <input
                                        type="radio"
                                        class="rental-archive-field"
                                        name="contract_period_type_{{ $archive->id }}"
                                        data-field="contract_period_type"
                                        value="{{ $option }}"
                                        @checked($archive->contract_period_type === $option)
                                        @disabled(! $canEditFields)
                                    >
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                        <input
                            type="number"
                            min="0"
                            class="rental-archive-field rental-archive-input-sm"
                            data-field="contract_years"
                            value="{{ $archive->contract_years }}"
                            @readonly(! $canEditFields)
                        >
                        <span>年</span>
                        <input
                            type="number"
                            min="0"
                            max="11"
                            class="rental-archive-field rental-archive-input-sm"
                            data-field="contract_months"
                            value="{{ $archive->contract_months }}"
                            @readonly(! $canEditFields)
                        >
                        <span>ヶ月</span>
                    </div>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">保証会社</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input
                            type="checkbox"
                            class="rental-archive-field"
                            data-field="has_guarantor_company"
                            value="1"
                            @checked($archive->has_guarantor_company)
                            @disabled(! $canEditFields)
                        >
                        <span>あり</span>
                    </label>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">駐車場</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input
                            type="checkbox"
                            class="rental-archive-field"
                            data-field="has_parking"
                            value="1"
                            @checked($archive->has_parking)
                            @disabled(! $canEditFields)
                        >
                        <span>あり</span>
                    </label>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">特優賃</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input
                            type="checkbox"
                            class="rental-archive-field"
                            data-field="has_tokuyu_chin"
                            value="1"
                            @checked($archive->has_tokuyu_chin)
                            @disabled(! $canEditFields)
                        >
                        <span>あり</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</section>
