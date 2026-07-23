<div class="application-blocks-board">
    <div class="application-blocks-grid rental-archive-grid">
        @foreach ($archives as $archive)
            @include('admin.rental-property-archives._card', ['archive' => $archive])
        @endforeach
    </div>
</div>

@push('scripts')
<script>
(() => {
    document.querySelectorAll('.rental-archive-summary-card[data-detail-url]').forEach((card) => {
        const openDetail = () => {
            window.location.href = card.dataset.detailUrl;
        };

        card.addEventListener('click', (event) => {
            if (event.target.closest('input, button, a, select, textarea, label')) {
                return;
            }
            openDetail();
        });

        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openDetail();
            }
        });
    });
})();
</script>
@endpush
