@php
    $canEditFields = (bool) ($canEdit ?? false);
    $radioRows = [
        'condition_corporation' => '法人',
        'condition_student' => '学生',
        'condition_gender' => '性別',
        'condition_single' => '単身者',
        'condition_two_tenants' => '二人入居',
        'condition_children' => '子供',
        'condition_pets' => 'ペット',
        'condition_instruments' => '楽器',
        'condition_office_use' => '事務所利用',
        'condition_roomshare' => 'ルームシェア',
    ];
    $leftFields = array_slice($radioRows, 0, 5, true);
    $rightFields = array_slice($radioRows, 5, null, true);
@endphp

<section class="application-block rental-archive-detail__section rental-archive-condition-frame">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">契約条件</h3>

    <div class="rental-archive-form-grid">
        <div class="rental-archive-form-col">
            @foreach ($leftFields as $field => $label)
                <div class="rental-archive-form-row">
                    <div class="rental-archive-form-label">{{ $label }}</div>
                    <div class="rental-archive-form-value">
                        <div class="rental-archive-radio-group" role="radiogroup" aria-label="{{ $label }}">
                            @foreach (\App\Models\RentalPropertyArchive::enumFieldOptions()[$field] as $option)
                                <label class="rental-archive-radio">
                                    <input
                                        type="radio"
                                        class="rental-archive-field"
                                        name="{{ $field }}_{{ $archive->id }}"
                                        data-field="{{ $field }}"
                                        value="{{ $option }}"
                                        @checked($archive->{$field} === $option)
                                        @disabled(! $canEditFields)
                                    >
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rental-archive-form-col">
            @foreach ($rightFields as $field => $label)
                <div class="rental-archive-form-row">
                    <div class="rental-archive-form-label">{{ $label }}</div>
                    <div class="rental-archive-form-value">
                        <div class="rental-archive-radio-group" role="radiogroup" aria-label="{{ $label }}">
                            @foreach (\App\Models\RentalPropertyArchive::enumFieldOptions()[$field] as $option)
                                <label class="rental-archive-radio">
                                    <input
                                        type="radio"
                                        class="rental-archive-field"
                                        name="{{ $field }}_{{ $archive->id }}"
                                        data-field="{{ $field }}"
                                        value="{{ $option }}"
                                        @checked($archive->{$field} === $option)
                                        @disabled(! $canEditFields)
                                    >
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="rental-archive-form-row">
                <div class="rental-archive-form-label">フリーレント</div>
                <div class="rental-archive-form-value">
                    <label class="rental-archive-check">
                        <input
                            type="checkbox"
                            class="rental-archive-field"
                            data-field="has_free_rent"
                            value="1"
                            @checked($archive->has_free_rent)
                            @disabled(! $canEditFields)
                        >
                        <span>あり</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</section>
