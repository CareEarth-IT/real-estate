@php
    $canEditFields = (bool) ($canEdit ?? false);
    $locationOptions = \App\Models\RentalPropertyArchive::locationEnvironmentLocationOptions();
    $structureOptions = \App\Models\RentalPropertyArchive::locationEnvironmentStructureOptions();
    $buildingOptions = \App\Models\RentalPropertyArchive::locationEnvironmentBuildingOptions();
    $floorOptions = \App\Models\RentalPropertyArchive::floorFeatureOptions();
    $outdoorOptions = \App\Models\RentalPropertyArchive::outdoorSpaceOptions();
    $managementOptions = \App\Models\RentalPropertyArchive::managementSecurityOptions();
    $commonAreaOptions = \App\Models\RentalPropertyArchive::commonAreaOptions();
    $sizeOptions = \App\Models\RentalPropertyArchive::sizeFeatureOptions();
    $buildingAgeOptions = \App\Models\RentalPropertyArchive::buildingAgeFeatureOptions();
    $parkingOptions = \App\Models\RentalPropertyArchive::parkingBicycleOptions();
    $sunlightOptions = \App\Models\RentalPropertyArchive::sunlightLightingOptions();
    $gardenOptions = \App\Models\RentalPropertyArchive::gardenOptions();
    $floorPlanOptions = \App\Models\RentalPropertyArchive::floorPlanFeatureOptions();
    $locationSelected = array_values((array) ($archive->location_environment ?? []));
    $floorSelected = array_values((array) ($archive->floor_features ?? []));
    $outdoorSelected = array_values((array) ($archive->outdoor_space ?? []));
    $managementSelected = array_values((array) ($archive->management_security ?? []));
    $commonAreaSelected = array_values((array) ($archive->common_area ?? []));
    $sizeSelected = array_values((array) ($archive->size_features ?? []));
    $buildingAgeSelected = array_values((array) ($archive->building_age_features ?? []));
    $parkingSelected = array_values((array) ($archive->parking_bicycle ?? []));
    $sunlightSelected = array_values((array) ($archive->sunlight_lighting ?? []));
    $gardenSelected = array_values((array) ($archive->garden ?? []));
    $floorPlanSelected = array_values((array) ($archive->floor_plan_features ?? []));
@endphp

