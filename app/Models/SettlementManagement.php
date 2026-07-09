<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementManagement extends Model
{
    public const FEE_TYPE_ADVERTISING = 'advertising';

    public const FEE_TYPE_BROKER = 'broker_fee';

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

    public static function applicableFeeTypesFromFlowManagement(FlowManagement $flowManagement): array
    {
        $application = $flowManagement->application;
        $types = [];

        if ($application !== null && (int) $application->advertising_fee >= 1) {
            $types[] = self::FEE_TYPE_ADVERTISING;
        }

        if ($flowManagement->has_broker_fee) {
            $types[] = self::FEE_TYPE_BROKER;
        }

        return $types;
    }

    public static function feeAmountFromFlowManagement(FlowManagement $flowManagement, string $feeType): ?int
    {
        $application = $flowManagement->application;

        return match ($feeType) {
            self::FEE_TYPE_ADVERTISING => $application !== null && (int) $application->advertising_fee >= 1
                ? (int) $application->advertising_fee
                : null,
            self::FEE_TYPE_BROKER => $application !== null && (int) $application->broker_fee >= 1
                ? (int) $application->broker_fee
                : null,
            default => null,
        };
    }

    public static function syncFromFlowManagement(FlowManagement $flowManagement): void
    {
        $flowManagement->loadMissing('application');

        if (! $flowManagement->settlement_transition) {
            self::query()
                ->where('flow_management_id', $flowManagement->id)
                ->delete();

            return;
        }

        $applicableTypes = self::applicableFeeTypesFromFlowManagement($flowManagement);

        if ($applicableTypes === []) {
            self::query()
                ->where('flow_management_id', $flowManagement->id)
                ->delete();

            return;
        }

        $legacyRow = self::query()
            ->where('flow_management_id', $flowManagement->id)
            ->whereNull('fee_type')
            ->first();

        foreach ($applicableTypes as $index => $feeType) {
            $settlementManagement = self::query()
                ->where('flow_management_id', $flowManagement->id)
                ->where('fee_type', $feeType)
                ->first();

            if ($settlementManagement === null && $index === 0 && $legacyRow !== null) {
                $settlementManagement = $legacyRow;
            } elseif ($settlementManagement === null) {
                $settlementManagement = new self([
                    'flow_management_id' => $flowManagement->id,
                    'fee_type' => $feeType,
                ]);
            }

            $settlementManagement->customer_id = $flowManagement->customer_id;
            $settlementManagement->staff_in_charge = $flowManagement->staff_in_charge;
            $settlementManagement->property_name = $flowManagement->property_name;
            $settlementManagement->fee_type = $feeType;
            $settlementManagement->estimated_sales = self::feeAmountFromFlowManagement($flowManagement, $feeType);
            $settlementManagement->save();
        }

        self::query()
            ->where('flow_management_id', $flowManagement->id)
            ->where(function ($query) use ($applicableTypes) {
                $query->whereNull('fee_type')
                    ->orWhereNotIn('fee_type', $applicableTypes);
            })
            ->delete();
    }

    public function feeTypeLabel(): ?string
    {
        return match ($this->fee_type) {
            self::FEE_TYPE_ADVERTISING => '広告料',
            self::FEE_TYPE_BROKER => '仲介手数料',
            default => null,
        };
    }

    public function feeTypeBadgeClasses(): string
    {
        return match ($this->fee_type) {
            self::FEE_TYPE_ADVERTISING => 'bg-amber-100 text-amber-950 border-amber-500',
            self::FEE_TYPE_BROKER => 'bg-emerald-100 text-emerald-900 border-emerald-600',
            default => 'bg-slate-100 text-slate-700 border-slate-300',
        };
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
            'flow_management_id' => '書類管理ID',
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
