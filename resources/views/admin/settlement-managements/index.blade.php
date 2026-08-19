@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">決済金管理</h2>
            <p class="mt-1 text-sm text-slate-500">
                表示件数: <strong class="text-slate-700">{{ $settlementManagements->total() }}</strong> 件
                @if ($search !== '')
                    <span class="text-slate-400">（「{{ $search }}」で絞り込み中）</span>
                @endif
                @if ($transferDate)
                    <span class="text-slate-400">（決済振込日: {{ \Illuminate\Support\Carbon::parse($transferDate)->format('Y/m/d') }}）</span>
                @endif
            </p>
        </div>
        <x-admin-search-form
            :value="$search"
            :preserve="array_filter([
                'transfer_date' => $transferDate ?: null,
                'sort' => $sort ?: null,
                'direction' => $sort ? $direction : null,
            ])"
        />
    </div>

    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <form method="GET" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
            @if ($search !== '')
                <input type="hidden" name="search" value="{{ $search }}">
            @endif
            <div>
                <label for="settlement-transfer-date" class="mb-1 block text-xs font-medium text-slate-500">決済振込日で絞り込み</label>
                <input
                    type="date"
                    id="settlement-transfer-date"
                    name="transfer_date"
                    value="{{ $transferDate }}"
                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-[#5383c3] focus:outline-none focus:ring-2 focus:ring-[#5383c3]/20"
                >
            </div>
            <div>
                <label for="settlement-sort" class="mb-1 block text-xs font-medium text-slate-500">並び替え</label>
                <select
                    id="settlement-sort"
                    name="sort"
                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-[#5383c3] focus:outline-none focus:ring-2 focus:ring-[#5383c3]/20"
                >
                    <option value="" @selected($sort === '')>作成日時（新しい順）</option>
                    @foreach ($sortOptions as $value => $label)
                        <option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="settlement-direction" class="mb-1 block text-xs font-medium text-slate-500">順序</label>
                <select
                    id="settlement-direction"
                    name="direction"
                    class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 focus:border-[#5383c3] focus:outline-none focus:ring-2 focus:ring-[#5383c3]/20"
                >
                    <option value="asc" @selected($direction === 'asc')>昇順（近い順）</option>
                    <option value="desc" @selected($direction === 'desc')>降順（遠い順）</option>
                </select>
            </div>
            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-md bg-[#5383c3] px-3 py-1.5 text-sm font-medium text-white hover:opacity-90 transition-opacity"
            >
                適用
            </button>
            @if ($transferDate || $sort !== '')
                <a
                    href="{{ route('admin.settlement-managements.index', array_filter(['search' => $search !== '' ? $search : null])) }}"
                    class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50"
                >
                    条件をクリア
                </a>
            @endif
        </form>
        @if ($sort !== '')
            <p class="mt-2 text-xs text-slate-500">
                「{{ $sortOptions[$sort] }}」を{{ $direction === 'desc' ? '降順' : '昇順' }}で表示しています。未記載は末尾です。
            </p>
        @endif
    </div>

    @if (($upcomingTransferCount ?? 0) > 0)
        <div class="settlement-transfer-countdown-notice mb-6" role="status">
            <p class="settlement-transfer-countdown-notice__title">決済振込日1週間前のお知らせ</p>
            <p class="settlement-transfer-countdown-notice__body">
                決済振込日まで7日以内の案件が
                <strong>{{ $upcomingTransferCount }} 件</strong>
                あります。未完了の案件はカウントダウンを表示しています。
            </p>
        </div>
    @endif

    @if ($settlementManagements->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-12 text-center text-slate-500 shadow-sm">
            @if ($search !== '' || $transferDate)
                「{{ $search !== '' ? $search : '決済振込日' }}」に一致するデータがありません。
            @else
                決済金管理のデータがありません。
            @endif
        </div>
    @else
        @include('admin.settlement-managements._cards')
    @endif
@endsection
