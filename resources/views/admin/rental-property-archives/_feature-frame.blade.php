@php
    $canEditFields = (bool) ($canEdit ?? false);
@endphp

<section class="application-block rental-archive-detail__section rental-archive-feature-frame">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">特徴項目・備考</h3>

    <div class="rental-archive-form-grid">
        <div class="rental-archive-form-col">
            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">管理形態</div>
                <div class="rental-archive-form-value">
                    <div class="rental-archive-radio-group" role="radiogroup" aria-label="管理形態">
                        @foreach (\App\Models\RentalPropertyArchive::enumFieldOptions()['management_form'] as $option)
                            <label class="rental-archive-radio">
                                <input
                                    type="radio"
                                    class="rental-archive-field"
                                    name="management_form_{{ $archive->id }}"
                                    data-field="management_form"
                                    value="{{ $option }}"
                                    @checked($archive->management_form === $option)
                                    @disabled(! $canEditFields)
                                >
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">リフォーム詳細</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input type="checkbox" class="rental-archive-field" data-field="has_reform_detail" value="1" @checked($archive->has_reform_detail) @disabled(! $canEditFields)>
                        <span>あり</span>
                    </label>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">他交通機関</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input type="checkbox" class="rental-archive-field" data-field="has_other_transit" value="1" @checked($archive->has_other_transit) @disabled(! $canEditFields)>
                        <span>あり</span>
                    </label>
                </div>
            </div>

            <div class="rental-archive-form-row rental-archive-form-row--blank">
                <div class="rental-archive-form-label">駅から</div>
                <div class="rental-archive-form-value">
                    <span class="rental-archive-blank-note">（未設定）</span>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">エネルギー消費性能</div>
                <div class="rental-archive-form-value">
                    <div class="rental-archive-radio-group" role="radiogroup" aria-label="エネルギー消費性能">
                        @foreach (\App\Models\RentalPropertyArchive::enumFieldOptions()['energy_performance'] as $option)
                            <label class="rental-archive-radio">
                                <input
                                    type="radio"
                                    class="rental-archive-field"
                                    name="energy_performance_{{ $archive->id }}"
                                    data-field="energy_performance"
                                    value="{{ $option }}"
                                    @checked($archive->energy_performance === $option)
                                    @disabled(! $canEditFields)
                                >
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">環境設備・距離１</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input type="checkbox" class="rental-archive-field" data-field="has_env_facility_distance_1" value="1" @checked($archive->has_env_facility_distance_1) @disabled(! $canEditFields)>
                        <span>あり</span>
                    </label>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">環境設備・距離２</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input type="checkbox" class="rental-archive-field" data-field="has_env_facility_distance_2" value="1" @checked($archive->has_env_facility_distance_2) @disabled(! $canEditFields)>
                        <span>あり</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="rental-archive-form-col">
            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">環境設備・隣接１</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input type="checkbox" class="rental-archive-field" data-field="has_env_facility_adjacent_1" value="1" @checked($archive->has_env_facility_adjacent_1) @disabled(! $canEditFields)>
                        <span>あり</span>
                    </label>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">環境設備・隣接２</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input type="checkbox" class="rental-archive-field" data-field="has_env_facility_adjacent_2" value="1" @checked($archive->has_env_facility_adjacent_2) @disabled(! $canEditFields)>
                        <span>あり</span>
                    </label>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">環境設備・１F</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input type="checkbox" class="rental-archive-field" data-field="has_env_facility_1f" value="1" @checked($archive->has_env_facility_1f) @disabled(! $canEditFields)>
                        <span>あり</span>
                    </label>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">断熱性能</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <select
                        class="rental-archive-field rental-archive-select-sm"
                        data-field="insulation_grade"
                        @disabled(! $canEditFields)
                    >
                        <option value="">選択</option>
                        @foreach (range(1, 7) as $grade)
                            <option value="{{ $grade }}" @selected((int) $archive->insulation_grade === $grade)>{{ $grade }}</option>
                        @endforeach
                    </select>
                    <span>段階 / 7段階中</span>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">目安光熱費</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <span>約</span>
                    <input
                        type="number"
                        min="0"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="utility_cost_major"
                        value="{{ $archive->utility_cost_major }}"
                        @readonly(! $canEditFields)
                    >
                    <span>.</span>
                    <input
                        type="number"
                        min="0"
                        max="9"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="utility_cost_minor"
                        value="{{ $archive->utility_cost_minor }}"
                        @readonly(! $canEditFields)
                    >
                    <span>万円/年</span>
                </div>
            </div>
        </div>
    </div>
</section>
