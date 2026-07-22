<article
    class="application-block rental-archive-card"
    data-archive-id="{{ $archive->id }}"
>
    <div class="rental-archive-card__main">
        <button
            type="button"
            class="rental-archive-thumb"
            data-lightbox-open
            aria-label="画像を大きく表示"
            @disabled($archive->images->isEmpty())
        >
            @if ($archive->images->isNotEmpty())
                <img
                    class="rental-archive-thumb__image"
                    src="{{ route('admin.rental-property-archives.images.show', [$archive, $archive->images->first()]) }}"
                    alt="{{ $archive->images->first()->original_name ?: '物件画像' }}"
                    loading="lazy"
                >
                <span class="rental-archive-thumb__count">{{ $archive->images->count() }}枚</span>
            @else
                <span class="rental-archive-thumb__placeholder">
                    画像未登録
                    <small>クリックで拡大表示</small>
                </span>
            @endif
        </button>

        <div class="rental-archive-card__info">
            <div class="application-block__header rental-archive-card__header">
                <h3 class="application-block__title">
                    {{ $archive->property_name ?: '（物件名未設定）' }}
                </h3>
                @if ($canEdit ?? false)
                    <form method="post" action="{{ route('admin.rental-property-archives.destroy', $archive) }}" onsubmit="return confirm('この物件ブロックを削除しますか？画像も一緒に削除されます。');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">削除</button>
                    </form>
                @endif
            </div>

            <div class="application-block__body rental-archive-card__body">
                <div class="application-block__cells application-block__cells--stack">
                    @foreach ($columnLabels as $field => $label)
                        <label class="application-block__cell application-block__cell--editable">
                            <span class="application-block__cell-label">{{ $label }}</span>
                            <input
                                type="text"
                                class="rental-archive-field application-inline-field"
                                data-archive-id="{{ $archive->id }}"
                                data-field="{{ $field }}"
                                value="{{ $archive->{$field} }}"
                                maxlength="255"
                                placeholder="{{ $label }}を入力"
                                @readonly(!($canEdit ?? false))
                            >
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="application-block__flags rental-archive-actions">
                @if ($canEdit ?? false)
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
                @else
                    <span class="text-xs text-slate-500">閲覧のみ</span>
                @endif
            </div>
        </div>
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
</article>
