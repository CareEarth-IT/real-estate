<?php

namespace App\Http\Controllers;

use App\Services\SettlementCsvImportService;
use App\Support\SettlementCsvImportMapper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class SettlementCsvImportController extends Controller
{
    public function __construct(
        private readonly SettlementCsvImportMapper $mapper,
        private readonly SettlementCsvImportService $importer,
    ) {}

    public function preview(Request $request): RedirectResponse
    {
        $validated = $request->validate(
            [
                'csv' => ['required', 'file', 'max:10240'],
            ],
            [
                'csv.required' => 'CSVファイルを選択してください。',
                'csv.file' => 'CSVファイルを選択してください。',
                'csv.max' => 'CSVファイルは10MB以内にしてください。',
            ]
        );

        $file = $validated['csv'];
        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! in_array($extension, ['csv', 'txt'], true)) {
            return redirect()
                ->route('home')
                ->with('error', 'CSVファイル（.csv）を選んでください。');
        }

        $this->forgetStoredCsv($request);

        $token = (string) Str::uuid();
        $storagePath = 'csv-imports/'.$token.'.csv';
        Storage::disk('local')->put($storagePath, $file->get());

        try {
            $this->mapper->parseFile(Storage::disk('local')->path($storagePath));
        } catch (RuntimeException $exception) {
            Storage::disk('local')->delete($storagePath);

            return redirect()
                ->route('home')
                ->with('error', $exception->getMessage());
        }

        $request->session()->put('csv_import', [
            'token' => $token,
            'path' => $storagePath,
            'original_name' => $file->getClientOriginalName(),
        ]);

        return redirect()->route('home.settlement-csv-import.show');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $stored = $request->session()->get('csv_import');
        if (! is_array($stored) || empty($stored['path']) || ! Storage::disk('local')->exists($stored['path'])) {
            return redirect()
                ->route('home')
                ->with('error', 'CSVファイルの確認期限が切れました。もう一度読み込んでください。');
        }

        try {
            $table = $this->mapper->parseFile(Storage::disk('local')->path($stored['path']));
        } catch (RuntimeException $exception) {
            $this->forgetStoredCsv($request);

            return redirect()
                ->route('home')
                ->with('error', $exception->getMessage());
        }

        $guessedMap = $this->mapper->resolveColumns($table['rows'][0] ?? []);
        $savedMap = $request->session()->get('csv_import_last_mapping');

        return $this->previewView(
            $table,
            $guessedMap,
            is_array($savedMap) ? $savedMap : [],
            (string) ($stored['original_name'] ?? 'CSV'),
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $stored = $request->session()->get('csv_import');
        if (! is_array($stored) || empty($stored['path']) || ! Storage::disk('local')->exists($stored['path'])) {
            return redirect()
                ->route('home')
                ->with('error', 'CSVファイルの確認期限が切れました。もう一度読み込んでください。');
        }

        $validated = $request->validate([
            'duplicate_mode' => ['required', 'in:skip,update'],
            'import_from_first_row' => ['nullable', 'boolean'],
            'columns' => ['required', 'array'],
            'columns.*' => ['nullable', 'string'],
        ]);

        $columnMap = [];
        foreach ($validated['columns'] as $index => $field) {
            $field = trim((string) $field);
            if ($field !== '' && array_key_exists($field, SettlementCsvImportMapper::TARGET_FIELDS)) {
                $columnMap[(int) $index] = $field;
            }
        }

        if ($columnMap === []) {
            return redirect()
                ->route('home.settlement-csv-import.show')
                ->withInput()
                ->with('error', '取り込む項目を1つ以上選んでください。');
        }

        $importFromFirstRow = $request->boolean('import_from_first_row');

        try {
            $result = $this->importer->importPath(
                Storage::disk('local')->path($stored['path']),
                $columnMap,
                $importFromFirstRow,
                $validated['duplicate_mode'],
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('home.settlement-csv-import.show')
                ->withInput()
                ->with('error', $exception->getMessage());
        }

        $request->session()->put('csv_import_last_mapping', $columnMap);
        $this->forgetStoredCsv($request);

        $message = sprintf(
            'CSVを取り込みました。新規 %d 件 / 更新 %d 件',
            $result['created'],
            $result['updated']
        );

        if ($result['skipped'] > 0) {
            $message .= sprintf(' / スキップ %d 件', $result['skipped']);
            if ($result['errors'] !== []) {
                $message .= '（'.implode(' / ', $result['errors']).'）';
            }
        }

        return redirect()
            ->route('home')
            ->with('success', $message);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $this->forgetStoredCsv($request);

        return redirect()->route('home');
    }

    /**
     * @param  array{rows: list<list<string>>, column_count: int}  $table
     * @param  array<int, string>  $guessedMap
     * @param  array<int, string>  $savedMap
     */
    private function previewView(array $table, array $guessedMap, array $savedMap, string $originalName): View
    {
        $previewRows = array_slice($table['rows'], 0, 8);

        return view('dashboard.csv-import', [
            'originalName' => $originalName,
            'columnCount' => $table['column_count'],
            'previewRows' => $previewRows,
            'guessedMap' => $guessedMap,
            'savedMap' => $savedMap,
            'targetFields' => SettlementCsvImportMapper::TARGET_FIELDS,
            'pageTitle' => 'CSV取込確認',
        ]);
    }

    private function forgetStoredCsv(Request $request): void
    {
        $stored = $request->session()->get('csv_import');
        if (is_array($stored) && ! empty($stored['path'])) {
            Storage::disk('local')->delete($stored['path']);
        }

        $request->session()->forget('csv_import');
    }
}
