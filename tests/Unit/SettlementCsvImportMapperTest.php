<?php

namespace Tests\Unit;

use App\Support\SettlementCsvImportMapper;
use PHPUnit\Framework\TestCase;

class SettlementCsvImportMapperTest extends TestCase
{
    public function test_it_maps_japanese_headers_to_settlement_fields(): void
    {
        $csv = implode("\n", [
            '管理番号,請求書No.,事業種別,記入者,物件名,賃借人名,支払者,成約日,売上計上月,税抜売上,税込売上,発生ポイント,備考',
            '10001,INV-1,不動産,山田,グリーンハイツ,スキップする名前,佐藤花子,2026/4/1,2026/04,"33,000","36,300",10pt,テスト備考',
        ]);

        $rows = (new SettlementCsvImportMapper)->mapString($csv);

        $this->assertCount(1, $rows);
        $this->assertSame('10001', $rows[0]['management_number']);
        $this->assertSame('不動産', $rows[0]['business_type']);
        $this->assertSame('山田', $rows[0]['staff_in_charge']);
        $this->assertSame('グリーンハイツ', $rows[0]['property_name']);
        $this->assertSame('佐藤花子', $rows[0]['contractor']);
        $this->assertSame('2026-04-01', $rows[0]['contract_date']);
        $this->assertSame(202604, $rows[0]['sales_recorded_month']);
        $this->assertSame(33000, $rows[0]['sales_excluding_tax']);
        $this->assertSame(36300, $rows[0]['sales_including_tax']);
        $this->assertSame('10pt', $rows[0]['earned_points']);
        $this->assertSame('テスト備考', $rows[0]['remarks']);
        $this->assertArrayNotHasKey('tenant_name', $rows[0]);
    }

    public function test_it_falls_back_to_invoice_number_for_management_number(): void
    {
        $csv = implode("\n", [
            '請求書No.,物件名,支払者',
            'INV-1002,ブルーマンション,田中',
        ]);

        $rows = (new SettlementCsvImportMapper)->mapString($csv);

        $this->assertSame('INV-1002', $rows[0]['management_number']);
        $this->assertSame('ブルーマンション', $rows[0]['property_name']);
        $this->assertSame('田中', $rows[0]['contractor']);
    }

    public function test_it_skips_empty_rows(): void
    {
        $csv = implode("\n", [
            '物件名,備考',
            ',',
            'パークハイツ,メモ',
        ]);

        $rows = (new SettlementCsvImportMapper)->mapString($csv);

        $this->assertCount(1, $rows);
        $this->assertSame('パークハイツ', $rows[0]['property_name']);
    }

    public function test_it_converts_column_indexes_to_letters(): void
    {
        $this->assertSame('A', SettlementCsvImportMapper::columnLetter(0));
        $this->assertSame('B', SettlementCsvImportMapper::columnLetter(1));
        $this->assertSame('Z', SettlementCsvImportMapper::columnLetter(25));
        $this->assertSame('AA', SettlementCsvImportMapper::columnLetter(26));
    }
}
