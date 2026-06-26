@if ($paginator->hasPages())
    <nav class="flex flex-col items-center gap-3" role="navigation" aria-label="ページネーション">
        <p class="text-sm text-slate-600">
            全 <span class="font-medium text-slate-800">{{ $paginator->total() }}</span> 件中
            <span class="font-medium text-slate-800">{{ $paginator->firstItem() }}</span>
            〜
            <span class="font-medium text-slate-800">{{ $paginator->lastItem() }}</span>
            件を表示
        </p>

        <div class="inline-flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-400 cursor-not-allowed">
                    前へ
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    class="admin-pagination-link inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    前へ
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex items-center px-2 py-2 text-sm text-slate-400">…</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span
                                aria-current="page"
                                class="inline-flex min-w-[2.5rem] items-center justify-center rounded-lg bg-[#5383c3] px-3 py-2 text-sm font-semibold text-white shadow-sm"
                            >
                                {{ $page }}
                            </span>
                        @else
                            <a
                                href="{{ $url }}"
                                class="admin-pagination-link inline-flex min-w-[2.5rem] items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                                aria-label="{{ $page }}ページ目へ"
                            >
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    class="admin-pagination-link inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
                >
                    次へ
                </a>
            @else
                <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm font-medium text-slate-400 cursor-not-allowed">
                    次へ
                </span>
            @endif
        </div>
    </nav>
@endif
