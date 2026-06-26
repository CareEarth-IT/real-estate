@extends('layouts.admin')

@section('title', '物件マスターデータ — ' . config('app.name'))

@php
    use App\Support\Format;
@endphp

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">物件マスターデータ</h2>
        <p class="mt-1 text-sm text-slate-500">登録件数: <strong class="text-slate-700">{{ count($properties) }}</strong> 件</p>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        @if(!empty($properties) && count($properties) > 0)
        <div class="column-picker" id="columnPicker">
            <button type="button" class="btn btn-outline" id="columnPickerToggle" aria-expanded="false" aria-controls="columnPickerPanel">
                列の表示設定
            </button>
            <div class="column-picker-panel" id="columnPickerPanel" hidden>
                <p class="column-picker-title">表示する列を選択</p>
                <div class="column-picker-list">
                    @foreach($toggleableColumns as $col)
                    <label class="column-picker-item">
                        <input type="checkbox" class="column-toggle" value="{{ $col['key'] }}" checked>
                        {{ $col['label'] }}
                    </label>
                    @endforeach
                </div>
                <div class="column-picker-actions">
                    <button type="button" class="btn btn-ghost btn-sm" id="columnShowAll">すべて表示</button>
                    <button type="button" class="btn btn-ghost btn-sm" id="columnReset">リセット</button>
                </div>
            </div>
        </div>
        @endif
        <a href="{{ route('properties.create') }}" class="btn btn-primary">+ 新規登録</a>
    </div>
</div>

@if(empty($properties) || count($properties) === 0)
<div class="empty-state">
    <span class="empty-icon">📋</span>
    <h2>データがありません</h2>
    <p>「データ登録」から最初の物件データを追加してください。</p>
    <a href="{{ route('properties.create') }}" class="btn btn-primary">データを登録する</a>
</div>
@else

<div class="table-wrapper">
    <table class="data-table" id="propertyTable">
        <thead>
            <tr>
                @foreach($listColumns as $col)
                <th data-col="{{ $col['key'] }}" class="{{ $col['thClass'] ?? '' }}">
                    {{ $col['label'] }}
                </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($properties as $p)
            <tr>
                @foreach($listColumns as $col)
                <td data-col="{{ $col['key'] }}" class="{{ $col['tdClass'] ?? '' }}">
                    @php $id = (int) $p->id; @endphp
                    @switch($col['key'])
                        @case('id')
                            {{ $id }}
                            @break
                        @case('created_at')
                            {{ Format::formatDateTime($p->created_at) }}
                            @break
                        @case('sales_person')
                            {{ $p->sales_person ?: '—' }}
                            @break
                        @case('buyer_name')
                            {{ $p->buyer_name }}
                            @break
                        @case('broker_name')
                            {{ $p->broker_name ?: '—' }}
                            @break
                        @case('owner_name')
                            {{ $p->owner_name ?: '—' }}
                            @break
                        @case('property_address')
                            {{ $p->property_address }}
                            @break
                        @case('building_price')
                            {{ Format::formatYen((int) $p->building_price) }}
                            @break
                        @case('land_price')
                            {{ Format::formatYen((int) $p->land_price) }}
                            @break
                        @case('total_price')
                            {{ Format::formatYen((int) $p->total_price) }}
                            @break
                        @case('registration_fee')
                            {{ Format::formatYen((int) $p->registration_fee) }}
                            @break
                        @case('brokerage_fee')
                            {{ Format::formatYen((int) $p->brokerage_fee) }}
                            @break
                        @case('property_tax')
                            {{ Format::formatYen((int) $p->property_tax) }}
                            @break
                        @case('documents')
                            @foreach($documentShortLabels as $field => $label)
                                @if(!empty($p->{$field}) && $documentService->fileExists($p->{$field}))
                                <a href="{{ route('files.show', ['property' => $id, 'field' => $field]) }}" target="_blank" class="doc-badge" title="{{ $label }}">{{ $label }}</a>
                                @endif
                            @endforeach
                            @break
                        @case('actions')
                            <a href="{{ route('properties.show', $p) }}" class="btn btn-outline btn-sm">閲覧</a>
                            <a href="{{ route('properties.edit', $p) }}" class="btn btn-ghost btn-sm">編集</a>
                            @break
                    @endswitch
                </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
(function () {
    const STORAGE_KEY = 'careearth_column_visibility';
    const table = document.getElementById('propertyTable');
    const panel = document.getElementById('columnPickerPanel');
    const toggleBtn = document.getElementById('columnPickerToggle');
    const checkboxes = document.querySelectorAll('.column-toggle');
    const showAllBtn = document.getElementById('columnShowAll');
    const resetBtn = document.getElementById('columnReset');

    if (!table || !panel) return;

    const defaultHidden = [];

    function loadHidden() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return [...defaultHidden];
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [...defaultHidden];
        } catch (e) {
            return [...defaultHidden];
        }
    }

    function saveHidden(hidden) {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(hidden));
    }

    function setColumnVisible(key, visible) {
        table.querySelectorAll('[data-col="' + key + '"]').forEach(function (el) {
            el.classList.toggle('col-hidden', !visible);
        });
    }

    function applyFromCheckboxes() {
        const hidden = [];
        checkboxes.forEach(function (cb) {
            const visible = cb.checked;
            setColumnVisible(cb.value, visible);
            if (!visible) hidden.push(cb.value);
        });
        saveHidden(hidden);
    }

    function applyFromStorage() {
        const hidden = loadHidden();
        checkboxes.forEach(function (cb) {
            const visible = !hidden.includes(cb.value);
            cb.checked = visible;
            setColumnVisible(cb.value, visible);
        });
    }

    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const open = panel.hidden;
        panel.hidden = !open;
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('columnPicker').contains(e.target)) {
            panel.hidden = true;
            toggleBtn.setAttribute('aria-expanded', 'false');
        }
    });

    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', applyFromCheckboxes);
    });

    showAllBtn.addEventListener('click', function () {
        checkboxes.forEach(function (cb) { cb.checked = true; });
        applyFromCheckboxes();
    });

    resetBtn.addEventListener('click', function () {
        localStorage.removeItem(STORAGE_KEY);
        checkboxes.forEach(function (cb) { cb.checked = true; });
        applyFromCheckboxes();
    });

    applyFromStorage();
})();
</script>

@endif
@endsection
