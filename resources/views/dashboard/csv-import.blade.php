@extends('layouts.admin')

@section('title', 'CSV取込確認 — ' . config('app.name'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('home') }}" class="inline-flex text-sm text-primary-600 hover:underline mb-4">← ホームへ戻る</a>
        <h2 class="text-2xl font-bold text-slate-900">CSV取込確認</h2>
        <p class="mt-1 text-sm text-slate-500">
            ファイル: <strong class="text-slate-700">{{ $originalName }}</strong>
            ／ 書類管理・決済金管理へ取り込みます。
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="post" action="{{ route('home.settlement-csv-import.store') }}" id="csv-import-confirm-form">
        @csrf

        <section class="mb-6 overflow-hidden rounded-xl border border-slate-300 bg-white">
            <div class="border-b border-slate-300 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-800">
                取込設定
            </div>
            <div class="grid grid-cols-1 divide-y divide-slate-200 md:grid-cols-3 md:divide-x md:divide-y-0">
                <div class="p-4">
                    <p class="text-sm font-semibold text-slate-800">管理番号重複時の設定</p>
                    <p class="mt-1 text-xs text-slate-500">管理番号（または請求書No.）がすでに存在する場合</p>
                    @php $duplicateMode = old('duplicate_mode', 'skip'); @endphp
                    <div class="mt-3 space-y-2 text-sm text-slate-800">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="duplicate_mode" value="skip" class="text-primary-600 focus:ring-primary-500" @checked($duplicateMode === 'skip')>
                            取込をスキップする
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="duplicate_mode" value="update" class="text-primary-600 focus:ring-primary-500" @checked($duplicateMode === 'update')>
                            書類・決済金管理を更新する
                        </label>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-sm font-semibold text-slate-800">取込開始行</p>
                    <label class="mt-3 flex items-center gap-2 text-sm text-slate-800">
                        <input type="checkbox" name="import_from_first_row" value="1" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" @checked(old('import_from_first_row'))>
                        1行目から取り込む
                    </label>
                    <p class="mt-2 text-xs text-slate-500">チェックしない場合、1行目は見出しとして扱い2行目から取り込みます。</p>
                </div>
                <div class="p-4">
                    <p class="text-sm font-semibold text-slate-800">既存の取込設定の利用</p>
                    <label class="mt-3 flex items-center gap-2 text-sm text-slate-800">
                        <input type="checkbox" id="use-saved-mapping" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500" @disabled($savedMap === [])>
                        既存の取込設定を利用する
                    </label>
                    <select
                        id="saved-mapping-select"
                        class="mt-2 w-full rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 disabled:bg-slate-100"
                        @disabled($savedMap === [])
                    >
                        <option value="">選択してください</option>
                        @if ($savedMap !== [])
                            <option value="last">前回の設定</option>
                        @endif
                    </select>
                </div>
            </div>
        </section>

        <p class="mb-1 text-sm text-slate-700">※新規登録時は「管理番号」または「請求書No.（管理番号）」の入力が必要です。</p>
        <p class="mb-1 text-sm text-slate-700">※更新時は「管理番号」または「請求書No.（管理番号）」の入力が必須です。</p>
        <p class="mb-4 text-sm text-slate-700">※「賃借人名」は取り込みません（未定）。「支払者」は契約者、「成約日」は契約日、「記入者」は担当者として取り込みます。</p>

        <div class="overflow-x-auto rounded-xl border border-slate-300 bg-white">
            <table class="min-w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-100 text-center text-slate-700">
                        @for ($i = 0; $i < $columnCount; $i++)
                            <th class="border-b border-r border-slate-300 px-2 py-2 font-semibold whitespace-nowrap">
                                {{ \App\Support\SettlementCsvImportMapper::columnLetter($i) }}
                            </th>
                        @endfor
                    </tr>
                    <tr class="bg-white">
                        @for ($i = 0; $i < $columnCount; $i++)
                            @php $selectedField = old('columns.'.$i, $guessedMap[$i] ?? ''); @endphp
                            <th class="border-b border-r border-slate-300 px-2 py-2 min-w-[11rem]">
                                <select
                                    name="columns[{{ $i }}]"
                                    class="csv-map-select w-full rounded-md border border-slate-300 bg-white px-2 py-1.5 text-xs font-medium text-slate-800"
                                    data-guess="{{ $guessedMap[$i] ?? '' }}"
                                >
                                    <option value="">未選択</option>
                                    @foreach ($targetFields as $field => $label)
                                        <option value="{{ $field }}" @selected($selectedField === $field)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </th>
                        @endfor
                    </tr>
                </thead>
                <tbody>
                    @foreach ($previewRows as $row)
                        <tr class="hover:bg-slate-50">
                            @for ($i = 0; $i < $columnCount; $i++)
                                <td class="border-b border-r border-slate-200 px-2 py-2 text-slate-800 whitespace-nowrap max-w-[14rem] truncate" title="{{ $row[$i] ?? '' }}">
                                    {{ $row[$i] ?? '' }}
                                </td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('home.settlement-csv-import.cancel') }}" class="btn btn-outline">キャンセル</a>
            <button type="submit" class="btn btn-primary">取り込む</button>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (() => {
        const savedMap = @json($savedMap);
        const checkbox = document.getElementById('use-saved-mapping');
        const select = document.getElementById('saved-mapping-select');
        const fields = Array.from(document.querySelectorAll('.csv-map-select'));

        function applyMap(map) {
            fields.forEach((field, index) => {
                const guessed = field.dataset.guess || '';
                const value = map ? (map[String(index)] || map[index] || '') : guessed;
                field.value = value;
            });
        }

        function toggleSaved() {
            const useSaved = Boolean(checkbox?.checked) && select?.value === 'last';
            applyMap(useSaved ? savedMap : null);
        }

        checkbox?.addEventListener('change', () => {
            if (checkbox.checked && select && !select.value && select.options.length > 1) {
                select.value = 'last';
            }
            if (!checkbox.checked && select) {
                select.value = '';
            }
            toggleSaved();
        });
        select?.addEventListener('change', toggleSaved);
    })();
</script>
@endpush
