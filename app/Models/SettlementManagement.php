<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettlementManagement extends Model
{
    public const FEE_TYPE_ADVERTISING = 'advertising';

    public const FEE_TYPE_BROKER = 'broker_fee';

    public const FEE_TYPE_COMBINED = 'combined';

    protected $table = 'settlement_managements';

    protected $fillable = [
        'customer_id',
        'flow_management_id',
        'fee_type',
        'management_number',
        'staff_in_charge',
        'contractor',
        'property_name',
        'room_number',
        'entry_method',
        'contract_date',
        'estimated_sales',
        'advertising_fee_amount',
        'broker_fee_amount',
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
            'advertising_fee_amount' => 'integer',
            'broker_fee_amount' => 'integer',
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

        $settlementManagement = self::query()
            ->where('flow_management_id', $flowManagement->id)
            ->orderBy('id')
            ->first();

        if ($settlementManagement === null) {
            $settlementManagement = new self([
                'flow_management_id' => $flowManagement->id,
            ]);
        }

        $advertisingAmount = in_array(self::FEE_TYPE_ADVERTISING, $applicableTypes, true)
            ? self::feeAmountFromFlowManagement($flowManagement, self::FEE_TYPE_ADVERTISING)
            : null;
        $brokerAmount = in_array(self::FEE_TYPE_BROKER, $applicableTypes, true)
            ? self::feeAmountFromFlowManagement($flowManagement, self::FEE_TYPE_BROKER)
            : null;

        $feeType = match (true) {
            count($applicableTypes) > 1 => self::FEE_TYPE_COMBINED,
            default => $applicableTypes[0],
        };

        $settlementManagement->customer_id = $flowManagement->customer_id;
        $settlementManagement->staff_in_charge = $flowManagement->staff_in_charge;
        $settlementManagement->contractor = $flowManagement->contractor;
        $settlementManagement->property_name = $flowManagement->property_name;
        $settlementManagement->room_number = $flowManagement->room_number;
        $settlementManagement->entry_method = $flowManagement->entry_method;
        $settlementManagement->fee_type = $feeType;
        $settlementManagement->advertising_fee_amount = $advertisingAmount;
        $settlementManagement->broker_fee_amount = $brokerAmount;
        $settlementManagement->estimated_sales = (int) ($advertisingAmount ?? 0) + (int) ($brokerAmount ?? 0);
        $settlementManagement->save();

        self::query()
            ->where('flow_management_id', $flowManagement->id)
            ->where('id', '!=', $settlementManagement->id)
            ->delete();
    }

    /**
     * @return list<array{key: string, label: string, classes: string}>
     */
    public function feeTypeBadges(): array
    {
        $badges = [];

        if ($this->hasAdvertisingFee()) {
            $badges[] = [
                'key' => self::FEE_TYPE_ADVERTISING,
                'label' => '広告料',
                'classes' => 'bg-amber-100 text-amber-950 border-amber-500',
            ];
        }

        if ($this->hasBrokerFee()) {
            $badges[] = [
                'key' => self::FEE_TYPE_BROKER,
                'label' => '仲介手数料',
                'classes' => 'bg-emerald-100 text-emerald-900 border-emerald-600',
            ];
        }

        return $badges;
    }

    public function hasAdvertisingFee(): bool
    {
        return $this->advertising_fee_amount !== null
            || in_array($this->fee_type, [self::FEE_TYPE_ADVERTISING, self::FEE_TYPE_COMBINED], true);
    }

    public function hasBrokerFee(): bool
    {
        return $this->broker_fee_amount !== null
            || in_array($this->fee_type, [self::FEE_TYPE_BROKER, self::FEE_TYPE_COMBINED], true);
    }

    public function feeTypeLabel(): ?string
    {
        $labels = array_column($this->feeTypeBadges(), 'label');

        return $labels === [] ? null : implode('・', $labels);
    }

    public function feeTypeBadgeClasses(): string
    {
        return match ($this->fee_type) {
            self::FEE_TYPE_ADVERTISING => 'bg-amber-100 text-amber-950 border-amber-500',
            self::FEE_TYPE_BROKER => 'bg-emerald-100 text-emerald-900 border-emerald-600',
            self::FEE_TYPE_COMBINED => 'bg-slate-100 text-slate-800 border-slate-400',
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
            'contractor' => '契約者',
            'property_name' => '物件名',
            'room_number' => '部屋番号',
            'entry_method' => '記入方法',
            'contract_date' => '契約日',
            'estimated_sales' => '想定売上（合計）',
            'advertising_fee_amount' => '広告料',
            'broker_fee_amount' => '仲介手数料',
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
