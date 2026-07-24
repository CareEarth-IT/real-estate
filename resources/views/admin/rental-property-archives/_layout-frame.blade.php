@php
    $canEditFields = (bool) ($canEdit ?? false);

    $slotGroups = [
        'washitsu_tatami' => ['label' => '和室（畳数）', 'count' => 10],
        'yoshitsu_tatami' => ['label' => '洋室（畳数）', 'count' => 10],
    ];
    $sideSlotGroups = [
        'nando_sizes' => ['label' => '納戸', 'count' => 5],
        'loft_sizes' => ['label' => 'ロフト', 'count' => 2],
        'study_sizes' => ['label' => '書斎', 'count' => 2],
        'sunroom_sizes' => ['label' => 'サンルーム', 'count' => 2],
        'grenier_sizes' => ['label' => 'グルニエ', 'count' => 2],
    ];
@endphp

<section class="application-block rental-archive-detail__section rental-archive-layout-frame">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">間取り</h3>

    <div class="rental-archive-form-grid">
        <div class="rental-archive-form-col">
            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">間取り</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <input
                        type="number"
                        min="0"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="floor_plan_rooms"
                        value="{{ $archive->floor_plan_rooms }}"
                        @readonly(! $canEditFields)
                    >
                    <select
                        class="rental-archive-field rental-archive-select-sm"
                        data-field="floor_plan_type"
                        @disabled(! $canEditFields)
                    >
                        @foreach (\App\Models\RentalPropertyArchive::enumFieldOptions()['floor_plan_type'] as $option)
                            <option
                                value="{{ $option }}"
                                @selected(($archive->floor_plan_type ?: 'R') === $option)
                            >{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">面積</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <input
                        type="number"
                        min="0"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="area_major"
                        value="{{ $archive->area_major }}"
                        @readonly(! $canEditFields)
                    >
                    <span>.</span>
                    <input
                        type="number"
                        min="0"
                        max="9"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="area_minor"
                        value="{{ $archive->area_minor }}"
                        @readonly(! $canEditFields)
                    >
                    <span>㎡</span>
                    <span class="rental-archive-inline-label">バルコニー面積</span>
                    <input
                        type="number"
                        min="0"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="balcony_area_major"
                        value="{{ $archive->balcony_area_major }}"
                        @readonly(! $canEditFields)
                    >
                    <span>.</span>
                    <input
                        type="number"
                        min="0"
                        max="9"
                        class="rental-archive-field rental-archive-input-sm"
                        data-field="balcony_area_minor"
                        value="{{ $archive->balcony_area_minor }}"
                        @readonly(! $canEditFields)
                    >
                    <span>㎡</span>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">開口向き</div>
                <div class="rental-archive-form-value">
                    <div class="rental-archive-radio-group" role="radiogroup" aria-label="開口向き">
                        @foreach (\App\Models\RentalPropertyArchive::enumFieldOptions()['opening_direction'] as $option)
                            <label class="rental-archive-radio">
                                <input
                                    type="radio"
                                    class="rental-archive-field"
                                    name="opening_direction_{{ $archive->id }}"
                                    data-field="opening_direction"
                                    value="{{ $option }}"
                                    @checked($archive->opening_direction === $option)
                                    @disabled(! $canEditFields)
                                >
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            @foreach ($slotGroups as $field => $meta)
                @php $values = array_values((array) ($archive->{$field} ?? [])); @endphp
                <div class="rental-archive-form-row">
                    <div class="rental-archive-form-label">{{ $meta['label'] }}</div>
                    <div class="rental-archive-form-value rental-archive-form-value--wrap">
                        @for ($i = 0; $i < $meta['count']; $i++)
                            <input
                                type="number"
                                min="0"
                                class="rental-archive-field rental-archive-input-slot"
                                data-field="{{ $field }}"
                                data-slot-index="{{ $i }}"
                                value="{{ $values[$i] ?? '' }}"
                                @readonly(! $canEditFields)
                            >
                        @endfor
                    </div>
                </div>
            @endforeach

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">LDK詳細</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="ldk_detail"
                        value="{{ $archive->ldk_detail }}"
                        maxlength="255"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>
        </div>

        <div class="rental-archive-form-col">
            @foreach ($sideSlotGroups as $field => $meta)
                @php $values = array_values((array) ($archive->{$field} ?? [])); @endphp
                <div class="rental-archive-form-row">
                    <div class="rental-archive-form-label">{{ $meta['label'] }}</div>
                    <div class="rental-archive-form-value rental-archive-form-value--wrap">
                        @for ($i = 0; $i < $meta['count']; $i++)
                            <input
                                type="number"
                                min="0"
                                class="rental-archive-field rental-archive-input-slot"
                                data-field="{{ $field }}"
                                data-slot-index="{{ $i }}"
                                value="{{ $values[$i] ?? '' }}"
                                @readonly(! $canEditFields)
                            >
                        @endfor
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
