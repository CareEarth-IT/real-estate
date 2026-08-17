<?php

namespace Tests\Unit;

use App\Models\SettlementManagement;
use App\Services\SettlementInvoiceCsvService;
use App\Support\XlsxTemplateFiller;
use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class SettlementInvoiceCsvServiceTest extends TestCase
{
    private string $templatePath;

    protected function setUp(): void
    {
        parent::setUp();

        $files = glob(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'reference'.DIRECTORY_SEPARATOR.'*.xlsx') ?: [];
        $this->assertNotSame([], $files, '請求書テンプレートが見つかりません。');
        $this->templatePath = $files[0];

        $container = new Container;
        $container->instance('config', new Repository([
            'settlement-invoice' => [
                'reference' => $this->templatePath,
                'cells' => [
                    'management_number' => 'AF1',
                    'issue_date' => 'AE2',
                    'contractor' => 'F7',
                    'estimated_sales' => 'I12',
                    'property_name' => 'K17',
                    'property_address' => 'K19',
                    'settlement_transfer_date' => 'B47',
                ],
            ],
        ]));

        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        Carbon::setTestNow(Carbon::parse('2026-08-13 09:00:00', 'Asia/Tokyo'));
        Date::setTestNow(Carbon::parse('2026-08-13 09:00:00', 'Asia/Tokyo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Date::setTestNow();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);

        parent::tearDown();
    }

    public function test_it_fills_specified_cells_on_the_reference_template(): void
    {
        $settlement = $this->makeSettlement([
            'management_number' => 'INV-1001',
            'contractor' => '山田太郎',
            'property_name' => 'グリーンハイツ',
            'room_number' => '205',
            'estimated_sales' => 33000,
            'settlement_transfer_date' => '2026-08-10',
        ]);

        $binary = (new SettlementInvoiceCsvService)->build($settlement);
        $sheetXml = $this->sheetXml($binary);
        $reader = new XlsxTemplateFiller;

        $this->assertSame('INV-1001', $reader->readCell($sheetXml, 'AF1'));
        $this->assertSame('2026/08/13', $reader->readCell($sheetXml, 'AE2'));
        $this->assertSame('山田太郎', $reader->readCell($sheetXml, 'F7'));
        $this->assertSame('33000', $reader->readCell($sheetXml, 'I12'));
        $this->assertSame('グリーンハイツ 205', $reader->readCell($sheetXml, 'K17'));
        $this->assertSame('', $reader->readCell($sheetXml, 'K19'));
        $this->assertSame('2026/08/10', $reader->readCell($sheetXml, 'B47'));
    }

    public function test_it_leaves_optional_cells_blank(): void
    {
        $settlement = $this->makeSettlement([
            'management_number' => 'INV-1002',
            'contractor' => '佐藤花子',
            'property_name' => 'ブルーマンション',
        ]);

        $binary = (new SettlementInvoiceCsvService)->build($settlement);
        $sheetXml = $this->sheetXml($binary);
        $reader = new XlsxTemplateFiller;

        $this->assertSame('INV-1002', $reader->readCell($sheetXml, 'AF1'));
        $this->assertSame('佐藤花子', $reader->readCell($sheetXml, 'F7'));
        $this->assertSame('', $reader->readCell($sheetXml, 'I12'));
        $this->assertSame('', $reader->readCell($sheetXml, 'K19'));
        $this->assertSame('', $reader->readCell($sheetXml, 'B47'));
        $this->assertSame('ブルーマンション', $reader->readCell($sheetXml, 'K17'));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeSettlement(array $attributes): SettlementManagement
    {
        $settlement = new class extends SettlementManagement
        {
            protected function casts(): array
            {
                return [
                    'estimated_sales' => 'integer',
                ];
            }
        };
        $settlement->fill($attributes);
        $settlement->id = 15;

        return $settlement;
    }

    private function sheetXml(string $binary): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'invoice_test_');
        $this->assertNotFalse($tempPath);
        file_put_contents($tempPath, $binary);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($tempPath) === true);
        $xml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        @unlink($tempPath);

        $this->assertIsString($xml);

        return $xml;
    }
}
