<div id="customer-info-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">
    <div
        class="flex max-h-[90vh] w-full max-w-4xl flex-col rounded-xl bg-white shadow-xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="customer-info-modal-title"
    >
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h3 id="customer-info-modal-title" class="text-lg font-semibold text-slate-900">顧客情報入力</h3>
                <p class="mt-1 text-sm text-slate-500">
                    顧客ID: <span id="customer-info-case-number">未採番</span>
                </p>
            </div>
        </div>

        <form id="customer-info-form" class="flex min-h-0 flex-1 flex-col">
            <div class="overflow-y-auto px-6 py-5 space-y-6">
                <div id="customer-info-errors" class="hidden rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm"></div>

                <section class="space-y-4">
                    <h4 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-2">物件・契約情報</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">物件名 <span class="text-red-500">*</span></span>
                            <input type="text" name="property_name" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">部屋番号 <span class="text-red-500">*</span></span>
                            <input type="text" name="room_number" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm md:col-span-2">
                            <span class="mb-1 block font-medium text-slate-700">住所 <span class="text-red-500">*</span></span>
                            <input type="text" name="address" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">管理会社 <span class="text-red-500">*</span></span>
                            <input type="text" name="management_company" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">入居日/保険加入日 <span class="text-red-500">*</span></span>
                            <input type="text" name="move_in_date" required data-date-picker autocomplete="off" placeholder="YYYY/MM/DD" class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none bg-white">
                            <span class="mt-1 block text-xs text-slate-500">手入力（例: 1990/05/15）またはカレンダーから選択できます</span>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">契約期間 <span class="text-red-500">*</span></span>
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    name="contract_period"
                                    required
                                    inputmode="numeric"
                                    class="customer-info-field w-24 rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none"
                                >
                                <span class="text-sm text-slate-700 shrink-0">年</span>
                            </div>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">種類（契約期間） <span class="text-red-500">*</span></span>
                            <select name="contract_period_type" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none bg-white">
                                <option value="">選択してください</option>
                                <option value="0">普通</option>
                                <option value="1">定期</option>
                            </select>
                        </label>
                    </div>
                </section>

                <section class="space-y-4">
                    <h4 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-2">申込者情報</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">氏名 <span class="text-red-500">*</span></span>
                            <input type="text" name="name" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">生年月日 <span class="text-red-500">*</span></span>
                            <input type="text" name="date_of_birth" required data-date-picker data-date-max="today" autocomplete="off" placeholder="YYYY/MM/DD" class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none bg-white">
                            <span class="mt-1 block text-xs text-slate-500">手入力（例: 1990/05/15）またはカレンダーから選択できます</span>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">既婚/未婚 <span class="text-red-500">*</span></span>
                            <select name="is_married" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none bg-white">
                                <option value="">選択してください</option>
                                <option value="1">既婚</option>
                                <option value="0">未婚</option>
                            </select>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">携帯番号 <span class="text-red-500">*</span></span>
                            <input type="text" name="mobile_number" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm md:col-span-2">
                            <span class="mb-1 block font-medium text-slate-700">メールアドレス <span class="text-red-500">*</span></span>
                            <input type="email" name="email" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm md:col-span-2">
                            <span class="mb-1 block font-medium text-slate-700">職業 <span class="text-red-500">*</span></span>
                            <input type="text" name="occupation" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                    </div>
                </section>

                <section class="space-y-4">
                    <h4 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-2">会社・学校情報</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block text-sm md:col-span-2">
                            <span class="mb-1 block font-medium text-slate-700">会社名/学校名 <span class="text-red-500">*</span></span>
                            <input type="text" name="company_or_school_name" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">電話番号（会社名/学校名） <span class="text-red-500">*</span></span>
                            <input type="text" name="company_or_school_phone" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm md:col-span-2">
                            <span class="mb-1 block font-medium text-slate-700">住所（会社/学校） <span class="text-red-500">*</span></span>
                            <input type="text" name="company_or_school_address" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                    </div>
                </section>

                <section class="space-y-4">
                    <h4 class="text-sm font-semibold text-slate-900 border-b border-slate-100 pb-2">緊急連絡先</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">緊急連絡先の氏名 <span class="text-red-500">*</span></span>
                            <input type="text" name="emergency_contact_name" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">続柄 <span class="text-red-500">*</span></span>
                            <input type="text" name="emergency_contact_relationship" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">生年月日（緊急連絡先） <span class="text-red-500">*</span></span>
                            <input type="text" name="emergency_contact_date_of_birth" required data-date-picker data-date-max="today" autocomplete="off" placeholder="YYYY/MM/DD" class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none bg-white">
                            <span class="mt-1 block text-xs text-slate-500">手入力（例: 1990/05/15）またはカレンダーから選択できます</span>
                        </label>
                        <label class="block text-sm">
                            <span class="mb-1 block font-medium text-slate-700">携帯番号（緊急連絡先） <span class="text-red-500">*</span></span>
                            <input type="text" name="emergency_contact_mobile" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm md:col-span-2">
                            <span class="mb-1 block font-medium text-slate-700">現住所（緊急連絡先） <span class="text-red-500">*</span></span>
                            <input type="text" name="emergency_contact_address" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                        <label class="block text-sm md:col-span-2">
                            <span class="mb-1 block font-medium text-slate-700">メールアドレス（緊急連絡先） <span class="text-red-500">*</span></span>
                            <input type="email" name="emergency_contact_email" required class="customer-info-field w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#5383c3] focus:ring-2 focus:ring-[#5383c3]/20 outline-none">
                        </label>
                    </div>
                </section>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4">
                <button
                    type="button"
                    id="customer-info-modal-cancel"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    キャンセル
                </button>
                <button
                    type="submit"
                    id="customer-info-modal-save"
                    class="rounded-lg bg-[#5383c3] px-4 py-2 text-sm font-medium text-white hover:opacity-90"
                >
                    保存する
                </button>
            </div>
        </form>
    </div>
</div>
