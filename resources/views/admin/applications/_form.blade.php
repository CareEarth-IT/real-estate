<section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
    <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">基本情報</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <x-form-field label="担当者" name="staff_in_charge" required class="md:col-span-2" />
        <x-form-field label="物件名" name="property_name" required />
        <x-form-field label="号室" name="room_number" required />
        <x-form-field label="入居予定日" name="scheduled_move_in_date" type="date" required />
        <x-form-field label="広告料" name="advertising_fee" type="number" min="0" required />
        <x-form-field
            label="仲介手数料"
            name="has_broker_fee"
            type="select"
            :options="['' => '選択してください', '1' => 'あり', '0' => 'なし', 'undecided' => '未定']"
            required
        />
        <div id="broker-fee-field" class="hidden md:col-span-2">
            <x-form-field label="仲介手数料（金額）" name="broker_fee" type="number" min="0" />
        </div>
        <div class="relative md:col-span-2 overflow-visible" id="management-company-field">
            <label for="management_company_name" class="block text-sm font-medium text-slate-700 mb-1">
                管理会社名
                <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="management_company_name"
                name="management_company_name"
                value="{{ old('management_company_name') }}"
                required
                autocomplete="off"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none"
            >
            <ul
                id="management-company-suggestions"
                class="absolute z-20 mt-1 hidden max-h-48 w-full overflow-y-auto rounded-lg border border-slate-200 bg-white py-1 shadow-lg"
                role="listbox"
            ></ul>
            @error('management_company_name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <x-form-field label="申込方法" name="application_method" required />
        <x-form-field label="状況" name="status" type="textarea" rows="4" class="md:col-span-2" required />
    </div>
</section>

<section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
    <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">補足情報</h3>
    <div class="grid grid-cols-1 gap-5">
        <x-form-field label="MEMO" name="memo" type="textarea" rows="3" />
        <x-form-field label="物件資料" name="property_documents_url" type="url" placeholder="https://" />
        <x-form-field
            label="家電サポート・CB等"
            name="appliance_support_notes"
            type="textarea"
            rows="3"
            placeholder="決済金は現金受取などの特記事項"
        />
    </div>
</section>

<div class="flex justify-end gap-3">
    <a href="{{ route('admin.applications.index') }}" class="btn btn-outline">キャンセル</a>
    <button type="submit" class="btn btn-primary">登録する</button>
</div>
