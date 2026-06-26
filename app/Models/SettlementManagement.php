<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementManagement extends Model
{
    protected $table = 'settlement_managements';

    protected $fillable = [
        'customer_id',
        'flow_management_id',
        'fee_type',
        'management_number',
        'staff_in_charge',
        'property_name',
        'contract_date',
        'estimated_sales',
        'settlement_transfer_request',
        'settlement_transfer_date',
        'sales_including_tax',
        'sales_excluding_tax',
        'earned_points',
        'ad_transfer_invoice_creation',
        'offset_statement_printing',
        'individual_invoice_printing',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'estimated_sales' => 'integer',
            'settlement_transfer_request' => 'boolean',
            'settlement_transfer_date' => 'date',
            'sales_including_tax' => 'integer',
            'sales_excluding_tax' => 'integer',
            'ad_transfer_invoice_creation' => 'boolean',
            'offset_statement_printing' => 'boolean',
            'individual_invoice_printing' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function flowManagement(): BelongsTo
    {
        return $this->belongsTo(FlowManagement::class);
    }

    public static function syncFromFlowManagement(FlowManagement $flowManagement): void
    {
        if (! $flowManagement->settlement_transition) {
            return;
        }

        $flowManagement->loadMissing('screeningCompletion.application.customer');
        $application = $flowManagement->screeningCompletion?->application;
        $customer = $application?->customer;

        $feeTypes = ['advertising'];
        if ($application?->has_broker_fee) {
            $feeTypes[] = 'broker_fee';
        }

        foreach ($feeTypes as $feeType) {
            $settlementManagement = static::query()
                ->where('flow_management_id', $flowManagement->id)
                ->where('fee_type', $feeType)
                ->first();

            $settlementManagement ??= new static([
                'flow_management_id' => $flowManagement->id,
                'fee_type' => $feeType,
            ]);

            $settlementManagement->customer_id = $flowManagement->customer_id;
            $settlementManagement->staff_in_charge = $flowManagement->staff_in_charge;
            $settlementManagement->property_name = $flowManagement->property_name_room;

            if ($customer?->contract_period) {
                $contractPeriod = $customer->contract_period;
                $settlementManagement->contract_date = is_string($contractPeriod)
                    ? (strtotime($contractPeriod) ? date('Y-m-d', strtotime($contractPeriod)) : null)
                    : $contractPeriod;
            }

            $settlementManagement->save();
        }
    }

    /**
     * @return list<string>
     */
    public static function booleanFields(): array
    {
        return [
            'settlement_transfer_request',
            'ad_transfer_invoice_creation',
            'offset_statement_printing',
            'individual_invoice_printing',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function columnLabels(): array
    {
        return [
            'id' => 'ID',
            'customer_id' => '顧客ID',
            'flow_management_id' => 'フロー管理ID',
            'fee_type' => '手数料種別',
            'management_number' => '管理番号',
            'staff_in_charge' => '担当者',
            'property_name' => '物件名',
            'contract_date' => '契約日',
            'estimated_sales' => '想定売上',
            'settlement_transfer_request' => '決済金振込依頼',
            'settlement_transfer_date' => '決済金振込日',
            'sales_including_tax' => '税込売上',
            'sales_excluding_tax' => '税抜売上',
            'earned_points' => '発生ポイント',
            'ad_transfer_invoice_creation' => '【AD振込】請求書作成',
            'offset_statement_printing' => '【相殺】明細書印刷',
            'individual_invoice_printing' => '【個人】請求書印刷',
            'remarks' => '備考',
        ];
    }
}
