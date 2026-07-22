<script>
    (function initRentalIncomeTerminateModal() {
        const modal = document.getElementById('rental-income-terminate-modal');
        const cancelButton = document.getElementById('rental-income-terminate-cancel');
        const openButtons = document.querySelectorAll('.rental-income-terminate-open');
        const terminatedOnInput = modal?.querySelector('input[name="terminated_on"]');
        const titleEl = document.getElementById('rental-income-terminate-modal-title');

        if (!modal || openButtons.length === 0) {
            return;
        }

        const openModal = (terminatedOn = null, paymentMonthLabel = null) => {
            if (terminatedOnInput && terminatedOn) {
                terminatedOnInput.value = terminatedOn;
            }

            if (titleEl) {
                titleEl.textContent = paymentMonthLabel
                    ? `契約解約（${paymentMonthLabel}）`
                    : '契約解約';
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
            const firstField = modal.querySelector('input[name="move_out_type"]');
            firstField?.focus();
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        openButtons.forEach((button) => {
            button.addEventListener('click', () => {
                openModal(
                    button.dataset.terminatedOn || null,
                    button.dataset.paymentMonthLabel || null,
                );
            });
        });

        cancelButton?.addEventListener('click', closeModal);

        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && modal.classList.contains('flex')) {
                closeModal();
            }
        });

        @if ($errors->has('terminated_on') || $errors->has('move_out_type') || $errors->has('move_out_reason') || $errors->has('move_out_cost'))
        openModal(
            @json(old('terminated_on')),
            null,
        );
        @endif
    })();
</script>
