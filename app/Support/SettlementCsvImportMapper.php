<?php

namespace App\Support;

use Carbon\Carbon;
use RuntimeException;

final class SettlementCsvImportMapper
{
    /** @var array<string, string> */
    public const TARGET_FIELDS = [
        'management_number' => '管理番号',
        'invoice_number' => '請求書No.（管理番号）',
        'business_type' => '事業種別',
        'staff_in_charge' => '担当者',
        'property_name' => '物件名',
        'contractor' => '契約者',
        'contract_date' => '契約日',
        'sales_recorded_month' => '売上計上月',
        'sales_excluding_tax' => '税抜売上',
        'sales_including_tax' => '税込売上',
        'earned_points' => '発生ポイント',
        'remarks' => '備考',
    ];
    private const HEADER_ALIASES = [
        '管理番号' => 'management_number',
        '請求書no' => 'invoice_number',
        '請求書no.' => 'invoice_number',
        '請求書番号' => 'invoice_number',
        '事業種別' => 'business_type',
        '記入者' => 'staff_in_charge',
        '担当者' => 'staff_in_charge',
        '物件名' => 'property_name',
        '支払者' => 'contractor',
        '契約者' => 'contractor',
        '成約日' => 'contract_date',
        '契約日' => 'contract_date',
        '売上計上月' => 'sales_recorded_month',
        '税抜売上' => 'sales_excluding_tax',
        '税込売上' => 'sales_including_tax',
        '発生ポイント' => 'earned_points',
        '備考' => 'remarks',
    ];

    /**
     * @return list<array{
     *     line: int,
     *     management_number: ?string,
     *     business_type: ?string,
     *     staff_in_charge: ?string,
     *     property_name: ?string,
     *     contractor: ?string,
     *     contract_date: ?string,
     *     sales_recorded_month: ?int,
     *     sales_excluding_tax: ?int,
     *     sales_including_tax: ?int,
     *     earned_points: ?string,
     *     remarks: ?string
     * }>
     */
    public function mapFile(string $path): array
    {
        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('CSVファイルを読み込めませんでした。');
        }

        return $this->mapString($contents);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function mapString(string $contents): array
    {
        $contents = $this->toUtf8($contents);
        $contents = str_replace(["\r\n", "\r"], "\n", $contents);
        $table = $this->parseTable($contents);
        $headers = $table['rows'][0] ?? [];
        $columnMap = $this->resolveColumns($headers);

        if ($columnMap === []) {
            throw new RuntimeException('取込対象の列（管理番号・請求書No.・物件名など）が見つかりません。');
        }

        return $this->mapParsedRows($table['rows'], $columnMap, false);
    }

    /**
     * @return array{rows: list<list<string>>, column_count: int}
     */
    public function parseFile(string $path): array
    {
        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException('CSVファイルを読み込めませんでした。');
        }

