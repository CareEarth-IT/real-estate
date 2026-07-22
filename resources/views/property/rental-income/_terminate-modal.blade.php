<div id="rental-income-terminate-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">
    <div
        class="flex max-h-[90vh] w-full max-w-lg flex-col rounded-xl bg-white shadow-xl"
        role="dialog"
        aria-modal="true"
        aria-labelledby="rental-income-terminate-modal-title"
    >
        <div class="border-b border-slate-200 px-6 py-4">
            <h3 id="rental-income-terminate-modal-title" class="text-lg font-semibold text-slate-900">契約解約</h3>
            <p class="mt-1 text-sm text-slate-500">選択した支払い月を解約月とします。契約開始月〜解約月のデータ（未納・納金済など入金状況に関係なく）は残し、解約月より後のみ削除します。</p>
        </div>

        <form
            id="rental-income-terminate-form"
            method="POST"
            action="{{ route('property.rental-income.contract.terminate') }}"
            class="flex min-h-0 flex-1 flex-col"
        >
            @csrf
            <input type="hidden" name="contract" value="{{ $contractKey }}">
            <input type="hidden" name="contractor" value="{{ $representative->contractor }}">
            <input type="hidden" name="property_name" value="{{ $representative->property_name }}">
            <input type="hidden" name="month" value="{{ $returnMonth }}">

            <div class="overflow-y-auto px-6 py-5 space-y-5">
                <label class="block text-sm">
                    <span class="mb-2 block font-semibold text-slate-700">解約日 <span class="text-red-500">*</span></span>
                    <input
                        type="date"
                        name="terminated_on"
                        required
                        value="{{ old('terminated_on', now()->toDateString()) }}"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none"
                    >
                    <span class="mt-1 block text-xs text-slate-500">契約開始月〜解約月は入金状況に関係なく残し、解約月より後のみ削除します。</span>
                    @error('terminated_on')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <fieldset>
                    <legend class="mb-2 text-sm font-semibold text-slate-700">退去区分 <span class="text-red-500">*</span></legend>
                    <div class="space-y-2">
                        @foreach ($moveOutTypeLabels as $value => $label)
                            <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 text-sm text-slate-800 cursor-pointer hover:bg-slate-50">
                                <input
                                    type="radio"
                                    name="move_out_type"
                                    value="{{ $value }}"
                                    class="text-primary-600 focus:ring-primary-500"
                                    @checked(old('move_out_type') === $value)
                                    required
                                >
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('move_out_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </fieldset>

                <label class="block text-sm">
                    <span class="mb-2 block font-semibold text-slate-700">退去理由 <span class="text-red-500">*</span></span>
                    <textarea
                        name="move_out_reason"
                        rows="4"
                        required
                        maxlength="2000"
                        placeholder="退去理由を入力"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none"
                    >{{ old('move_out_reason') }}</textarea>
                    @error('move_out_reason')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block text-sm">
                    <span class="mb-2 block font-semibold text-slate-700">退去費</span>
                    <input
                        type="number"
                        name="move_out_cost"
                        min="0"
                        step="1"
                        value="{{ old('move_out_cost') }}"
                        placeholder="0"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 outline-none"
                    >
                    @error('move_out_cost')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </label>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200 px-6 py-4">
                <button type="button" id="rental-income-terminate-cancel" class="btn btn-ghost">キャンセル</button>
                <button type="submit" class="btn btn-primary">解約を登録</button>
            </div>
        </form>
    </div>
</div>
