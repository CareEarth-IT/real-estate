<script>
(function () {
    const form = document.querySelector('.deal-draft-form');
    if (!form) {
        return;
    }

    const config = {
        statuses: @json($statuses),
        propertyTypes: @json($propertyTypes),
        totalCostKeys: @json(\App\Support\PropertyDealDraftCalculator::totalCostKeys()),
        sellingAdminKeys: ['sale_brokerage_fee', 'contract_stamp_duty', 'receipt_stamp_duty'],
    };

    let updateTimer = null;

    function parseInteger(value) {
        if (value === null || value === undefined || value === '') {
            return 0;
        }

        const normalized = String(value).replace(/[,，¥\s]/g, '');

        if (normalized === '' || normalized === '-') {
            return 0;
        }

        const parsed = parseInt(normalized, 10);

        return Number.isFinite(parsed) ? parsed : 0;
    }

    function fieldValue(name) {
        const field = form.querySelector(`[name="${name}"]`);

        if (!field) {
            return '';
        }

        return field.value;
    }

    function formatPreviewValue(format, value) {
        if (value === null || value === undefined || value === '') {
            if (format === 'percent') {
                return '';
            }

            if (format === 'yen' || format === 'yen_signed') {
                return '0';
            }

            return '';
        }

        if (format === 'status') {
            return config.statuses[value] || value || '—';
        }

        if (format === 'property_type') {
            return config.propertyTypes[value] || value || '—';
        }

        if (format === 'percent') {
            const numeric = Number(value);

            if (!Number.isFinite(numeric)) {
                return '';
            }

            const text = Number.isInteger(numeric) ? String(numeric) : numeric.toFixed(1).replace(/\.0$/, '');

            return text + '%';
        }

        if (format === 'yen_signed') {
            const amount = parseInteger(value);

            if (amount < 0) {
                return '-' + Math.abs(amount).toLocaleString('ja-JP');
            }

            return amount.toLocaleString('ja-JP');
        }

        if (format === 'yen') {
            return parseInteger(value).toLocaleString('ja-JP');
        }

        return String(value);
    }

    function collectAdFees() {
        const fees = [];

        form.querySelectorAll('.deal-draft-ad-fee-form-row').forEach((row) => {
            const nameInput = row.querySelector('[name$="[agency_name]"]');
            const amountInput = row.querySelector('[name$="[amount]"]');
            const agencyName = nameInput?.value?.trim() || '';
            const amount = parseInteger(amountInput?.value);

            if (agencyName !== '' || amount !== 0) {
                fees.push({ agencyName, amount });
            }
        });

        return fees;
    }

    function propertyTaxTotal() {
        let total = 0;

        form.querySelectorAll('[name^="property_taxes"][name$="[amount]"]').forEach((input) => {
            total += parseInteger(input.value);
        });

        return total;
    }

    function calculateComputed() {
        let totalCost = config.totalCostKeys.reduce((sum, key) => sum + parseInteger(fieldValue(key)), 0);
        totalCost += propertyTaxTotal();

        const sellingPrice = parseInteger(fieldValue('expected_selling_price'));
        const adFeesTotal = collectAdFees().reduce((sum, fee) => sum + fee.amount, 0);
        const totalSellingAdminExpenses = adFeesTotal + config.sellingAdminKeys.reduce(
            (sum, key) => sum + parseInteger(fieldValue(key)),
            0,
        );

        let costRatio = null;
        let grossProfitMargin = null;

        if (sellingPrice > 0) {
            const ratio = totalCost / sellingPrice;
            costRatio = Math.round(ratio * 1000) / 10;
            grossProfitMargin = Math.round((1 - ratio) * 1000) / 10;
        }

        let estimatedOperatingProfitMargin = null;

        if (sellingPrice > 0) {
            estimatedOperatingProfitMargin = Math.round(
                (1 - ((totalCost + totalSellingAdminExpenses) / sellingPrice)) * 1000,
            ) / 10;
        }

        const expectedRent = parseInteger(fieldValue('expected_rent'));
        const annualRent = expectedRent * 12;
        let expectedSurfaceYield = null;
        let estimatedOwnershipYield = null;

        if (expectedRent > 0) {
            if (sellingPrice > 0) {
                expectedSurfaceYield = Math.round((annualRent / sellingPrice) * 1000) / 10;
            }

            if (totalCost > 0) {
                estimatedOwnershipYield = Math.round((annualRent / totalCost) * 1000) / 10;
            }
        }

        return {
            total_cost: totalCost,
            cost_ratio: costRatio,
            gross_profit_margin: grossProfitMargin,
            total_selling_admin_expenses: totalSellingAdminExpenses,
            estimated_operating_profit_margin: estimatedOperatingProfitMargin,
            expected_surface_yield: expectedSurfaceYield,
            estimated_ownership_yield: estimatedOwnershipYield,
        };
    }

    function updatePreviewCell(field, value, format) {
        document.querySelectorAll(`[data-preview-field="${field}"]:not([data-preview-fiscal-year])`).forEach((cell) => {
            const cellFormat = cell.dataset.previewFormat || format || 'text';
            cell.textContent = formatPreviewValue(cellFormat, value);
        });
    }

    function updatePropertyTaxCells() {
        form.querySelectorAll('[name^="property_taxes"][name$="[amount]"]').forEach((input) => {
            const name = input.getAttribute('name') || '';
            const match = name.match(/property_taxes\[(\d+)\]\[amount\]/);

            if (!match) {
                return;
            }

            const index = match[1];
            const yearInput = form.querySelector(`[name="property_taxes[${index}][fiscal_year]"]`);
            const fiscalYear = yearInput?.value;

            if (!fiscalYear) {
                return;
            }

            const cell = document.querySelector(`[data-preview-field="property_tax"][data-preview-fiscal-year="${fiscalYear}"]`);

            if (cell) {
                cell.textContent = parseInteger(input.value).toLocaleString('ja-JP');
            }
        });
    }

    function updateAdFeesPreview() {
        const cell = document.querySelector('[data-preview-ad-fees]');

        if (!cell) {
            return;
        }

        const fees = collectAdFees();

        if (fees.length === 0) {
            cell.innerHTML = '<span class="deal-draft-preview__empty">—</span>';

            return;
        }

        cell.innerHTML = fees.map((fee) => {
            const amountHtml = fee.amount
                ? `<span class="deal-draft-preview__ad-fee-amount">${fee.amount.toLocaleString('ja-JP')}</span>`
                : '';

            return `<div class="deal-draft-preview__ad-fee-line">${escapeHtml(fee.agencyName || '—')}${amountHtml}</div>`;
        }).join('');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function updateComputedPreview() {
        const computed = calculateComputed();

        Object.entries(computed).forEach(([field, value]) => {
            document.querySelectorAll(`[data-preview-field="${field}"][data-preview-computed="1"]`).forEach((cell) => {
                cell.textContent = formatPreviewValue(cell.dataset.previewFormat || 'yen', value);
            });

            const formComputed = document.getElementById(`field_${field}`);

            if (formComputed) {
                formComputed.textContent = formatPreviewValue(
                    computedFormat(field),
                    value,
                );
            }
        });
    }

    function computedFormat(field) {
        if (field.includes('ratio') || field.includes('margin') || field.includes('yield')) {
            return 'percent';
        }

        return 'yen';
    }

    function updatePreview() {
        document.querySelectorAll('[data-preview-field]:not([data-preview-computed]):not([data-preview-fiscal-year])').forEach((cell) => {
            const field = cell.dataset.previewField;

            if (!field || field === 'property_tax') {
                return;
            }

            const format = cell.dataset.previewFormat || 'text';
            updatePreviewCell(field, fieldValue(field), format);
        });

        const caseNumber = fieldValue('case_number');
        const headerCase = document.querySelector('[data-preview-header-case]');

        if (headerCase) {
            headerCase.textContent = caseNumber.trim() !== '' ? caseNumber.trim() : '—';
        }

        updatePropertyTaxCells();
        updateAdFeesPreview();
        updateComputedPreview();
    }

    function schedulePreviewUpdate() {
        clearTimeout(updateTimer);
        updateTimer = setTimeout(updatePreview, 80);
    }

    form.addEventListener('input', schedulePreviewUpdate);
    form.addEventListener('change', schedulePreviewUpdate);
    form.addEventListener('click', (event) => {
        if (event.target.closest('#dealDraftAdFeesAdd, .deal-draft-ad-fee-form-row__remove')) {
            schedulePreviewUpdate();
        }
    });

    updatePreview();
})();
</script>
