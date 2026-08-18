<?php

namespace Tests\Unit;

use App\Models\SettlementManagement;
use Carbon\Carbon;
use Tests\TestCase;

class SettlementManagementCountdownTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_shows_countdown_within_seven_days(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-18 12:00:00', 'Asia/Tokyo'));

        $settlement = $this->makeSettlement([
            'settlement_transfer_date' => '2026-08-25',
        ]);

        $this->assertTrue($settlement->shouldShowSettlementTransferCountdown());
        $this->assertSame(7, $settlement->daysUntilSettlementTransfer());
        $this->assertSame('決済振込日まで あと7日', $settlement->settlementTransferCountdownLabel());
    }

    public function test_it_shows_today_label_on_transfer_date(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-25 12:00:00', 'Asia/Tokyo'));

        $settlement = $this->makeSettlement([
            'settlement_transfer_date' => '2026-08-25',
        ]);

        $this->assertTrue($settlement->shouldShowSettlementTransferCountdown());
        $this->assertSame(0, $settlement->daysUntilSettlementTransfer());
        $this->assertSame('決済振込日は本日です', $settlement->settlementTransferCountdownLabel());
    }

    public function test_it_hides_countdown_when_workflow_is_complete(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-18 12:00:00', 'Asia/Tokyo'));

        $settlement = $this->makeSettlement([
            'settlement_transfer_date' => '2026-08-25',
            'settlement_transfer_request' => true,
            'ad_transfer_invoice_creation' => true,
            'offset_statement_printing' => true,
            'individual_invoice_printing' => true,
        ]);

        $this->assertTrue($settlement->isWorkflowComplete());
        $this->assertFalse($settlement->shouldShowSettlementTransferCountdown());
        $this->assertNull($settlement->settlementTransferCountdownLabel());
    }

    public function test_it_hides_countdown_outside_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-18 12:00:00', 'Asia/Tokyo'));

        $settlement = $this->makeSettlement([
            'settlement_transfer_date' => '2026-08-26',
        ]);

        $this->assertFalse($settlement->shouldShowSettlementTransferCountdown());
        $this->assertNull($settlement->settlementTransferCountdownLabel());
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
                    'contract_date' => 'date',
                    'settlement_transfer_date' => 'date',
                    'settlement_transfer_request' => 'boolean',
                    'ad_transfer_invoice_creation' => 'boolean',
                    'offset_statement_printing' => 'boolean',
                    'individual_invoice_printing' => 'boolean',
                ];
            }
        };

        $settlement->forceFill(array_merge([
            'settlement_transfer_request' => false,
            'ad_transfer_invoice_creation' => false,
            'offset_statement_printing' => false,
            'individual_invoice_printing' => false,
        ], $attributes));
        $settlement->syncOriginal();

        return $settlement;
    }
}
