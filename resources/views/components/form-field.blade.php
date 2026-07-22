@props([
    'label',
    'name',
    'type' => 'text',
    'required' => false,
    'options' => [],
    'value' => null,
])

@php
    $fieldValue = old($name, $value);
@endphp

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-slate-700 mb-1">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if ($type === 'readonly')
        <input
            type="text"
            id="{{ $name }}"
            value="{{ $fieldValue }}"
            readonly
            class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-sm text-slate-600 outline-none"
        >
    @elseif ($type === 'textarea')
        <textarea
            id="{{ $name }}"
            name="{{ $name }}"
            rows="{{ $attributes->get('rows', 3) }}"
            @if ($required) required @endif
            {{ $attributes->except('class', 'rows')->merge(['class' => 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none']) }}
        >{{ $fieldValue }}</textarea>
    @elseif ($type === 'select')
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @if ($required) required @endif
            {{ $attributes->except('class')->merge(['class' => 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none bg-white']) }}
        >
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $fieldValue === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @elseif ($type === 'date')
        <input
            type="text"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $fieldValue }}"
            data-date-picker
            autocomplete="off"
            placeholder="YYYY/MM/DD"
            @if ($required) required @endif
            {{ $attributes->except('class')->merge(['class' => 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none bg-white']) }}
        >
        <p class="mt-1 text-xs text-slate-500">手入力（例: 1990/05/15）またはカレンダーから選択できます</p>
    @else
        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            value="{{ $fieldValue }}"
            @if ($required) required @endif
            {{ $attributes->except('class')->merge(['class' => 'w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-100 outline-none']) }}
        >
    @endif

    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
