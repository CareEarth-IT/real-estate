@php
    $canEditFields = (bool) ($canEdit ?? false);
    $locationOptions = \App\Models\RentalPropertyArchive::locationEnvironmentLocationOptions();
    $structureOptions = \App\Models\RentalPropertyArchive::locationEnvironmentStructureOptions();
    $buildingOptions = \App\Models\RentalPropertyArchive::locationEnvironmentBuildingOptions();
    $selected = array_values((array) ($archive->location_environment ?? []));
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
                            @checked(in_array($option, $selected, true))
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
                            @checked(in_array($option, $selected, true))
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
                            @checked(in_array($option, $selected, true))
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
</section>
