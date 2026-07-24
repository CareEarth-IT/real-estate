@php
    $canEditFields = (bool) ($canEdit ?? false);
@endphp

<section class="application-block rental-archive-detail__section rental-archive-listing-frame">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">入居・取引態様・掲載指示</h3>

    <div class="rental-archive-form-grid">
        <div class="rental-archive-form-col">
            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">入居予定</div>
                <div class="rental-archive-form-value">
                    <div class="rental-archive-radio-group" role="radiogroup" aria-label="入居予定">
                        @foreach (\App\Models\RentalPropertyArchive::enumFieldOptions()['move_in_schedule'] as $option)
                            <label class="rental-archive-radio">
                                <input
                                    type="radio"
                                    class="rental-archive-field"
                                    name="move_in_schedule_{{ $archive->id }}"
                                    data-field="move_in_schedule"
                                    value="{{ $option }}"
                                    @checked($archive->move_in_schedule === $option)
                                    @disabled(! $canEditFields)
                                >
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">取引態様</div>
                <div class="rental-archive-form-value">
                    <select
                        class="rental-archive-field application-inline-field"
                        data-field="transaction_type"
                        @disabled(! $canEditFields)
                    >
                        <option value="">選択してください</option>
                        @foreach (\App\Models\RentalPropertyArchive::enumFieldOptions()['transaction_type'] as $option)
                            <option value="{{ $option }}" @selected($archive->transaction_type === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rental-archive-form-row rental-archive-form-row--blank">
                <div class="rental-archive-form-label">手数料率</div>
                <div class="rental-archive-form-value">
                    <span class="rental-archive-blank-note">（未設定）</span>
                </div>
            </div>

            <div class="rental-archive-form-row rental-archive-form-row--blank">
                <div class="rental-archive-form-label">広告料</div>
                <div class="rental-archive-form-value">
                    <span class="rental-archive-blank-note">（未設定）</span>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">元付会社名</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="source_company_name"
                        value="{{ $archive->source_company_name }}"
                        maxlength="255"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">元付担当者</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="source_staff_name"
                        value="{{ $archive->source_staff_name }}"
                        maxlength="255"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">元付電話番号</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="source_phone"
                        value="{{ $archive->source_phone }}"
                        maxlength="50"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">元付確認日</div>
                <div class="rental-archive-form-value">
                    <input
                        type="date"
                        class="rental-archive-field application-inline-field rental-archive-input-date"
                        data-field="source_confirmed_on"
                        value="{{ optional($archive->source_confirmed_on)->format('Y-m-d') }}"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">貴社物件コード</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="company_property_code"
                        value="{{ $archive->company_property_code }}"
                        maxlength="255"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>
        </div>

        <div class="rental-archive-form-col">
            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">ネット掲載</div>
                <div class="rental-archive-form-value">
                    <select
                        class="rental-archive-field application-inline-field"
                        data-field="net_listing"
                        @disabled(! $canEditFields)
                    >
                        <option value="">選択してください</option>
                    </select>
                </div>
            </div>

            @foreach ([
                'スマピク掲載',
                'おすピク掲載',
                '店舗案内ピックアップ掲載',
                '動画・CM掲載',
                'パノラマ掲載',
                '得意なエリア枠掲載',
                '会社聞流通掲載',
            ] as $blankLabel)
                <div class="rental-archive-form-row rental-archive-form-row--blank">
                    <div class="rental-archive-form-label">{{ $blankLabel }}</div>
                    <div class="rental-archive-form-value">
                        <span class="rental-archive-blank-note">（未設定）</span>
                    </div>
                </div>
            @endforeach

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">他者によるコピー</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="third_party_copy"
                        value="{{ $archive->third_party_copy }}"
                        maxlength="255"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>
        </div>
    </div>
</section>
