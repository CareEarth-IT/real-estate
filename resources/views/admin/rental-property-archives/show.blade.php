@extends('layouts.admin')

@section('title', ($archive->property_name ?: '物件詳細') . ' — 賃貸物件保管 — ' . config('app.name'))

@section('content')
<div
    class="rental-archive-detail"
    data-archive-id="{{ $archive->id }}"
>
    <div class="mb-6">
        <a href="{{ route('admin.rental-property-archives.index') }}" class="mb-4 inline-flex text-sm text-primary-600 hover:underline">
            ← 賃貸物件保管へ戻る
        </a>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900" data-archive-title>
                    {{ $archive->property_name ?: '（物件名未設定）' }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    更新日時: {{ $archive->updated_at?->format('Y/m/d H:i') ?? '—' }}
                </p>
            </div>

            @if ($canEdit ?? false)
                <form method="post" action="{{ route('admin.rental-property-archives.destroy', $archive) }}" onsubmit="return confirm('この物件を削除しますか？画像も一緒に削除されます。');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline btn-sm text-rose-600 border-rose-200 hover:bg-rose-50">削除</button>
                </form>
            @endif
        </div>
    </div>

    <div class="application-blocks-board space-y-6">
        @include('admin.rental-property-archives._property-frame', ['archive' => $archive])
        @include('admin.rental-property-archives._money-frame', ['archive' => $archive])
        @include('admin.rental-property-archives._condition-frame', ['archive' => $archive])
        @include('admin.rental-property-archives._layout-frame', ['archive' => $archive])
        @include('admin.rental-property-archives._listing-frame', ['archive' => $archive])
        @include('admin.rental-property-archives._surroundings-frame', ['archive' => $archive])
        @include('admin.rental-property-archives._feature-frame', ['archive' => $archive])
        @include('admin.rental-property-archives._location-environment-frame', ['archive' => $archive])

        <section class="application-block rental-archive-detail__section">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-400">Googleドライブ</h3>
            <div class="application-block__cell application-block__cell--editable">
                <span class="application-block__cell-label">リンク</span>
                <input
                    type="url"
                    class="rental-archive-field application-inline-field"
                    data-archive-id="{{ $archive->id }}"
                    data-field="google_drive_url"
                    value="{{ $archive->google_drive_url }}"
                    maxlength="2000"
                    placeholder="Googleドライブのリンクを貼り付け"
                    @readonly(!($canEdit ?? false))
                >
                <a
                    href="{{ $archive->google_drive_url ?: '#' }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="rental-archive-drive-open mt-2 inline-flex text-sm font-semibold text-primary-600 hover:underline {{ $archive->google_drive_url ? '' : 'pointer-events-none opacity-40' }}"
                    @if (! $archive->google_drive_url) aria-disabled="true" @endif
                >
                    リンクを開く
                </a>
            </div>
        </section>

        <section class="application-block rental-archive-detail__section">
            <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-400">
                    画像
                    <span class="ml-1 font-normal normal-case text-slate-500" data-image-count>({{ $archive->images->count() }}枚)</span>
                </h3>
                @if ($canEdit ?? false)
                    <div class="flex flex-wrap items-center gap-2">
                        <input
                            type="file"
                            class="rental-archive-image-input"
                            accept="image/jpeg,image/png,.jpg,.jpeg,.png"
                            multiple
                            hidden
                        >
                        <button type="button" class="btn btn-outline btn-sm rental-archive-upload-btn">
                            画像を追加
                        </button>
                        <span class="text-xs text-slate-500">複数選択可（jpg / png）</span>
                    </div>
                @endif
            </div>

            <div class="rental-archive-gallery" data-image-gallery style="display:grid;grid-template-columns:repeat(2,150px);gap:12px;width:max-content;max-width:100%;">
                @forelse ($archive->images as $index => $image)
                    <div class="rental-archive-gallery__item" data-image-id="{{ $image->id }}" style="width:150px;height:150px;position:relative;overflow:hidden;">
                        <button
                            type="button"
                            class="rental-archive-gallery__thumb"
                            data-lightbox-open
                            data-lightbox-index="{{ $index }}"
                            aria-label="画像を大きく表示"
                            style="display:block;width:150px;height:150px;padding:0;border:0;overflow:hidden;cursor:pointer;"
                        >
                            <img
                                src="{{ route('admin.rental-property-archives.images.show', [$archive, $image]) }}"
                                alt="{{ $image->original_name ?: '物件画像' }}"
                                loading="lazy"
                                width="150"
                                height="150"
                                style="width:150px;height:150px;max-width:150px;max-height:150px;object-fit:cover;display:block;"
                            >
                        </button>
                        @if ($canEdit ?? false)
                            <button
                                type="button"
                                class="rental-archive-gallery__delete"
                                data-image-delete
                                data-image-id="{{ $image->id }}"
                                aria-label="画像を削除"
                            >削除</button>
                        @endif
                    </div>
                @empty
                    <p class="rental-archive-gallery__empty" data-gallery-empty>画像がまだありません。</p>
                @endforelse
            </div>

            <div class="rental-archive-image-data" hidden>
                @foreach ($archive->images as $image)
                    <span
                        data-image-id="{{ $image->id }}"
                        data-image-url="{{ route('admin.rental-property-archives.images.show', [$archive, $image]) }}"
                        data-image-name="{{ $image->original_name ?: '物件画像' }}"
                    ></span>
                @endforeach
            </div>
        </section>
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
@endsection

@push('scripts')
<script>
(() => {
    const root = document.querySelector('.rental-archive-detail');
    if (!root) {
        return;
    }

    const canEdit = @json((bool) ($canEdit ?? false));
    const archiveId = root.dataset.archiveId;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const lightbox = document.getElementById('rental-archive-lightbox');
    const track = lightbox?.querySelector('[data-lightbox-track]');
    const counter = lightbox?.querySelector('[data-lightbox-counter]');
    const prevBtn = lightbox?.querySelector('[data-lightbox-prev]');
    const nextBtn = lightbox?.querySelector('[data-lightbox-next]');
    const gallery = root.querySelector('[data-image-gallery]');
    const dataBox = root.querySelector('.rental-archive-image-data');
    const countLabel = root.querySelector('[data-image-count]');
    const fileInput = root.querySelector('.rental-archive-image-input');
    const uploadButton = root.querySelector('.rental-archive-upload-btn');

    let currentIndex = 0;
    let images = [];

    function adminUrl(path) {
        return typeof adminApiUrl === 'function' ? adminApiUrl(path) : path;
    }

    function readImages() {
        return Array.from(root.querySelectorAll('.rental-archive-image-data [data-image-url]')).map((node) => ({
            id: node.dataset.imageId,
            url: node.dataset.imageUrl,
            name: node.dataset.imageName || '物件画像',
        }));
    }

    function updateCount() {
        if (countLabel) {
            countLabel.textContent = `(${readImages().length}枚)`;
        }
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

    function openLightbox(startIndex = 0) {
        images = readImages();
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
        images = [];
        if (track) {
            track.innerHTML = '';
        }
    }

    function ensureEmptyMessage() {
        if (!gallery) {
            return;
        }
        const hasItems = gallery.querySelector('.rental-archive-gallery__item');
        let empty = gallery.querySelector('[data-gallery-empty]');
        if (hasItems) {
            empty?.remove();
            return;
        }
        if (!empty) {
            empty = document.createElement('p');
            empty.className = 'rental-archive-gallery__empty';
            empty.dataset.galleryEmpty = '';
            empty.textContent = '画像がまだありません。';
            gallery.appendChild(empty);
        }
    }

    function appendGalleryItem(image, index) {
        if (!gallery || !dataBox) {
            return;
        }

        gallery.querySelector('[data-gallery-empty]')?.remove();

        const item = document.createElement('div');
        item.className = 'rental-archive-gallery__item';
        item.dataset.imageId = String(image.id);
        item.innerHTML = `
            <button type="button" class="rental-archive-gallery__thumb" data-lightbox-open data-lightbox-index="${index}" aria-label="画像を大きく表示" style="display:block;width:150px;height:150px;padding:0;border:0;overflow:hidden;cursor:pointer;">
                <img src="${image.url}" alt="${image.original_name || '物件画像'}" loading="lazy" width="150" height="150" style="width:150px;height:150px;max-width:150px;max-height:150px;object-fit:cover;display:block;">
            </button>
            ${canEdit ? `<button type="button" class="rental-archive-gallery__delete" data-image-delete data-image-id="${image.id}" aria-label="画像を削除">削除</button>` : ''}
        `;
        item.style.cssText = 'width:150px;height:150px;position:relative;overflow:hidden;';
        gallery.appendChild(item);

        const node = document.createElement('span');
        node.dataset.imageId = String(image.id);
        node.dataset.imageUrl = image.url;
        node.dataset.imageName = image.original_name || '物件画像';
        dataBox.appendChild(node);

        updateCount();
        reindexLightboxButtons();
    }

    function reindexLightboxButtons() {
        gallery?.querySelectorAll('[data-lightbox-open]').forEach((btn, index) => {
            btn.dataset.lightboxIndex = String(index);
        });
    }

    async function saveField(field, value) {
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

    root.querySelectorAll('.rental-archive-field').forEach((field) => {
        const isCheckbox = field.type === 'checkbox';
        const isRadio = field.type === 'radio';
        const isSlot = field.dataset.slotIndex !== undefined;
        const isSurroundings = field.dataset.surroundingsIndex !== undefined;
        const isTagGroup = field.classList.contains('rental-archive-tag-field');
        let previous = isCheckbox ? field.checked : field.value;
        let timer = null;

        const collectSlotValues = () => {
            const group = root.querySelectorAll(`.rental-archive-field[data-field="${field.dataset.field}"][data-slot-index]`);
            return Array.from(group)
                .sort((a, b) => Number(a.dataset.slotIndex) - Number(b.dataset.slotIndex))
                .map((el) => (el.value === '' ? null : Number(el.value)));
        };

        const collectTagGroupValues = () => {
            return Array.from(
                root.querySelectorAll(`.rental-archive-tag-field[data-field="${field.dataset.field}"]:checked`)
            ).map((el) => el.value);
        };

        const collectSurroundings = () => {
            const rowCount = root.querySelectorAll('[data-surroundings-row]').length;
            const rows = [];
            for (let i = 0; i < rowCount; i += 1) {
                const get = (key) => root.querySelector(
                    `.rental-archive-field[data-field="surroundings"][data-surroundings-index="${i}"][data-surroundings-key="${key}"]`
                );
                const categoryEl = get('category');
                const placeEl = get('place_name');
                const metersEl = get('meters');
                const driveEl = get('google_drive_url');
                rows.push({
                    category: categoryEl?.value || null,
                    place_name: placeEl?.value || null,
                    meters: metersEl?.value === '' || metersEl?.value == null ? null : Number(metersEl.value),
                    google_drive_url: driveEl?.value || null,
                });
            }
            return rows;
        };

        const syncSurroundingsOpenLink = () => {
            const index = field.dataset.surroundingsIndex;
            if (field.dataset.surroundingsKey !== 'google_drive_url' || index == null) {
                return;
            }
            const openLink = root.querySelector(`[data-surroundings-open="${index}"]`);
            if (!openLink) {
                return;
            }
            const url = (field.value || '').trim();
            openLink.href = url || '#';
            openLink.classList.toggle('pointer-events-none', !url);
            openLink.classList.toggle('opacity-40', !url);
            if (url) {
                openLink.removeAttribute('aria-disabled');
            } else {
                openLink.setAttribute('aria-disabled', 'true');
            }
        };

        const currentValue = () => {
            if (isCheckbox) {
                return field.checked;
            }
            if (isRadio) {
                return field.checked ? field.value : previous;
            }
            if (isSlot) {
                return collectSlotValues();
            }
            if (isSurroundings) {
                return collectSurroundings();
            }
            if (isTagGroup) {
                return collectTagGroupValues();
            }
            return field.value;
        };

        const persist = async () => {
            if (!canEdit) {
                return;
            }
            if (isRadio && !field.checked) {
                return;
            }

            const value = currentValue();
            if (isSlot || isSurroundings || isTagGroup) {
                const comparable = JSON.stringify(value);
                if (comparable === previous) {
                    return;
                }
            } else {
                const comparable = isCheckbox ? Boolean(value) : (value ?? '');
                const previousComparable = isCheckbox ? Boolean(previous) : (previous ?? '');
                if (String(comparable) === String(previousComparable) && !isRadio) {
                    return;
                }
                if (isRadio && String(value) === String(previous)) {
                    return;
                }
            }

            try {
                const payloadValue = isCheckbox && !isTagGroup
                    ? Boolean(value)
                    : ((isSlot || isSurroundings || isTagGroup) ? value : (value === '' || value === null ? null : value));
                await saveField(field.dataset.field, payloadValue);
                previous = (isSlot || isSurroundings || isTagGroup)
                    ? JSON.stringify(value)
                    : (isCheckbox ? field.checked : (isRadio ? field.value : field.value));

                if (field.dataset.field === 'property_name') {
                    const title = root.querySelector('[data-archive-title]');
                    if (title) {
                        title.textContent = field.value || '（物件名未設定）';
                    }
                }
                if (field.dataset.field === 'google_drive_url') {
                    const openLink = field.closest('.application-block__cell, .rental-archive-detail__section')?.querySelector('.rental-archive-drive-open');
                    if (openLink) {
                        const url = (field.value || '').trim();
                        openLink.href = url || '#';
                        openLink.classList.toggle('pointer-events-none', !url);
                        openLink.classList.toggle('opacity-40', !url);
                        if (url) {
                            openLink.removeAttribute('aria-disabled');
                        } else {
                            openLink.setAttribute('aria-disabled', 'true');
                        }
                    }
                }
                if (isSurroundings) {
                    syncSurroundingsOpenLink();
                }
            } catch (error) {
                if (isCheckbox) {
                    field.checked = Boolean(previous);
                } else if (isRadio) {
                    const group = root.querySelectorAll(`input[type="radio"][data-field="${field.dataset.field}"]`);
                    group.forEach((radio) => {
                        radio.checked = radio.value === previous;
                    });
                } else if (isSlot) {
                    try {
                        const restored = JSON.parse(previous || '[]');
                        const group = root.querySelectorAll(`.rental-archive-field[data-field="${field.dataset.field}"][data-slot-index]`);
                        group.forEach((el) => {
                            const idx = Number(el.dataset.slotIndex);
                            el.value = restored[idx] ?? '';
                        });
                    } catch (_) {
                        // ignore restore parse errors
                    }
                } else if (isSurroundings) {
                    try {
                        const restored = JSON.parse(previous || '[]');
                        restored.forEach((row, index) => {
                            ['category', 'place_name', 'meters', 'google_drive_url'].forEach((key) => {
                                const el = root.querySelector(
                                    `.rental-archive-field[data-field="surroundings"][data-surroundings-index="${index}"][data-surroundings-key="${key}"]`
                                );
                                if (el) {
                                    el.value = row?.[key] ?? '';
                                }
                            });
                            const openLink = root.querySelector(`[data-surroundings-open="${index}"]`);
                            if (openLink) {
                                const url = (row?.google_drive_url || '').trim();
                                openLink.href = url || '#';
                                openLink.classList.toggle('pointer-events-none', !url);
                                openLink.classList.toggle('opacity-40', !url);
                            }
                        });
                    } catch (_) {
                        // ignore restore parse errors
                    }
                } else if (isTagGroup) {
                    try {
                        const restored = JSON.parse(previous || '[]');
                        root.querySelectorAll(`.rental-archive-tag-field[data-field="${field.dataset.field}"]`).forEach((el) => {
                            el.checked = restored.includes(el.value);
                        });
                    } catch (_) {
                        // ignore restore parse errors
                    }
                } else {
                    field.value = previous ?? '';
                }
                alert(error.message);
            }
        };

        if (isSlot) {
            previous = JSON.stringify(collectSlotValues());
        }
        if (isSurroundings) {
            previous = JSON.stringify(collectSurroundings());
        }
        if (isTagGroup) {
            previous = JSON.stringify(collectTagGroupValues());
        }

        if (isCheckbox || isRadio || field.tagName === 'SELECT') {
            field.addEventListener('change', persist);
            return;
        }

        field.addEventListener('change', persist);
        field.addEventListener('blur', persist);
        field.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(persist, 700);
            if (isSurroundings && field.dataset.surroundingsKey === 'google_drive_url') {
                syncSurroundingsOpenLink();
            }
        });
    });

    gallery?.addEventListener('click', async (event) => {
        const openBtn = event.target.closest('[data-lightbox-open]');
        if (openBtn) {
            openLightbox(Number(openBtn.dataset.lightboxIndex || 0));
            return;
        }

        const deleteBtn = event.target.closest('[data-image-delete]');
        if (!deleteBtn || !canEdit) {
            return;
        }

        const imageId = deleteBtn.dataset.imageId;
        if (!imageId || !confirm('この画像を削除しますか？')) {
            return;
        }

        try {
            const response = await fetch(adminUrl(`/admin/rental-property-archives/${archiveId}/images/${imageId}`), {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(data.message || '画像の削除に失敗しました。');
            }

            gallery.querySelector(`.rental-archive-gallery__item[data-image-id="${imageId}"]`)?.remove();
            dataBox?.querySelector(`[data-image-id="${imageId}"]`)?.remove();
            updateCount();
            reindexLightboxButtons();
            ensureEmptyMessage();
        } catch (error) {
            alert(error.message);
        }
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

            let nextIndex = readImages().length;
            (data.images || []).forEach((image) => {
                appendGalleryItem(image, nextIndex);
                nextIndex += 1;
            });
        } catch (error) {
            alert(error.message);
        } finally {
            fileInput.value = '';
            uploadButton.disabled = false;
        }
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

    const storageKey = `rental-archive-tag-panel-height:${archiveId}`;
    root.querySelectorAll('[data-tag-panel-resizer]').forEach((resizer) => {
        const panel = resizer.closest('.rental-archive-tag-panel');
        const scroll = panel?.querySelector('[data-tag-panel-scroll]');
        if (!panel || !scroll) {
            return;
        }

        const minHeight = 120;
        const maxHeight = () => Math.max(minHeight, Math.floor(window.innerHeight * 0.7));
        const saved = Number(localStorage.getItem(storageKey) || 0);
        if (saved >= minHeight) {
            scroll.style.height = `${Math.min(saved, maxHeight())}px`;
        }

        let startY = 0;
        let startHeight = 0;

        const onMove = (event) => {
            const clientY = event.touches?.[0]?.clientY ?? event.clientY;
            const next = Math.min(maxHeight(), Math.max(minHeight, startHeight + (clientY - startY)));
            scroll.style.height = `${next}px`;
        };

        const onEnd = () => {
            panel.classList.remove('is-resizing');
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onEnd);
            document.removeEventListener('touchmove', onMove);
            document.removeEventListener('touchend', onEnd);
            localStorage.setItem(storageKey, String(parseInt(scroll.style.height, 10) || startHeight));
        };

        const onStart = (event) => {
            event.preventDefault();
            startY = event.touches?.[0]?.clientY ?? event.clientY;
            startHeight = scroll.getBoundingClientRect().height;
            panel.classList.add('is-resizing');
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onEnd);
            document.addEventListener('touchmove', onMove, { passive: false });
            document.addEventListener('touchend', onEnd);
        };

        resizer.addEventListener('mousedown', onStart);
        resizer.addEventListener('touchstart', onStart, { passive: false });
    });
})();
</script>
@endpush
