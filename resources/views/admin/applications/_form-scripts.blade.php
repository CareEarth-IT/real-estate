<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        initBrokerFeeField();
        initManagementCompanyAutocomplete();
        initDatePickers();
    });

    function initDatePickers() {
        if (typeof flatpickr === 'undefined') {
            return;
        }

        document.querySelectorAll('[data-date-picker]').forEach((input) => {
            flatpickr(input, {
                locale: 'ja',
                dateFormat: 'Y-m-d',
                allowInput: true,
            });
        });
    }

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

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
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
                headers: { Accept: 'application/json' },
            })
                .then((response) => response.ok ? response.json() : Promise.reject())
                .then(renderSuggestions)
                .catch((error) => {
                    if (error.name !== 'AbortError') {
                        hideSuggestions();
                    }
                });
        }

        managementCompanyInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchSuggestions, 250);
        });

        suggestionsList.addEventListener('mousedown', function (event) {
            const button = event.target.closest('.management-company-suggestion');
            if (!button) {
                return;
            }

            event.preventDefault();
            managementCompanyInput.value = button.dataset.value;
            hideSuggestions();
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('#management-company-field')) {
                hideSuggestions();
            }
        });
    }
</script>
