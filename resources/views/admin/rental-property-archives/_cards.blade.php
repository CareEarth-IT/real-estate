<div class="application-blocks-board">
    <div class="application-blocks-grid rental-archive-grid">
        @foreach ($archives as $archive)
            @include('admin.rental-property-archives._card', ['archive' => $archive])
        @endforeach
    </div>
</div>

<div class="rental-archive-lightbox" id="rental-archive-lightbox" hidden>
    <div class="rental-archive-lightbox__backdrop" data-lightbox-close></div>
    <div class="rental-archive-lightbox__dialog" role="dialog" aria-modal="true" aria-label="物件画像プレビュー">
        <button type="button" class="rental-archive-lightbox__close" data-lightbox-close aria-label="閉じる">×</button>
        <div class="rental-archive-lightbox__frame">
            <button type="button" class="rental-archive-lightbox__nav rental-archive-lightbox__nav--prev" data-lightbox-prev aria-label="前の画像">
                <span aria-hidden="true">‹</span>
            </button>
            <div class="rental-archive-lightbox__stage">
                <div class="rental-archive-lightbox__track" data-lightbox-track></div>
                <div class="rental-archive-lightbox__counter" data-lightbox-counter>1/1</div>
            </div>
            <button type="button" class="rental-archive-lightbox__nav rental-archive-lightbox__nav--next" data-lightbox-next aria-label="次の画像">
                <span aria-hidden="true">›</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const canEdit = @json((bool) ($canEdit ?? false));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const lightbox = document.getElementById('rental-archive-lightbox');
    const track = lightbox?.querySelector('[data-lightbox-track]');
    const counter = lightbox?.querySelector('[data-lightbox-counter]');
    const prevBtn = lightbox?.querySelector('[data-lightbox-prev]');
    const nextBtn = lightbox?.querySelector('[data-lightbox-next]');

    let activeCard = null;
    let currentIndex = 0;
    let images = [];

    function adminUrl(path) {
        return typeof adminApiUrl === 'function' ? adminApiUrl(path) : path;
    }

    function readImages(card) {
        return Array.from(card.querySelectorAll('.rental-archive-image-data [data-image-url]')).map((node) => ({
            id: node.dataset.imageId,
            url: node.dataset.imageUrl,
            name: node.dataset.imageName || '物件画像',
        }));
    }

    function updateThumb(card) {
        const list = readImages(card);
        const thumb = card.querySelector('.rental-archive-thumb');
        const openBtn = card.querySelector('[data-lightbox-open]');
        if (!thumb || !openBtn) {
            return;
        }

        openBtn.disabled = list.length === 0;

        if (list.length === 0) {
            thumb.innerHTML = '<span class="rental-archive-thumb__placeholder">画像未登録<small>クリックで拡大表示</small></span>';
            return;
        }

        thumb.innerHTML = `
            <img class="rental-archive-thumb__image" src="${list[0].url}" alt="${list[0].name}" loading="lazy">
            <span class="rental-archive-thumb__count">${list.length}枚</span>
        `;
    }

    function setCounter() {
        if (!counter) {
            return;
        }
        const total = images.length;
        counter.textContent = total ? `${currentIndex + 1}/${total}` : '0/0';
        if (prevBtn) {
            prevBtn.hidden = total <= 1;
        }
        if (nextBtn) {
            nextBtn.hidden = total <= 1;
        }
    }

    function renderLightboxSlides() {
        if (!track) {
            return;
        }
        track.innerHTML = '';
        images.forEach((image) => {
            const slide = document.createElement('div');
            slide.className = 'rental-archive-lightbox__slide';
            const img = document.createElement('img');
            img.src = image.url;
            img.alt = image.name;
            slide.appendChild(img);
            track.appendChild(slide);
        });
    }

    function goTo(index, behavior = 'smooth') {
        if (!track || images.length === 0) {
            return;
        }
        currentIndex = (index + images.length) % images.length;
        const slideWidth = track.clientWidth || track.parentElement?.clientWidth || 0;
        track.scrollTo({ left: slideWidth * currentIndex, behavior });
        setCounter();
    }

    function openLightbox(card, startIndex = 0) {
        activeCard = card;
        images = readImages(card);
        if (!lightbox || images.length === 0) {
            return;
        }
        renderLightboxSlides();
        lightbox.hidden = false;
        document.body.classList.add('rental-archive-lightbox-open');
        requestAnimationFrame(() => goTo(startIndex, 'auto'));
    }

    function closeLightbox() {
        if (!lightbox) {
            return;
        }
        lightbox.hidden = true;
        document.body.classList.remove('rental-archive-lightbox-open');
        activeCard = null;
        images = [];
        if (track) {
            track.innerHTML = '';
        }
    }

    async function saveField(archiveId, field, value) {
        const response = await fetch(adminUrl(`/admin/rental-property-archives/${archiveId}/fields`), {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ field, value }),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(data.errors?.value?.[0] || data.message || '保存に失敗しました。');
        }
    }

    document.querySelectorAll('.rental-archive-field').forEach((field) => {
        let previous = field.value;
        let timer = null;

        const persist = async () => {
            if (!canEdit || field.value === previous) {
                return;
            }
            try {
                await saveField(field.dataset.archiveId, field.dataset.field, field.value || null);
                previous = field.value;
                if (field.dataset.field === 'property_name') {
                    const title = field.closest('.rental-archive-card')?.querySelector('.application-block__title');
                    if (title) {
                        title.textContent = field.value || '（物件名未設定）';
                    }
                }
            } catch (error) {
                field.value = previous;
                alert(error.message);
            }
        };

        field.addEventListener('change', persist);
        field.addEventListener('blur', persist);
        field.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(persist, 700);
        });
    });

    document.querySelectorAll('.rental-archive-card').forEach((card) => {
        const archiveId = card.dataset.archiveId;
        const fileInput = card.querySelector('.rental-archive-image-input');
        const uploadButton = card.querySelector('.rental-archive-upload-btn');
        const dataBox = card.querySelector('.rental-archive-image-data');

        card.querySelector('[data-lightbox-open]')?.addEventListener('click', () => {
            openLightbox(card, 0);
        });

        uploadButton?.addEventListener('click', () => fileInput?.click());

        fileInput?.addEventListener('change', async () => {
            if (!canEdit || !fileInput.files?.length) {
                return;
            }

            const formData = new FormData();
            Array.from(fileInput.files).forEach((file) => formData.append('images[]', file));

            uploadButton.disabled = true;
            try {
                const response = await fetch(adminUrl(`/admin/rental-property-archives/${archiveId}/images`), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData,
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) {
                    throw new Error(data.errors?.['images.0']?.[0] || data.message || '画像の登録に失敗しました。');
                }

                (data.images || []).forEach((image) => {
                    const node = document.createElement('span');
                    node.dataset.imageId = String(image.id);
                    node.dataset.imageUrl = image.url;
                    node.dataset.imageName = image.original_name || '物件画像';
                    dataBox?.appendChild(node);
                });
                updateThumb(card);
            } catch (error) {
                alert(error.message);
            } finally {
                fileInput.value = '';
                uploadButton.disabled = false;
            }
        });
    });

    lightbox?.querySelectorAll('[data-lightbox-close]').forEach((el) => {
        el.addEventListener('click', closeLightbox);
    });
    prevBtn?.addEventListener('click', () => goTo(currentIndex - 1));
    nextBtn?.addEventListener('click', () => goTo(currentIndex + 1));

    track?.addEventListener('scroll', () => {
        if (!track || images.length === 0) {
            return;
        }
        const width = track.clientWidth || 1;
        const index = Math.round(track.scrollLeft / width);
        if (index !== currentIndex && index >= 0 && index < images.length) {
            currentIndex = index;
            setCounter();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (lightbox?.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            closeLightbox();
        } else if (event.key === 'ArrowLeft') {
            goTo(currentIndex - 1);
        } else if (event.key === 'ArrowRight') {
            goTo(currentIndex + 1);
        }
    });

    const focusId = @json(session('focus_archive_id'));
    if (focusId) {
        document.querySelector(`[data-archive-id="${focusId}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
})();
</script>
@endpush