<section class="application-block rental-archive-detail__section rental-archive-tag-frame">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">特徴項目</h3>

    <div
        class="rental-archive-tag-panel"
        data-tag-group="location_environment"
        data-field="location_environment"
    >
        <div class="rental-archive-tag-panel__title">■ 立地・環境</div>
        <div class="rental-archive-tag-panel__scroll" data-tag-panel-scroll style="height: 280px;">
            <div class="rental-archive-tag-grid">
                @foreach ($locationOptions as $option)
                    <label class="rental-archive-tag-item">
                        <input
                            type="checkbox"
                            class="rental-archive-field rental-archive-tag-field"
                            data-field="location_environment"
                            data-tag-group="location_environment"
                            value="{{ $option }}"
                            @checked(in_array($option, $locationSelected, true))
                            @disabled(! $canEditFields)
                        >
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            </div>

            <div class="rental-archive-tag-grid rental-archive-tag-grid--continued">
                @foreach ($structureOptions as $option)
                    <label class="rental-archive-tag-item">
                        <input
                            type="checkbox"
                            class="rental-archive-field rental-archive-tag-field"
                            data-field="location_environment"
                            data-tag-group="location_environment"
                            value="{{ $option }}"
                            @checked(in_array($option, $locationSelected, true))
                            @disabled(! $canEditFields)
                        >
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            </div>

            <div class="rental-archive-tag-grid rental-archive-tag-grid--continued">
                @foreach ($buildingOptions as $option)
                    <label class="rental-archive-tag-item">
                        <input
                            type="checkbox"
                            class="rental-archive-field rental-archive-tag-field"
                            data-field="location_environment"
                            data-tag-group="location_environment"
                            value="{{ $option }}"
                            @checked(in_array($option, $locationSelected, true))
                            @disabled(! $canEditFields)
                        >
                        <span>{{ $option }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div
            class="rental-archive-tag-panel__resizer"
            data-tag-panel-resizer
            role="separator"
            aria-orientation="horizontal"
            aria-label="特徴項目パネルの高さを変更"
            title="ドラッグして高さを変更"
        ></div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="floor_features"
        data-field="floor_features"
    >
        <div class="rental-archive-tag-panel__title">■ 階・フロア</div>
        <div class="rental-archive-tag-grid">
            @foreach ($floorOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="floor_features"
                        data-tag-group="floor_features"
                        value="{{ $option }}"
                        @checked(in_array($option, $floorSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="outdoor_space"
        data-field="outdoor_space"
    >
        <div class="rental-archive-tag-panel__title">■ 居室外スペース</div>
        <div class="rental-archive-tag-grid">
            @foreach ($outdoorOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="outdoor_space"
                        data-tag-group="outdoor_space"
                        value="{{ $option }}"
                        @checked(in_array($option, $outdoorSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="management_security"
        data-field="management_security"
    >
        <div class="rental-archive-tag-panel__title">■ 管理・防犯</div>
        <div class="rental-archive-tag-grid">
            @foreach ($managementOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="management_security"
                        data-tag-group="management_security"
                        value="{{ $option }}"
                        @checked(in_array($option, $managementSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="common_area"
        data-field="common_area"
    >
        <div class="rental-archive-tag-panel__title">■ 共用部</div>
        <div class="rental-archive-tag-grid">
            @foreach ($commonAreaOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="common_area"
                        data-tag-group="common_area"
                        value="{{ $option }}"
                        @checked(in_array($option, $commonAreaSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="size_features"
        data-field="size_features"
    >
        <div class="rental-archive-tag-panel__title">■ 広さ</div>
        <div class="rental-archive-tag-grid">
            @foreach ($sizeOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="size_features"
                        data-tag-group="size_features"
                        value="{{ $option }}"
                        @checked(in_array($option, $sizeSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="building_age_features"
        data-field="building_age_features"
    >
        <div class="rental-archive-tag-panel__title">■ 築年数</div>
        <div class="rental-archive-tag-grid">
            @foreach ($buildingAgeOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="building_age_features"
                        data-tag-group="building_age_features"
                        value="{{ $option }}"
                        @checked(in_array($option, $buildingAgeSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="parking_bicycle"
        data-field="parking_bicycle"
    >
        <div class="rental-archive-tag-panel__title">■ 駐車・駐輪</div>
        <div class="rental-archive-tag-grid">
            @foreach ($parkingOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="parking_bicycle"
                        data-tag-group="parking_bicycle"
                        value="{{ $option }}"
                        @checked(in_array($option, $parkingSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="sunlight_lighting"
        data-field="sunlight_lighting"
    >
        <div class="rental-archive-tag-panel__title">■ 日当たり・採光</div>
        <div class="rental-archive-tag-grid">
            @foreach ($sunlightOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="sunlight_lighting"
                        data-tag-group="sunlight_lighting"
                        value="{{ $option }}"
                        @checked(in_array($option, $sunlightSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="garden"
        data-field="garden"
    >
        <div class="rental-archive-tag-panel__title">■ 庭</div>
        <div class="rental-archive-tag-grid">
            @foreach ($gardenOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="garden"
                        data-tag-group="garden"
                        value="{{ $option }}"
                        @checked(in_array($option, $gardenSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="floor_plan_features"
        data-field="floor_plan_features"
    >
        <div class="rental-archive-tag-panel__title">■ 間取り</div>
        <div class="rental-archive-tag-grid">
            @foreach ($floorPlanOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="floor_plan_features"
                        data-tag-group="floor_plan_features"
                        value="{{ $option }}"
                        @checked(in_array($option, $floorPlanSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>
</section>
