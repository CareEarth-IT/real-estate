<script>
(function () {
    const existingGroup = document.getElementById('existingAddressGroup');
    const newGroup = document.getElementById('newAddressGroup');
    const addressSelect = document.getElementById('property_address');
    const modeRadios = document.querySelectorAll('input[name="address_mode"]');

    function setNewRequired(on) {
        ['prefecture', 'city', 'street'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.required = on;
        });
        if (addressSelect) addressSelect.required = !on;
    }

    function toggleAddressMode() {
        const isNew = document.querySelector('input[name="address_mode"]:checked')?.value === 'new';
        if (existingGroup) existingGroup.hidden = isNew;
        if (newGroup) newGroup.hidden = !isNew;
        setNewRequired(isNew);
    }

    modeRadios.forEach(function (r) { r.addEventListener('change', toggleAddressMode); });
    toggleAddressMode();

    const existingSalesGroup = document.getElementById('existingSalesGroup');
    const newSalesGroup = document.getElementById('newSalesGroup');
    const salesSelect = document.getElementById('sales_person');
    const salesModeRadios = document.querySelectorAll('input[name="sales_person_mode"]');

    function toggleSalesMode() {
        const isNew = document.querySelector('input[name="sales_person_mode"]:checked')?.value === 'new';
        if (existingSalesGroup) existingSalesGroup.hidden = isNew;
        if (newSalesGroup) newSalesGroup.hidden = !isNew;
        const newInput = document.getElementById('new_sales_person');
        if (newInput) newInput.required = false;
        if (salesSelect) salesSelect.required = false;
    }

    salesModeRadios.forEach(function (r) { r.addEventListener('change', toggleSalesMode); });
    toggleSalesMode();

    const buildingInput = document.getElementById('building_price');
    const landInput = document.getElementById('land_price');
    const totalDisplay = document.getElementById('total_price_display');
    const totalInputWrap = document.getElementById('total_price_input_wrap');
    const totalInput = document.getElementById('total_price');
    const priceModeRadios = document.querySelectorAll('input[name="price_mode"]');

    function updateTotalDisplay() {
        const total = (parseInt(buildingInput.value, 10) || 0) + (parseInt(landInput.value, 10) || 0);
        totalDisplay.textContent = '¥' + total.toLocaleString('ja-JP');
    }

    function togglePriceMode() {
        const isTotal = document.querySelector('input[name="price_mode"]:checked')?.value === 'total';
        if (isTotal) {
            buildingInput.value = '0';
            landInput.value = '0';
            buildingInput.readOnly = true;
            landInput.readOnly = true;
            totalInputWrap.hidden = false;
            totalDisplay.hidden = true;
        } else {
            buildingInput.readOnly = false;
            landInput.readOnly = false;
            totalInputWrap.hidden = true;
            totalDisplay.hidden = false;
            updateTotalDisplay();
        }
    }

    priceModeRadios.forEach(function (r) { r.addEventListener('change', togglePriceMode); });
    buildingInput.addEventListener('input', updateTotalDisplay);
    landInput.addEventListener('input', updateTotalDisplay);
    togglePriceMode();

    document.querySelectorAll('.file-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const preview = document.getElementById('preview_' + input.name);
            preview.innerHTML = '';
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            const ext = file.name.split('.').pop().toLowerCase();
            if (ext === 'pdf') {
                preview.innerHTML = '<span class="preview-pdf">' + file.name + '</span>';
            } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.className = 'preview-thumb';
                preview.appendChild(img);
            }
        });
    });
})();
</script>
