@php
    use App\Support\Format;

    $addressOptions = $addresses;
    if ($isEdit && !empty($formData['property_address']) && !in_array($formData['property_address'], $addressOptions, true)) {
        array_unshift($addressOptions, $formData['property_address']);
    }

    $salesPersonOptions = $salesPersons;
    if ($isEdit && !empty($formData['sales_person']) && !in_array($formData['sales_person'], $salesPersonOptions, true)) {
        array_unshift($salesPersonOptions, $formData['sales_person']);
    }

    $docFields = $documentLabels;
@endphp

<form method="post" enctype="multipart/form-data" class="entry-form" id="propertyForm"
      action="{{ $isEdit ? route('properties.update', array_filter(['property' => $property, 'from' => request('from')])) : route('properties.store') }}">
    @csrf
    @if($isEdit)
    @method('PUT')
    @endif

    <section class="form-section form-section-clean">
        <h2 class="section-label">基本情報</h2>

        <div class="form-row form-row-2">
            <div class="form-group">
                <label for="created_at">作成日時 <span class="required">*</span></label>
                <input type="datetime-local" id="created_at" name="created_at"
                       value="{{ old('created_at', $formData['created_at']) }}" required>
            </div>
            <div class="form-group sales-person-block">
                <p class="inline-block-label">担当営業</p>
                <div class="address-mode address-mode-compact">
                    <label class="radio-chip">
                        <input type="radio" name="sales_person_mode" value="existing"
                            {{ old('sales_person_mode', $formData['sales_person_mode']) === 'existing' ? 'checked' : '' }}>
                        登録済み
                    </label>
                    <label class="radio-chip">
                        <input type="radio" name="sales_person_mode" value="new"
                            {{ old('sales_person_mode', $formData['sales_person_mode']) === 'new' ? 'checked' : '' }}>
                        新規追加
                    </label>
                </div>
                <div id="existingSalesGroup"
                     {{ old('sales_person_mode', $formData['sales_person_mode']) === 'new' ? 'hidden' : '' }}>
                    <select id="sales_person" name="sales_person">
                        <option value="">— 未選択 —</option>
                        @foreach($salesPersonOptions as $person)
                        <option value="{{ $person }}"
                            {{ old('sales_person', $formData['sales_person']) === $person && old('sales_person_mode', $formData['sales_person_mode']) === 'existing' ? 'selected' : '' }}>
                            {{ $person }}
                        </option>
                        @endforeach
                    </select>
                    @if(!$hasSalesPersons && !$isEdit)
                    <p class="field-hint">登録済みの担当はまだありません</p>
                    @endif
                </div>
                <div id="newSalesGroup"
                     {{ old('sales_person_mode', $formData['sales_person_mode']) !== 'new' ? 'hidden' : '' }}>
                    <input type="text" id="new_sales_person" name="new_sales_person"
                           value="{{ old('new_sales_person', $formData['new_sales_person']) }}" placeholder="例：田中">
                </div>
            </div>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="buyer_name">購入者 <span class="required">*</span></label>
                <input type="text" id="buyer_name" name="buyer_name"
                       value="{{ old('buyer_name', $formData['buyer_name']) }}" required placeholder="山田 太郎">
            </div>
            <div class="form-group">
                <label for="broker_name">仲介業者名</label>
                <input type="text" id="broker_name" name="broker_name"
                       value="{{ old('broker_name', $formData['broker_name']) }}" placeholder="○○不動産">
            </div>
            <div class="form-group">
                <label for="owner_name">オーナー名</label>
                <input type="text" id="owner_name" name="owner_name"
                       value="{{ old('owner_name', $formData['owner_name']) }}" placeholder="株式会社○○">
            </div>
        </div>

        <div class="address-block">
            <p class="address-block-label">物件住所 <span class="required">*</span></p>

            <div class="address-mode">
                <label class="radio-chip">
                    <input type="radio" name="address_mode" value="existing"
                        {{ old('address_mode', $formData['address_mode']) === 'existing' ? 'checked' : '' }}>
                    登録済みから選択
                </label>
                <label class="radio-chip">
                    <input type="radio" name="address_mode" value="new"
                        {{ old('address_mode', $formData['address_mode']) === 'new' ? 'checked' : '' }}>
                    新規入力
                </label>
            </div>

            <div class="form-group" id="existingAddressGroup"
                 {{ old('address_mode', $formData['address_mode']) === 'new' ? 'hidden' : '' }}>
                <label for="property_address">登録済み住所</label>
                <select id="property_address" name="property_address">
                    <option value="">選択してください</option>
                    @foreach($addressOptions as $addr)
                    <option value="{{ $addr }}"
                        {{ old('property_address', $formData['property_address']) === $addr && old('address_mode', $formData['address_mode']) === 'existing' ? 'selected' : '' }}>
                        {{ $addr }}
                    </option>
                    @endforeach
                </select>
                @if(!$hasAddresses && !$isEdit)
                <p class="field-hint">登録済みの住所はまだありません</p>
                @endif
            </div>

            <div id="newAddressGroup" class="address-fields"
                 {{ old('address_mode', $formData['address_mode']) !== 'new' ? 'hidden' : '' }}>
                <div class="form-row form-row-4">
                    <div class="form-group">
                        <label for="prefecture">都道府県</label>
                        <select id="prefecture" name="prefecture">
                            <option value="">選択</option>
                            @foreach($prefectures as $pref)
                            <option value="{{ $pref }}"
                                {{ old('prefecture', $formData['prefecture']) === $pref ? 'selected' : '' }}>
                                {{ $pref }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="city">市町村</label>
                        <input type="text" id="city" name="city"
                               value="{{ old('city', $formData['city']) }}" placeholder="渋谷区">
                    </div>
                    <div class="form-group">
                        <label for="street">丁目・番地・号</label>
                        <input type="text" id="street" name="street"
                               value="{{ old('street', $formData['street']) }}" placeholder="神南1-2-3">
                    </div>
                    <div class="form-group">
                        <label for="building_name">建物名</label>
                        <input type="text" id="building_name" name="building_name"
                               value="{{ old('building_name', $formData['building_name']) }}" placeholder="○○マンション101">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="form-section form-section-clean">
        <h2 class="section-label">価格・費用</h2>

        @php $isTotalPriceMode = old('price_mode', $formData['price_mode'] ?? 'split') === 'total'; @endphp

        <div class="address-mode address-mode-compact price-mode-row">
            <label class="radio-chip">
                <input type="radio" name="price_mode" value="split"
                    {{ !$isTotalPriceMode ? 'checked' : '' }}>
                建物・土地を入力
            </label>
            <label class="radio-chip">
                <input type="radio" name="price_mode" value="total"
                    {{ $isTotalPriceMode ? 'checked' : '' }}>
                物件価格を直接入力
            </label>
        </div>

        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="building_price">建物取得価格</label>
                <div class="input-yen">
                    <input type="number" id="building_price" name="building_price" min="0"
                           value="{{ $isTotalPriceMode ? 0 : (int) old('building_price', $formData['building_price']) }}"
                           {{ $isTotalPriceMode ? 'readonly' : '' }}>
                    <span class="input-unit">円</span>
                </div>
            </div>
            <div class="form-group">
                <label for="land_price">土地取得価格</label>
                <div class="input-yen">
                    <input type="number" id="land_price" name="land_price" min="0"
                           value="{{ $isTotalPriceMode ? 0 : (int) old('land_price', $formData['land_price']) }}"
                           {{ $isTotalPriceMode ? 'readonly' : '' }}>
                    <span class="input-unit">円</span>
                </div>
            </div>
            <div class="form-group">
                <label for="total_price">物件価格（合算）</label>
                <div class="input-yen" id="total_price_input_wrap" {{ !$isTotalPriceMode ? 'hidden' : '' }}>
                    <input type="number" id="total_price" name="total_price" min="0"
                           value="{{ (int) old('total_price', $formData['total_price'] ?? 0) }}">
                    <span class="input-unit">円</span>
                </div>
                <div class="calculated-field" id="total_price_display" {{ $isTotalPriceMode ? 'hidden' : '' }}>
                    {{ Format::formatYen((int) old('building_price', $formData['building_price']) + (int) old('land_price', $formData['land_price'])) }}
                </div>
            </div>
        </div>
        <div class="form-row form-row-3">
            <div class="form-group">
                <label for="registration_fee">登記費用</label>
                <div class="input-yen">
                    <input type="number" id="registration_fee" name="registration_fee" min="0"
                           value="{{ (int) old('registration_fee', $formData['registration_fee']) }}">
                    <span class="input-unit">円</span>
                </div>
            </div>
            <div class="form-group">
                <label for="brokerage_fee">仲介手数料</label>
                <div class="input-yen">
                    <input type="number" id="brokerage_fee" name="brokerage_fee" min="0"
                           value="{{ (int) old('brokerage_fee', $formData['brokerage_fee']) }}">
                    <span class="input-unit">円</span>
                </div>
            </div>
            <div class="form-group">
                <label for="property_tax">固定資産税</label>
                <div class="input-yen">
                    <input type="number" id="property_tax" name="property_tax" min="0"
                           value="{{ (int) old('property_tax', $formData['property_tax']) }}">
                    <span class="input-unit">円</span>
                </div>
            </div>
        </div>
    </section>

    <section class="form-section form-section-clean">
        <h2 class="section-label">添付書類</h2>
        <p class="section-hint">jpg / png / pdf（各{{ (int) (config('careearth.upload.max_size') / 1024 / 1024) }}MBまで）{{ $isEdit ? ' — 変更する場合のみファイルを選択' : '' }}</p>
        <div class="form-row form-row-2">
            @foreach($docFields as $name => $label)
                @php
                    $currentPath = $isEdit ? ($property->{$name} ?? null) : null;
                    $hasFile = $currentPath && $documentService->fileExists($currentPath);
                @endphp
            <div class="form-group file-group">
                <label for="{{ $name }}">{{ $label }}</label>
                @if($hasFile)
                <p class="current-file">
                    現在のファイル:
                    <a href="{{ route('files.show', ['property' => $property->id, 'field' => $name]) }}" target="_blank">表示</a>
                </p>
                @endif
                <input type="file" id="{{ $name }}" name="{{ $name }}"
                       accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                       class="file-input">
                <div class="file-preview" id="preview_{{ $name }}"></div>
            </div>
            @endforeach
        </div>
    </section>

    <div class="form-actions">
        @if($isEdit)
        <a href="{{ route('properties.show', array_filter(['property' => $property, 'from' => request('from') === 'reference' ? 'reference' : null])) }}" class="btn btn-ghost">キャンセル</a>
        @else
        <button type="reset" class="btn btn-ghost">リセット</button>
        @endif
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@include('components.property-form-script')
