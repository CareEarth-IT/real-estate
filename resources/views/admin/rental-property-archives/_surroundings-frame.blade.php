@php
    $canEditFields = (bool) ($canEdit ?? false);
    $categories = \App\Models\RentalPropertyArchive::surroundingCategories();
    $rowCount = \App\Models\RentalPropertyArchive::surroundingsRowCount();
    $rows = array_values((array) ($archive->surroundings ?? []));
@endphp

<section class="application-block rental-archive-detail__section rental-archive-surroundings-frame">
    <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">周辺環境</h3>

    <div class="rental-archive-form-grid rental-archive-form-grid--single">
        <div class="rental-archive-form-col">
            @for ($i = 0; $i < $rowCount; $i++)
                @php
                    $row = $rows[$i] ?? [];
                    $category = $row['category'] ?? '';
                    $placeName = $row['place_name'] ?? '';
                    $meters = $row['meters'] ?? '';
                    $driveUrl = $row['google_drive_url'] ?? '';
                @endphp
                <div class="rental-archive-form-row rental-archive-form-row--surroundings" data-surroundings-row="{{ $i }}">
                    <div class="rental-archive-form-label">周辺画像{{ $i + 1 }}</div>
                    <div class="rental-archive-form-value rental-archive-form-value--stack">
                        <div class="rental-archive-form-value--wrap">
                            <select
                                class="rental-archive-field rental-archive-select-sm"
                                data-field="surroundings"
                                data-surroundings-key="category"
                                data-surroundings-index="{{ $i }}"
                                @disabled(! $canEditFields)
                            >
                                <option value="">種別</option>
                                @foreach ($categories as $option)
                                    <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            <input
                                type="text"
                                class="rental-archive-field application-inline-field"
                                data-field="surroundings"
                                data-surroundings-key="place_name"
                                data-surroundings-index="{{ $i }}"
                                value="{{ $placeName }}"
                                maxlength="255"
                                placeholder="施設名など"
                                @readonly(! $canEditFields)
                            >
                            <span>まで</span>
                            <input
                                type="number"
                                min="0"
                                class="rental-archive-field rental-archive-input-sm"
                                data-field="surroundings"
                                data-surroundings-key="meters"
                                data-surroundings-index="{{ $i }}"
                                value="{{ $meters }}"
                                @readonly(! $canEditFields)
                            >
                            <span>ｍ</span>
                        </div>
                        <div class="rental-archive-form-value--wrap">
                            <input
                                type="url"
                                class="rental-archive-field application-inline-field"
                                data-field="surroundings"
                                data-surroundings-key="google_drive_url"
                                data-surroundings-index="{{ $i }}"
                                value="{{ $driveUrl }}"
                                maxlength="2000"
                                placeholder="Googleドライブのリンクを貼り付け"
                                @readonly(! $canEditFields)
                            >
                            <a
                                href="{{ $driveUrl ?: '#' }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="rental-archive-drive-open text-sm font-semibold text-primary-600 hover:underline {{ $driveUrl ? '' : 'pointer-events-none opacity-40' }}"
                                data-surroundings-open="{{ $i }}"
                                @if (! $driveUrl) aria-disabled="true" @endif
                            >
                                リンクを開く
                            </a>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>
</section>
