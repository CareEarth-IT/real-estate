<script>
    @if (!empty($rentalIncomeIndexUrl))
    const rentalIncomeIndexUrl = @json($rentalIncomeIndexUrl);
    const rentalIncomeListQuery = @json($listQuery ?? []);
    const paymentMonthSelect = document.getElementById('paymentMonthSelect');

    paymentMonthSelect?.addEventListener('change', () => {
        const month = paymentMonthSelect.value;
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
    });
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
