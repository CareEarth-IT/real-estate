@php
    $canEditFields = (bool) ($canEdit ?? false);
@endphp

<section class="application-block rental-archive-detail__section rental-archive-property-frame">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">物件情報</h3>

    <div class="rental-archive-form-grid">
        <div class="rental-archive-form-col">
            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">物件名</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="property_name"
                        value="{{ $archive->property_name }}"
                        maxlength="255"
                        placeholder="物件名を入力"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">階建</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <span>地上</span>
                    <input type="number" min="0" class="rental-archive-field rental-archive-input-sm" data-field="floors_above" value="{{ $archive->floors_above }}" @readonly(! $canEditFields)>
                    <span>階 / 地下</span>
                    <input type="number" min="0" class="rental-archive-field rental-archive-input-sm" data-field="floors_below" value="{{ $archive->floors_below }}" @readonly(! $canEditFields)>
                    <span>階・</span>
                    <input type="number" min="0" class="rental-archive-field rental-archive-input-sm" data-field="floor_part" value="{{ $archive->floor_part }}" @readonly(! $canEditFields)>
                    <span>階部分</span>
                    <input type="text" class="rental-archive-field rental-archive-input-sm" data-field="room_number" value="{{ $archive->room_number }}" maxlength="50" @readonly(! $canEditFields)>
                    <span>号室</span>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">物件種別</div>
                <div class="rental-archive-form-value">
                    <select class="rental-archive-field application-inline-field" data-field="property_type" @disabled(! $canEditFields)>
                        <option value="">選択してください</option>
                        @foreach (\App\Models\RentalPropertyArchive::propertyTypes() as $option)
                            <option value="{{ $option }}" @selected($archive->property_type === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">構造</div>
                <div class="rental-archive-form-value">
                    <select class="rental-archive-field application-inline-field" data-field="structure" @disabled(! $canEditFields)>
                        <option value="">選択してください</option>
                        @foreach (\App\Models\RentalPropertyArchive::structures() as $option)
                            <option value="{{ $option }}" @selected($archive->structure === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">築年月</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <span>西暦</span>
                    <input type="number" min="1800" max="2100" class="rental-archive-field rental-archive-input-sm" data-field="built_year" value="{{ $archive->built_year }}" @readonly(! $canEditFields)>
                    <span>年</span>
                    <input type="number" min="1" max="12" class="rental-archive-field rental-archive-input-sm" data-field="built_month" value="{{ $archive->built_month }}" @readonly(! $canEditFields)>
                    <span>月</span>
                    <div class="rental-archive-radio-group" role="radiogroup" aria-label="築年月区分">
                        @foreach (\App\Models\RentalPropertyArchive::buildingConditions() as $option)
                            <label class="rental-archive-radio">
                                <input
                                    type="radio"
                                    class="rental-archive-field"
                                    name="building_condition_{{ $archive->id }}"
                                    data-field="building_condition"
                                    value="{{ $option }}"
                                    @checked($archive->building_condition === $option)
                                    @disabled(! $canEditFields)
                                >
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">郵便番号</div>
                <div class="rental-archive-form-value rental-archive-form-value--wrap">
                    <span>〒</span>
                    <input
                        type="text"
                        inputmode="numeric"
                        class="rental-archive-field application-inline-field"
                        data-field="postal_code"
                        value="{{ $archive->postal_code }}"
                        maxlength="16"
                        placeholder="000-0000"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">所在地</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="location"
                        value="{{ $archive->location }}"
                        maxlength="255"
                        placeholder="都道府県・市区町村・町名"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">以下住所</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="address_detail"
                        value="{{ $archive->address_detail }}"
                        maxlength="255"
                        placeholder="番地など"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">街区・号棟</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="block_building"
                        value="{{ $archive->block_building }}"
                        maxlength="255"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">地図</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input
                            type="checkbox"
                            class="rental-archive-field"
                            data-field="show_on_map"
                            value="1"
                            @checked($archive->show_on_map)
                            @disabled(! $canEditFields)
                        >
                        <span>地図に表示する</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="rental-archive-form-col">
            @foreach ([1, 2, 3] as $transitNo)
                @php
                    $lineField = "transit{$transitNo}_line";
                    $stationField = "transit{$transitNo}_station";
                    $methodField = "transit{$transitNo}_method";
                    $minutesField = "transit{$transitNo}_minutes";
                @endphp
                <div class="rental-archive-form-row rental-archive-form-row--transit">
                    <div class="rental-archive-form-label">交通{{ $transitNo }}</div>
                    <div class="rental-archive-form-value rental-archive-form-value--stack">
                        <div class="rental-archive-form-value--wrap">
                            <span>沿線</span>
                            <input
                                type="text"
                                class="rental-archive-field application-inline-field"
                                data-field="{{ $lineField }}"
                                value="{{ $archive->{$lineField} }}"
                                maxlength="255"
                                @readonly(! $canEditFields)
                            >
                        </div>
                        <div class="rental-archive-form-value--wrap">
                            <span>駅</span>
                            <input
                                type="text"
                                class="rental-archive-field application-inline-field"
                                data-field="{{ $stationField }}"
                                value="{{ $archive->{$stationField} }}"
                                maxlength="255"
                                @readonly(! $canEditFields)
                            >
                        </div>
                        <div class="rental-archive-form-value--wrap">
                            <span>駅から</span>
                            <div class="rental-archive-radio-group" role="radiogroup" aria-label="交通{{ $transitNo }}手段">
                                @foreach (\App\Models\RentalPropertyArchive::transitMethods() as $option)
                                    <label class="rental-archive-radio">
                                        <input
                                            type="radio"
                                            class="rental-archive-field"
                                            name="{{ $methodField }}_{{ $archive->id }}"
                                            data-field="{{ $methodField }}"
                                            value="{{ $option }}"
                                            @checked($archive->{$methodField} === $option)
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
                                data-field="{{ $minutesField }}"
                                value="{{ $archive->{$minutesField} }}"
                                @readonly(! $canEditFields)
                            >
                            <span>分</span>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">賃主名</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="landlord_name"
                        value="{{ $archive->landlord_name }}"
                        maxlength="255"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">総戸数</div>
                <div class="rental-archive-form-value">
                    <input
                        type="text"
                        class="rental-archive-field application-inline-field"
                        data-field="total_units"
                        value="{{ $archive->total_units }}"
                        maxlength="50"
                        @readonly(! $canEditFields)
                    >
                </div>
            </div>
        </div>
    </div>
</section>
