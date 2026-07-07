<script>
    function dealDraftFieldUpdateUrl(recordId) {
        return adminApiUrl('/property/deal-drafts/' + encodeURIComponent(recordId) + '/fields');
    }

    function dealDraftAdFeeStoreUrl(recordId) {
        return adminApiUrl('/property/deal-drafts/' + encodeURIComponent(recordId) + '/ad-fees');
    }

    function dealDraftAdFeeUpdateUrl(recordId, adFeeId) {
        return adminApiUrl('/property/deal-drafts/' + encodeURIComponent(recordId) + '/ad-fees/' + encodeURIComponent(adFeeId));
    }

    async function saveDealDraftField(recordId, field, value, fieldLabel) {
        const response = await fetch(dealDraftFieldUpdateUrl(recordId), {
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

        return response.json();
    }

    function formatComputedValue(format, value) {
        if (value === null || value === undefined || value === '') {
            if (format === 'percent') {
                return '';
            }

            if (format === 'yen' || format === 'yen_signed') {
                return '0';
            }

            return '';
        }

        if (format === 'percent') {
            const numeric = Number(value);
            const text = Number.isInteger(numeric) ? String(numeric) : numeric.toFixed(1).replace(/\.0$/, '');

            return text + '%';
        }

        return Number(value).toLocaleString('ja-JP');
    }

    function updateComputedCells(recordId, computed) {
        if (!computed) {
            return;
        }

        Object.entries(computed).forEach(([field, value]) => {
            document.querySelectorAll(`[data-computed-field="${field}"][data-deal-draft-id="${recordId}"]`).forEach((cell) => {
                const format = cell.dataset.computedFormat || 'yen';
                cell.textContent = formatComputedValue(format, value);
            });
        });
    }

    async function requestJson(url, method, body) {
        const response = await fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: body ? JSON.stringify(body) : null,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = data.errors?.agency_name?.[0]
                ?? data.errors?.amount?.[0]
                ?? data.message
                ?? '保存に失敗しました。';
            throw new Error(message);
        }

        return data;
    }

    function createAdFeeItemElement(adFee) {
        const item = document.createElement('div');
        item.className = 'deal-draft-ad-fee-item';
        item.dataset.adFeeId = String(adFee.id);
        item.innerHTML = `
            <input type="text" class="deal-draft-ad-fee-name" value="" placeholder="仲介業者名" data-label="仲介業者名">
            <input type="text" inputmode="numeric" class="deal-draft-ad-fee-amount" value="" placeholder="金額" data-label="金額">
            <button type="button" class="deal-draft-ad-fee-delete btn btn-ghost btn-sm">削除</button>
        `;
        item.querySelector('.deal-draft-ad-fee-name').value = adFee.agency_name || '';
        item.querySelector('.deal-draft-ad-fee-amount').value = adFee.amount !== null && adFee.amount !== undefined
            ? Number(adFee.amount).toLocaleString('ja-JP')
            : '';
        return item;
    }

    function bindAdFeeItem(item, recordId) {
        const adFeeId = item.dataset.adFeeId;
        const nameInput = item.querySelector('.deal-draft-ad-fee-name');
        const amountInput = item.querySelector('.deal-draft-ad-fee-amount');
        const deleteButton = item.querySelector('.deal-draft-ad-fee-delete');

        let previousName = nameInput.value;
        let previousAmount = amountInput.value;

        async function saveName() {
            if (nameInput.value === previousName) {
                return;
            }

            if (nameInput.value.trim() === '' && previousName.trim() === '') {
                return;
            }

            nameInput.disabled = true;

            try {
                const data = await requestJson(dealDraftAdFeeUpdateUrl(recordId, adFeeId), 'PATCH', {
                    agency_name: nameInput.value,
                });
                previousName = nameInput.value;
                updateComputedCells(recordId, data.computed);
            } catch (error) {
                nameInput.value = previousName;
                alert(error.message);
            } finally {
                nameInput.disabled = false;
            }
        }

        async function saveAmount() {
            if (amountInput.value === previousAmount) {
                return;
            }

            amountInput.disabled = true;

            try {
                const data = await requestJson(dealDraftAdFeeUpdateUrl(recordId, adFeeId), 'PATCH', {
                    amount: amountInput.value,
                });
                previousAmount = amountInput.value;
                if (data.ad_fee?.amount !== null && data.ad_fee?.amount !== undefined) {
                    amountInput.value = Number(data.ad_fee.amount).toLocaleString('ja-JP');
                    previousAmount = amountInput.value;
                }

                updateComputedCells(recordId, data.computed);
            } catch (error) {
                amountInput.value = previousAmount;
                alert(error.message);
            } finally {
                amountInput.disabled = false;
            }
        }

        nameInput.addEventListener('blur', saveName);
        nameInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                nameInput.blur();
            }
        });

        amountInput.addEventListener('blur', saveAmount);
        amountInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                amountInput.blur();
            }
        });

        deleteButton.addEventListener('click', async () => {
            if (!confirm('この仲介業者名を削除しますか？')) {
                return;
            }

            deleteButton.disabled = true;

            try {
                const data = await requestJson(dealDraftAdFeeUpdateUrl(recordId, adFeeId), 'DELETE');
                item.remove();
                updateComputedCells(recordId, data.computed);
            } catch (error) {
                alert(error.message);
                deleteButton.disabled = false;
            }
        });
    }

    function bindAdFeesCell(cell) {
        const recordId = cell.dataset.dealDraftId;
        const list = cell.querySelector('.deal-draft-ad-fees-cell__list');
        const addButton = cell.querySelector('.deal-draft-ad-fee-add');

        cell.querySelectorAll('.deal-draft-ad-fee-item').forEach((item) => bindAdFeeItem(item, recordId));

        addButton?.addEventListener('click', async () => {
            addButton.disabled = true;

            try {
                const data = await requestJson(dealDraftAdFeeStoreUrl(recordId), 'POST');
                const item = createAdFeeItemElement(data.ad_fee);
                list.appendChild(item);
                bindAdFeeItem(item, recordId);
                updateComputedCells(recordId, data.computed);
                item.querySelector('.deal-draft-ad-fee-name')?.focus();
            } catch (error) {
                alert(error.message);
            } finally {
                addButton.disabled = false;
            }
        });
    }

    document.querySelectorAll('.deal-draft-inline-field').forEach((field) => {
        let previousValue = field.value;
        const fieldName = field.dataset.field;
        const fieldLabel = field.dataset.label || fieldName;
        const recordId = field.dataset.dealDraftId;

        field.addEventListener('change', async () => {
            if (!recordId || field.value === previousValue) {
                return;
            }

            field.disabled = true;

            try {
                await saveDealDraftField(recordId, fieldName, field.value || null, fieldLabel);
                previousValue = field.value;
            } catch (error) {
                field.value = previousValue;
                alert(error.message || '更新に失敗しました。もう一度お試しください。');
            } finally {
                field.disabled = false;
            }
        });
    });

    document.querySelectorAll('.deal-draft-ad-fees-cell').forEach(bindAdFeesCell);
</script>
