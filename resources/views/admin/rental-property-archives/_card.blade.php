<article
    class="application-block rental-archive-card rental-archive-summary-card"
    data-archive-id="{{ $archive->id }}"
    data-detail-url="{{ route('admin.rental-property-archives.show', $archive) }}"
    tabindex="0"
    role="link"
    aria-label="{{ $archive->property_name ?: '物件名未設定' }}の詳細を表示"
>
    <div class="rental-archive-card__main">
        <div class="rental-archive-thumb rental-archive-thumb--static" aria-hidden="true">
            @if ($archive->images->isNotEmpty())
                <img
                    class="rental-archive-thumb__image"
                    src="{{ route('admin.rental-property-archives.images.show', [$archive, $archive->images->first()]) }}"
                    alt=""
                    loading="lazy"
                >
                <span class="rental-archive-thumb__count">{{ $archive->images->count() }}枚</span>
            @else
                <span class="rental-archive-thumb__placeholder">
                    画像未登録
                </span>
            @endif
        </div>

        <div class="rental-archive-card__info">
            <div class="application-block__header rental-archive-card__header">
                <h3 class="application-block__title">
                    {{ $archive->property_name ?: '（物件名未設定）' }}
                </h3>
            </div>

            <div class="application-block__body rental-archive-card__body">
                <div class="application-block__cells application-block__cells--stack">
                    <div class="application-block__cell">
                        <span class="application-block__cell-label">住所</span>
                        <div class="application-block__cell-value">{{ $archive->address ?: '—' }}</div>
                    </div>
                    <div class="application-block__cell">
                        <span class="application-block__cell-label">築年数</span>
                        <div class="application-block__cell-value">{{ $archive->building_age ?: '—' }}</div>
                    </div>
                    @if ($archive->google_drive_url)
                        <div class="application-block__cell">
                            <span class="application-block__cell-label">Googleドライブ</span>
                            <div class="application-block__cell-value">
                                <a
                                    href="{{ $archive->google_drive_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-sm font-semibold text-primary-600 hover:underline"
                                    onclick="event.stopPropagation()"
                                >
                                    リンクを開く
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="application-block__flags rental-archive-actions">
                <span class="ml-auto self-center text-xs font-semibold text-primary-600">詳細を見る →</span>
            </div>
        </div>
    </div>
</article>
