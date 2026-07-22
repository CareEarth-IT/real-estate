@props([
    'value' => '',
])

<form method="GET" {{ $attributes->merge(['class' => 'admin-search-form flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2 w-full sm:w-auto sm:max-w-md']) }}>
    <div class="relative w-full sm:flex-1 sm:min-w-[200px]">
        <input
            type="search"
            name="search"
            value="{{ $value }}"
            placeholder="キーワードで検索"
            class="w-full rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-800 placeholder:text-slate-400 focus:border-[#5383c3] focus:outline-none focus:ring-2 focus:ring-[#5383c3]/20"
        >
    </div>
    <div class="flex shrink-0 gap-1.5">
        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-md bg-[#5383c3] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90 transition-opacity"
        >
            検索
        </button>
        @if ($value !== '')
            <a
                href="{{ url()->current() }}"
                class="admin-search-clear-link inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 transition-colors"
            >
                クリア
            </a>
        @endif
    </div>
</form>
