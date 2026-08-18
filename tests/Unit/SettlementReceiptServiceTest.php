<?php

namespace Tests\Unit;

use App\Models\SettlementManagement;
use App\Services\SettlementReceiptService;
use App\Support\XlsxTemplateFiller;
use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use ZipArchive;

class SettlementReceiptServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = new Container;
        $container->instance('config', new Repository([
            'settlement-receipt' => [
                'reference' => dirname(__DIR__, 2).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.'invoices'.DIRECTORY_SEPARATOR.'reference'.DIRECTORY_SEPARATOR.'領収書.xlsx',
                'cells' => [
                    'management_company_name' => 'B6',
                    'amount' => 'D7',
                    'blank' => 'D11',
                    'issue_date' => 'C12',
                    'body_amount' => 'E15',
                    'tax_amount' => 'E16',
                ],
            ],
        ]));

        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        Carbon::setTestNow(Carbon::parse('2026-08-18 12:00:00', 'Asia/Tokyo'));
        Date::setTestNow(Carbon::parse('2026-08-18 12:00:00', 'Asia/Tokyo'));
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

    public function test_it_fills_receipt_cells(): void
    {
        $settlement = new class extends SettlementManagement
        {
            protected function casts(): array
            {
                return [
                    'estimated_sales' => 'integer',
                    'sales_excluding_tax' => 'integer',
                ];
            }
        };
        $settlement->fill([
            'sales_excluding_tax' => 10000,
            'estimated_sales' => 11000,
        ]);
        $settlement->id = 9;

        $application = new class
        {
            public string $management_company_name = 'テスト管理会社';

            public mixed $customer = null;
        };
        $flow = new class($application)
        {
            public function __construct(public mixed $application) {}

            public function relationLoaded(string $relation): bool
            {
                return $relation === 'application';
            }
        };
        $settlement->setRelation('flowManagement', $flow);

        $binary = (new SettlementReceiptService)->build($settlement);
        $sheetXml = $this->sheetXml($binary);
        $reader = new XlsxTemplateFiller;

        $this->assertSame('テスト管理会社　御中', $reader->readCell($sheetXml, 'B6'));
        $this->assertSame('', $reader->readCell($sheetXml, 'D7'));
        $this->assertSame('', $reader->readCell($sheetXml, 'D11'));
        $this->assertStringContainsString('令和　　8年　　8月　　18日', $reader->readCell($sheetXml, 'C12'));
        $this->assertSame('10000', $reader->readCell($sheetXml, 'E15'));
        $this->assertSame('1000', $reader->readCell($sheetXml, 'E16'));
    }

    private function sheetXml(string $binary): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'receipt_test_');
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
