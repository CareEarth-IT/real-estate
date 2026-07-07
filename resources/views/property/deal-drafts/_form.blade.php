@php
    $formAction = $isEdit
        ? route('property.deal-drafts.update', $record)
        : route('property.deal-drafts.store');
@endphp

<form method="post" action="{{ $formAction }}" class="entry-form deal-draft-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <section class="form-section form-section-clean">
        @foreach ($formRows as $row)
            @if (($row['type'] ?? null) === 'group')
                <h3 class="deal-draft-form__group-title">
                    {{ $row['label'] }}
                    @if (!empty($row['subtitle']))
                        <span class="deal-draft-group-subtitle">{{ $row['subtitle'] }}</span>
                    @endif
                </h3>
                @if (($row['group_key'] ?? null) === 'property_taxes')
                    @include('property.deal-drafts._property-taxes-form')
                @endif
                @if (($row['group_key'] ?? null) === 'ad_fees')
                    @include('property.deal-drafts._ad-fees-form')
                @endif
            @elseif (($row['type'] ?? null) === 'documents')
                <div class="deal-draft-form__documents">
                    <h3 class="deal-draft-form__group-title">{{ $row['label'] }}</h3>
                    <a href="{{ route('reference.index', ['tab' => 'documents']) }}" class="btn btn-outline btn-sm">書類一覧</a>
                </div>
            @else
                @php
                    $key = $row['key'];
                    $format = $row['format'] ?? 'text';
                    $value = old($key, $record->{$key});
                    $indentClass = ! empty($row['indent']) ? 'deal-draft-form__field--indent' : '';
                    $highlightClass = match ($row['highlight'] ?? null) {
                        'cost' => 'deal-draft-form__field--cost',
                        'price' => 'deal-draft-form__field--price',
                        default => '',
                    };
                    $isComputed = ! empty($row['computed']);
                @endphp
                <div class="form-group deal-draft-form__field {{ $indentClass }} {{ $highlightClass }} @if($isComputed) deal-draft-form__field--computed @endif">
                    <label for="field_{{ $key }}">{{ $row['label'] }}</label>

                    @if ($isComputed)
                        <div class="deal-draft-computed-value" id="field_{{ $key }}">
                            @if ($format === 'yen')
                                {{ $value !== null && $value !== '' ? number_format((int) $value) : '0' }}
                            @elseif ($format === 'percent')
                                {{ $value !== null && $value !== '' ? rtrim(rtrim(number_format((float) $value, 1), '0'), '.').'%' : '—' }}
                            @else
                                {{ $value !== null && $value !== '' ? $value : '—' }}
                            @endif
                        </div>
                        <p class="deal-draft-computed-note">自動計算（保存時に更新）</p>
                    @else
                    @switch($format)
                        @case('status')
                            <select id="field_{{ $key }}" name="{{ $key }}" required>
                                @foreach ($statuses as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" @selected((string) old($key, $record->status) === (string) $statusValue)>
                                        {{ $statusLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @break

                        @case('property_type')
                            <select id="field_{{ $key }}" name="{{ $key }}">
                                <option value="">選択してください</option>
                                @foreach ($propertyTypes as $typeValue => $typeLabel)
                                    <option value="{{ $typeValue }}" @selected((string) old($key, $record->property_type) === (string) $typeValue)>
                                        {{ $typeLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @break

                        @case('yen')
                        @case('yen_signed')
                            <input
                                type="text"
                                inputmode="numeric"
                                id="field_{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $value !== null && $value !== '' ? number_format((int) $value) : '' }}"
                                placeholder="0"
                            >
                            @break

                        @case('percent')
                            <input
                                type="text"
                                inputmode="decimal"
                                id="field_{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $value !== null && $value !== '' ? rtrim(rtrim(number_format((float) $value, 1), '0'), '.') : '' }}"
                                placeholder="0.0"
                            >
                            @break

                        @default
                            <input
                                type="text"
                                id="field_{{ $key }}"
                                name="{{ $key }}"
                                value="{{ $value }}"
                                @if ($key === 'case_number') required @endif
                            >
                    @endswitch
                    @endif
                </div>
            @endif
        @endforeach

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
            <a href="{{ route('property.deal-drafts.index') }}" class="btn btn-ghost">キャンセル</a>
        </div>
    </section>
</form>
