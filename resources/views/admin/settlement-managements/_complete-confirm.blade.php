<div id="settlement-complete-confirm" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 p-4">
    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="settlement-complete-confirm-message">
        <p id="settlement-complete-confirm-message" class="text-base text-slate-800">振り込みは完了しましたか？</p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" id="settlement-complete-confirm-back" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                戻る
            </button>
            <button type="button" id="settlement-complete-confirm-ok" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700">
                完了
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (() => {
        const confirmComplete = () => new Promise((resolve) => {
            const overlay = document.getElementById('settlement-complete-confirm');
            const okButton = document.getElementById('settlement-complete-confirm-ok');
            const backButton = document.getElementById('settlement-complete-confirm-back');

            if (!overlay || !okButton || !backButton) {
                resolve(window.confirm('振り込みは完了しましたか？'));
                return;
            }

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');

            const cleanup = (result) => {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
                okButton.removeEventListener('click', onOk);
                backButton.removeEventListener('click', onBack);
                overlay.removeEventListener('click', onOverlay);
                resolve(result);
            };

            const onOk = () => cleanup(true);
            const onBack = () => cleanup(false);
            const onOverlay = (event) => {
                if (event.target === overlay) {
                    cleanup(false);
                }
            };

            okButton.addEventListener('click', onOk);
            backButton.addEventListener('click', onBack);
            overlay.addEventListener('click', onOverlay);
        });

        document.querySelectorAll('.settlement-complete-button').forEach((button) => {
            button.addEventListener('click', async (event) => {
                event.preventDefault();
                event.stopPropagation();

                if (!await confirmComplete()) {
                    return;
                }

                button.disabled = true;

                try {
                    const response = await fetch(button.dataset.completeUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw new Error(data.message || '完了処理に失敗しました。');
                    }
                    window.location.reload();
                } catch (error) {
                    button.disabled = false;
                    alert(error.message);
                }
            });
        });
    })();
</script>
@endpush
