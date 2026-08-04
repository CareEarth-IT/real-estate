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
    $kitchenOptions = \App\Models\RentalPropertyArchive::kitchenOptions();
    $bathroomOptions = \App\Models\RentalPropertyArchive::bathroomOptions();
    $toiletOptions = \App\Models\RentalPropertyArchive::toiletOptions();
    $balconyTerraceOptions = \App\Models\RentalPropertyArchive::balconyTerraceOptions();
    $indoorFacilityOptions = \App\Models\RentalPropertyArchive::indoorFacilityOptions();
    $storageOptions = \App\Models\RentalPropertyArchive::storageOptions();
    $lightingOptions = \App\Models\RentalPropertyArchive::lightingOptions();
    $informationEquipmentOptions = \App\Models\RentalPropertyArchive::informationEquipmentOptions();
    $renovationOptions = \App\Models\RentalPropertyArchive::renovationOptions();
    $costMoveInConditionOptions = \App\Models\RentalPropertyArchive::costMoveInConditionOptions();
    $furnitureApplianceOptions = \App\Models\RentalPropertyArchive::furnitureApplianceOptions();
    $goodConditionOptions = \App\Models\RentalPropertyArchive::goodConditionOptions();
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
    $kitchenSelected = array_values((array) ($archive->kitchen ?? []));
    $bathroomSelected = array_values((array) ($archive->bathroom ?? []));
    $toiletSelected = array_values((array) ($archive->toilet ?? []));
    $balconyTerraceSelected = array_values((array) ($archive->balcony_terrace ?? []));
    $indoorFacilitySelected = array_values((array) ($archive->indoor_facilities ?? []));
    $storageSelected = array_values((array) ($archive->storage ?? []));
    $lightingSelected = array_values((array) ($archive->lighting ?? []));
    $informationEquipmentSelected = array_values((array) ($archive->information_equipment ?? []));
    $renovationSelected = array_values((array) ($archive->renovation ?? []));
    $costMoveInConditionSelected = array_values((array) ($archive->cost_move_in_conditions ?? []));
    $furnitureApplianceSelected = array_values((array) ($archive->furniture_appliances ?? []));
    $goodConditionSelected = array_values((array) ($archive->good_conditions ?? []));
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

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="kitchen"
        data-field="kitchen"
    >
        <div class="rental-archive-tag-panel__title">■ キッチン</div>
        <div class="rental-archive-tag-grid">
            @foreach ($kitchenOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="kitchen"
                        data-tag-group="kitchen"
                        value="{{ $option }}"
                        @checked(in_array($option, $kitchenSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="bathroom"
        data-field="bathroom"
    >
        <div class="rental-archive-tag-panel__title">■ 浴室</div>
        <div class="rental-archive-tag-grid">
            @foreach ($bathroomOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="bathroom"
                        data-tag-group="bathroom"
                        value="{{ $option }}"
                        @checked(in_array($option, $bathroomSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="toilet"
        data-field="toilet"
    >
        <div class="rental-archive-tag-panel__title">■ トイレ</div>
        <div class="rental-archive-tag-grid">
            @foreach ($toiletOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="toilet"
                        data-tag-group="toilet"
                        value="{{ $option }}"
                        @checked(in_array($option, $toiletSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="balcony_terrace"
        data-field="balcony_terrace"
    >
        <div class="rental-archive-tag-panel__title">■ バルコニー・テラス</div>
        <div class="rental-archive-tag-grid">
            @foreach ($balconyTerraceOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="balcony_terrace"
                        data-tag-group="balcony_terrace"
                        value="{{ $option }}"
                        @checked(in_array($option, $balconyTerraceSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="indoor_facilities"
        data-field="indoor_facilities"
    >
        <div class="rental-archive-tag-panel__title">■ 室内設備・仕様</div>
        <div class="rental-archive-tag-grid">
            @foreach ($indoorFacilityOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="indoor_facilities"
                        data-tag-group="indoor_facilities"
                        value="{{ $option }}"
                        @checked(in_array($option, $indoorFacilitySelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="storage"
        data-field="storage"
    >
        <div class="rental-archive-tag-panel__title">■ 収納</div>
        <div class="rental-archive-tag-grid">
            @foreach ($storageOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="storage"
                        data-tag-group="storage"
                        value="{{ $option }}"
                        @checked(in_array($option, $storageSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="lighting"
        data-field="lighting"
    >
        <div class="rental-archive-tag-panel__title">■ 照明</div>
        <div class="rental-archive-tag-grid">
            @foreach ($lightingOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="lighting"
                        data-tag-group="lighting"
                        value="{{ $option }}"
                        @checked(in_array($option, $lightingSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="information_equipment"
        data-field="information_equipment"
    >
        <div class="rental-archive-tag-panel__title">■ 情報設備・回線</div>
        <div class="rental-archive-tag-grid">
            @foreach ($informationEquipmentOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="information_equipment"
                        data-tag-group="information_equipment"
                        value="{{ $option }}"
                        @checked(in_array($option, $informationEquipmentSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="renovation"
        data-field="renovation"
    >
        <div class="rental-archive-tag-panel__title">■ リフォーム</div>
        <div class="rental-archive-tag-grid">
            @foreach ($renovationOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="renovation"
                        data-tag-group="renovation"
                        value="{{ $option }}"
                        @checked(in_array($option, $renovationSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="cost_move_in_conditions"
        data-field="cost_move_in_conditions"
    >
        <div class="rental-archive-tag-panel__title">■ 費用・入居・引渡・条件</div>
        <div class="rental-archive-tag-grid">
            @foreach ($costMoveInConditionOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="cost_move_in_conditions"
                        data-tag-group="cost_move_in_conditions"
                        value="{{ $option }}"
                        @checked(in_array($option, $costMoveInConditionSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="furniture_appliances"
        data-field="furniture_appliances"
    >
        <div class="rental-archive-tag-panel__title">■ 家具・家電</div>
        <div class="rental-archive-tag-grid">
            @foreach ($furnitureApplianceOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="furniture_appliances"
                        data-tag-group="furniture_appliances"
                        value="{{ $option }}"
                        @checked(in_array($option, $furnitureApplianceSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>

    <div
        class="rental-archive-tag-panel rental-archive-tag-panel--compact"
        data-tag-group="good_conditions"
        data-field="good_conditions"
    >
        <div class="rental-archive-tag-panel__title">■ 良好</div>
        <div class="rental-archive-tag-grid">
            @foreach ($goodConditionOptions as $option)
                <label class="rental-archive-tag-item">
                    <input
                        type="checkbox"
                        class="rental-archive-field rental-archive-tag-field"
                        data-field="good_conditions"
                        data-tag-group="good_conditions"
                        value="{{ $option }}"
                        @checked(in_array($option, $goodConditionSelected, true))
                        @disabled(! $canEditFields)
                    >
                    <span>{{ $option }}</span>
                </label>
            @endforeach
        </div>
    </div>
</section>
