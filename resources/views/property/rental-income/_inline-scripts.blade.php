<script>
    @if (!empty($rentalIncomeIndexUrl))
    const rentalIncomeIndexUrl = @json($rentalIncomeIndexUrl);
    const rentalIncomeListQuery = @json($listQuery ?? []);

    function navigateToRentalIncomeMonth(month) {
        if (!month) {
            return;
        }

        const params = new URLSearchParams();

        Object.entries(rentalIncomeListQuery).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                params.set(key, value);
            }
        });

        params.set('month', month);

        const baseUrl = rentalIncomeIndexUrl.split('?')[0];
        window.location.href = baseUrl + '?' + params.toString();
    }

    function parseShortMonthSearch(query) {
        const trimmed = (query || '').trim();
        if (!trimmed) {
            return null;
        }

        const slashMatch = trimmed.match(/^(\d{2,4})[\/\-](\d{1,2})$/);
        if (slashMatch) {
            let year = parseInt(slashMatch[1], 10);
            const month = parseInt(slashMatch[2], 10);
            if (year < 100) {
                year += 2000;
            }
            if (month >= 1 && month <= 12) {
                return String(year * 100 + month);
            }
        }

        const digits = trimmed.replace(/\D/g, '');
        const fullMatch = digits.match(/^(\d{4})(\d{1,2})$/);
        if (fullMatch) {
            const month = parseInt(fullMatch[2], 10);
            if (month >= 1 && month <= 12) {
                return digits;
            }
        }

        const shortMatch = digits.match(/^(\d{2})(\d{1,2})$/);
        if (shortMatch) {
            const year = 2000 + parseInt(shortMatch[1], 10);
            const month = parseInt(shortMatch[2], 10);
            if (month >= 1 && month <= 12) {
                return String(year * 100 + month);
            }
        }

        return null;
    }

    function normalizeMonthSearchQuery(query) {
        return (query || '').trim().toLowerCase().replace(/\s+/g, '');
    }

    function monthOptionMatches(option, query) {
        if (!query) {
            return true;
        }

        const normalized = normalizeMonthSearchQuery(query);
        const compactQuery = normalized.replace(/[\/\-]/g, '');
        const label = (option.dataset.label || '').toLowerCase();
        const full = (option.dataset.full || '').toLowerCase();
        const value = option.dataset.value || '';

        return label.includes(normalized)
            || full.includes(normalized)
            || value.includes(compactQuery)
            || label.replace(/\//g, '').includes(compactQuery);
    }

    (function initPaymentMonthCombobox() {
        const combobox = document.getElementById('paymentMonthCombobox');
        const input = document.getElementById('paymentMonthInput');
        const hidden = document.getElementById('paymentMonthSelect');
        const listbox = document.getElementById('paymentMonthListbox');

        if (!combobox || !input || !hidden || !listbox) {
            return;
        }

        const options = Array.from(listbox.querySelectorAll('.rental-income-month-combobox-option'));
        let activeIndex = -1;
        let suppressBlur = false;

        const selectedLabel = () => {
            const selected = options.find((option) => option.dataset.value === hidden.value);
            return selected?.dataset.label || input.value;
        };

        const visibleOptions = () => options.filter((option) => !option.hidden);

        const setActiveOption = (index) => {
            const visible = visibleOptions();
            activeIndex = index;
            options.forEach((option) => option.classList.remove('is-active'));

            if (activeIndex >= 0 && activeIndex < visible.length) {
                visible[activeIndex].classList.add('is-active');
                visible[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        };

        const filterOptions = () => {
            const query = input.value;
            let visibleCount = 0;

            options.forEach((option) => {
                const matches = monthOptionMatches(option, query);
                option.hidden = !matches;
                if (matches) {
                    visibleCount += 1;
                }
            });

            let emptyMessage = listbox.querySelector('.rental-income-month-combobox-empty');
            if (visibleCount === 0) {
                if (!emptyMessage) {
                    emptyMessage = document.createElement('li');
                    emptyMessage.className = 'rental-income-month-combobox-empty';
                    emptyMessage.textContent = '一致する月がありません。yy/mm を入力して Enter で移動できます。';
                    listbox.appendChild(emptyMessage);
                }
                emptyMessage.hidden = false;
            } else if (emptyMessage) {
                emptyMessage.hidden = true;
            }

            setActiveOption(visibleCount > 0 ? 0 : -1);
        };

        const openList = () => {
            listbox.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            filterOptions();
        };

        const closeList = () => {
            listbox.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
            options.forEach((option) => option.classList.remove('is-active'));
            input.value = selectedLabel();
        };

        const chooseOption = (option) => {
            if (!option) {
                return;
            }

            suppressBlur = true;
            navigateToRentalIncomeMonth(option.dataset.value);
        };

        const submitSearch = () => {
            const visible = visibleOptions();

            if (activeIndex >= 0 && visible[activeIndex]) {
                chooseOption(visible[activeIndex]);
                return;
            }

            if (visible.length === 1) {
                chooseOption(visible[0]);
                return;
            }

            const exact = visible.find((option) => {
                const label = (option.dataset.label || '').toLowerCase();
                const full = (option.dataset.full || '').toLowerCase();
                const query = normalizeMonthSearchQuery(input.value);
                return label === query || full === query;
            });

            if (exact) {
                chooseOption(exact);
                return;
            }

            const parsed = parseShortMonthSearch(input.value);
            if (parsed) {
                navigateToRentalIncomeMonth(parsed);
            }
        };

        input.addEventListener('focus', openList);
        input.addEventListener('click', openList);
        input.addEventListener('input', () => {
            openList();
            filterOptions();
        });

        input.addEventListener('keydown', (event) => {
            const visible = visibleOptions();

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (listbox.hidden) {
                    openList();
                    return;
                }
                const nextIndex = activeIndex < visible.length - 1 ? activeIndex + 1 : 0;
                setActiveOption(nextIndex);
                return;
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (listbox.hidden) {
                    openList();
                    return;
                }
                const nextIndex = activeIndex > 0 ? activeIndex - 1 : visible.length - 1;
                setActiveOption(nextIndex);
                return;
            }

            if (event.key === 'Enter') {
                event.preventDefault();
                submitSearch();
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closeList();
                input.blur();
            }
        });

        input.addEventListener('blur', () => {
            if (suppressBlur) {
                return;
            }

            window.setTimeout(closeList, 120);
        });

        options.forEach((option) => {
            option.addEventListener('mousedown', (event) => {
                event.preventDefault();
                chooseOption(option);
            });
        });
    })();
    @endif

    const rentalIncomeStatusClasses = [
        'rental-income-status-unpaid',
        'rental-income-status-temporary',
        'rental-income-status-paid',
        'rental-income-status-overdue',
    ];

    function updateRentalIncomeRowStatus(row, status) {
        const normalizedStatus = status || 'unpaid';
        row.dataset.paymentStatus = normalizedStatus;

        row.classList.remove(...rentalIncomeStatusClasses);
        row.classList.add('rental-income-status-' + normalizedStatus);
    }

    document.querySelectorAll('[data-rental-income-id]').forEach((row) => {
        updateRentalIncomeRowStatus(row, row.dataset.paymentStatus);
    });

    function rentalIncomeFieldUpdateUrl(recordId) {
        return adminApiUrl('/property/rental-income/' + encodeURIComponent(recordId) + '/fields');
    }

    async function saveRentalIncomeField(recordId, field, value, fieldLabel) {
        const response = await fetch(rentalIncomeFieldUpdateUrl(recordId), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ field, value }),
        });

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            const validationMessage = data.errors?.value?.[0] ?? data.errors?.field?.[0];
            throw new Error(validationMessage || data.message || `${fieldLabel}の保存に失敗しました。`);
        }
    }

    document.querySelectorAll('.rental-income-inline-field').forEach((field) => {
        let previousValue = field.value;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;

        field.addEventListener('change', async () => {
            const row = field.closest('tr');
            const recordId = row?.dataset.rentalIncomeId;
            if (!recordId) {
                return;
            }

            const value = field.value || null;

            if (field.value === previousValue) {
                return;
            }

            try {
                await saveRentalIncomeField(recordId, fieldName, value, fieldLabel);
                previousValue = field.value;

                if (fieldName === 'payment_status') {
                    updateRentalIncomeRowStatus(row, field.value || 'unpaid');
                }
            } catch (error) {
                field.value = previousValue;
                alert(error.message || '更新に失敗しました。もう一度お試しください。');
            }
        });
    });
</script>