        return $this->parseTable($contents);
    }

    /**
     * @return array{rows: list<list<string>>, column_count: int}
     */
    public function parseTable(string $contents): array
    {
        $contents = $this->toUtf8($contents);
        $contents = str_replace(["\r\n", "\r"], "\n", $contents);
        $lines = preg_split("/\n/", $contents) ?: [];

        $rows = [];
        $columnCount = 0;
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $values = $this->parseCsvLine($line);
            $columnCount = max($columnCount, count($values));
            $rows[] = $values;
        }

        foreach ($rows as $index => $row) {
            if (count($row) < $columnCount) {
                $rows[$index] = array_pad($row, $columnCount, '');
            }
        }

        if ($rows === []) {
            throw new RuntimeException('CSVにデータがありません。');
        }

        return [
            'rows' => $rows,
            'column_count' => $columnCount,
        ];
    }

    /**
     * @param  list<list<string>>  $rows
     * @param  array<int, string>  $columnMap
     * @return list<array<string, mixed>>
     */
    public function mapParsedRows(array $rows, array $columnMap, bool $importFromFirstRow): array
    {
        if ($columnMap === []) {
            throw new RuntimeException('取込対象の列が選択されていません。');
        }

        $mapped = [];
        $start = $importFromFirstRow ? 0 : 1;
        if (! $importFromFirstRow && count($rows) < 2) {
            throw new RuntimeException('ヘッダー行の次に取り込むデータがありません。');
        }

        for ($i = $start; $i < count($rows); $i++) {
            $row = $this->mapRow($columnMap, $rows[$i], $i + 1);
            if ($row !== null) {
                $mapped[] = $row;
            }
        }

        return $mapped;
    }

    public static function columnLetter(int $index): string
    {
        $letter = '';
        $n = $index + 1;
        while ($n > 0) {
            $n--;
            $letter = chr(65 + ($n % 26)).$letter;
            $n = intdiv($n, 26);
        }

        return $letter;
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    public function resolveColumns(array $headers): array
    {
        $map = [];

        foreach ($headers as $index => $header) {
            $normalized = $this->normalizeHeader((string) $header);
            if ($normalized === '賃借人名') {
                continue;
            }

            $field = self::HEADER_ALIASES[$normalized] ?? null;
            if ($field !== null) {
                $map[$index] = $field;
            }
        }

        return $map;
    }

    /**
     * @param  array<int, string>  $columnMap
     * @param  list<string>  $values
     * @return array<string, mixed>|null
     */
    public function mapRow(array $columnMap, array $values, int $line): ?array
    {
        $raw = [];
        foreach ($columnMap as $index => $field) {
            $raw[$field] = trim((string) ($values[$index] ?? ''));
        }

        $managementNumber = $this->nullableString($raw['management_number'] ?? '');
        if ($managementNumber === null) {
            $managementNumber = $this->nullableString($raw['invoice_number'] ?? '');
        }

        $row = [
            'line' => $line,
            'management_number' => $managementNumber,
            'business_type' => $this->nullableString($raw['business_type'] ?? ''),
            'staff_in_charge' => $this->nullableString($raw['staff_in_charge'] ?? ''),
            'property_name' => $this->nullableString($raw['property_name'] ?? ''),
            'contractor' => $this->nullableString($raw['contractor'] ?? ''),
            'contract_date' => $this->parseDate($raw['contract_date'] ?? ''),
            'sales_recorded_month' => $this->parseYearMonth($raw['sales_recorded_month'] ?? ''),
            'sales_excluding_tax' => $this->parseInteger($raw['sales_excluding_tax'] ?? ''),
            'sales_including_tax' => $this->parseInteger($raw['sales_including_tax'] ?? ''),
            'earned_points' => $this->nullableString($raw['earned_points'] ?? ''),
            'remarks' => $this->nullableString($raw['remarks'] ?? ''),
        ];

        foreach ($row as $key => $value) {
            if ($key === 'line') {
                continue;
            }
            if ($value !== null && $value !== '') {
                return $row;
            }
        }

        return null;
    }

    public function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = str_replace(["\u{3000}", ' ', '　'], '', $header);
        $header = mb_convert_kana($header, 'asKV', 'UTF-8');
        $header = mb_strtolower($header, 'UTF-8');
        $header = str_replace(['．', '。'], '.', $header);

        return $header;
    }

    private function toUtf8(string $contents): string
    {
        if (str_starts_with($contents, "\xEF\xBB\xBF")) {
            return substr($contents, 3);
        }

        if (mb_check_encoding($contents, 'UTF-8')) {
            return $contents;
        }

        $converted = @mb_convert_encoding($contents, 'UTF-8', 'SJIS-win');
        if (is_string($converted) && $converted !== '') {
            return $converted;
        }

        return $contents;
    }

    /**
     * @return list<string>
     */
    private function parseCsvLine(string $line): array
    {
        $parsed = str_getcsv($line, ',', '"', '\\');

        return array_map(static fn ($value): string => trim((string) $value), $parsed);
    }

    private function nullableString(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function parseInteger(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $digits = preg_replace('/[^\d\-]/u', '', $value) ?? '';
        if ($digits === '' || $digits === '-') {
            return null;
        }

        return (int) $digits;
    }

    private function parseDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4,6}$/', $value) === 1) {
            $serial = (int) $value;
            if ($serial >= 20000 && $serial <= 80000) {
                return Carbon::create(1899, 12, 30)->addDays($serial)->toDateString();
            }
        }

        try {
            return Carbon::parse(str_replace(['年', '月', '日'], ['-', '-', ''], $value))->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseYearMonth(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return YearMonth::fromInput($value)
            ?? YearMonth::fromShortSearch($value)
            ?? YearMonth::fromDate($this->parseDate($value) ?? '');
    }
}
