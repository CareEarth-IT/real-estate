@extends('layouts.app')

@section('content')
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-slate-900">申込情報入力</h2>
        <p class="mt-2 text-sm text-slate-600"><span class="text-red-500">*</span> の付いた項目は必須です。</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-red-800 text-sm">
            <p class="font-medium">入力内容に誤りがあります。各項目をご確認ください。</p>
        </div>
    @endif

    <form method="POST" action="{{ route('applications.store') }}" class="space-y-8" novalidate>
        @csrf

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">物件・申込情報</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <x-form-field label="担当者" name="staff_in_charge" required class="md:col-span-2" />
                <x-form-field label="物件名" name="property_name" required />
                <x-form-field label="部屋番号" name="room_number" required />
                <x-form-field
                    label="入居予定日"
                    name="scheduled_move_in_date"
                    type="date"
                    required
                />
                <x-form-field label="広告料" name="advertising_fee" type="number" min="0" required />
                <x-form-field
                    label="仲介手数料"
                    name="has_broker_fee"
                    type="select"
                    :options="['' => '選択してください', '1' => 'あり', '0' => 'なし', 'undecided' => '未定']"
                    required
                />
                <div id="broker-fee-field" class="hidden">
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
                <x-form-field label="状況" name="status" type="textarea" rows="5" class="md:col-span-2" required />
            </div>
        </section>

        <section class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-5">
            <h3 class="text-base font-semibold text-slate-900 border-b border-slate-100 pb-3">その他</h3>
            <div class="grid grid-cols-1 gap-5">
                <x-form-field label="備考" name="memo" type="textarea" />
                <x-form-field label="物件資料" name="property_documents_url" type="url" placeholder="https://" />
                <x-form-field label="家電サポート・CB等" name="appliance_support_notes" type="textarea" />
            </div>
        </section>

        <div class="flex justify-end">
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-6 py-3 text-sm font-semibold text-white hover:bg-primary-700 transition-colors"
            >
                送信する
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initBrokerFeeField();
            initManagementCompanyAutocomplete();
        });

        function initBrokerFeeField() {
            const select = document.getElementById('has_broker_fee');
            const wrapper = document.getElementById('broker-fee-field');
            const input = document.getElementById('broker_fee');

            if (!select || !wrapper || !input) {
                return;
            }

            function toggleBrokerFeeField() {
                const show = select.value === '1';
                wrapper.classList.toggle('hidden', !show);
                input.required = show;
                if (!show) {
                    input.value = '';
                }
            }

            select.addEventListener('change', toggleBrokerFeeField);
            toggleBrokerFeeField();
        }

        function initManagementCompanyAutocomplete() {
            const managementCompanyInput = document.getElementById('management_company_name');
            const suggestionsList = document.getElementById('management-company-suggestions');

            if (!managementCompanyInput || !suggestionsList) {
                return;
            }

            const suggestionsUrl = adminApiUrl('/applications/management-company-suggestions');

            let debounceTimer = null;
            let fetchController = null;
            let activeIndex = -1;

            function hideSuggestions() {
                suggestionsList.classList.add('hidden');
                suggestionsList.innerHTML = '';
                activeIndex = -1;
            }

            function renderSuggestions(names) {
                if (!names.length) {
                    hideSuggestions();
                    return;
                }

                suggestionsList.innerHTML = names.map(function (name, index) {
                    return '<li role="option">' +
                        '<button type="button" class="management-company-suggestion block w-full px-3 py-2 text-left text-sm text-slate-700 hover:bg-primary-50 focus:bg-primary-50 focus:outline-none" data-index="' + index + '" data-value="' + escapeHtml(name) + '">' +
                        escapeHtml(name) +
                        '</button></li>';
                }).join('');

                suggestionsList.classList.remove('hidden');
                activeIndex = -1;
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function selectSuggestion(value) {
                managementCompanyInput.value = value;
                hideSuggestions();
            }

            function setActiveSuggestion(index) {
                const buttons = suggestionsList.querySelectorAll('.management-company-suggestion');
                buttons.forEach(function (button, buttonIndex) {
                    button.classList.toggle('bg-primary-50', buttonIndex === index);
                });
                activeIndex = index;
            }

            function fetchSuggestions() {
                const query = managementCompanyInput.value.trim();

                if (query.length < 2) {
                    hideSuggestions();
                    return;
                }

                if (fetchController) {
                    fetchController.abort();
                }

                fetchController = new AbortController();

                fetch(suggestionsUrl + '?q=' + encodeURIComponent(query), {
                    signal: fetchController.signal,
                    headers: {
                        Accept: 'application/json',
                    },
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Failed to fetch suggestions');
                        }
                        return response.json();
                    })
                    .then(renderSuggestions)
                    .catch(function (error) {
                        if (error.name !== 'AbortError') {
                            hideSuggestions();
                        }
                    });
            }

            managementCompanyInput.addEventListener('input', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchSuggestions, 250);
            });

            managementCompanyInput.addEventListener('keydown', function (event) {
                const buttons = suggestionsList.querySelectorAll('.management-company-suggestion');

                if (!buttons.length || suggestionsList.classList.contains('hidden')) {
                    return;
                }

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    setActiveSuggestion(Math.min(activeIndex + 1, buttons.length - 1));
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    setActiveSuggestion(Math.max(activeIndex - 1, 0));
                } else if (event.key === 'Enter' && activeIndex >= 0) {
                    event.preventDefault();
                    selectSuggestion(buttons[activeIndex].dataset.value);
                } else if (event.key === 'Escape') {
                    hideSuggestions();
                }
            });

            suggestionsList.addEventListener('mousedown', function (event) {
                const button = event.target.closest('.management-company-suggestion');
                if (!button) {
                    return;
                }

                event.preventDefault();
                selectSuggestion(button.dataset.value);
            });

            document.addEventListener('click', function (event) {
                if (!event.target.closest('#management-company-field')) {
                    hideSuggestions();
                }
            });
        }
    </script>
@endpush
