<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FlowManagement extends Model
{
    protected $table = 'flow_managements';

    protected $fillable = [
        'customer_id',
        'screening_completion_id',
        'staff_in_charge',
        'property_name_room',
        'application_method',
        'memo',
        'move_in_date',
        'document_deadline',
        'scheduled_visit_date',
        'key_handover_date',
        'documents_completed',
        'documents_returned',
        'resident_record_photo_request',
        'resident_record_photo_cancel',
        'certified_copy_acquisition',
        'important_matters_explanation_creation',
        'documents_arrived',
        'ad_fee_invoice_creation',
        'transfer_request_to_applicant',
        'transfer_receipt_from_applicant',
        'payment_request_creation',
        'accounting_transfer_request',
        'slack_sales_notification',
        'lifeline',
        'key_received',
        'key_to_applicant',
        'original_copy_to_applicant',
        'key_receipt_return',
        'contract_copy_storage',
        'settlement_transition',
    ];

    protected function casts(): array
    {
        return [
            'move_in_date' => 'date',
            'scheduled_visit_date' => 'date',
            'key_handover_date' => 'date',
            'documents_completed' => 'boolean',
            'documents_returned' => 'boolean',
            'resident_record_photo_request' => 'boolean',
            'resident_record_photo_cancel' => 'boolean',
            'certified_copy_acquisition' => 'boolean',
            'important_matters_explanation_creation' => 'boolean',
            'documents_arrived' => 'boolean',
            'transfer_request_to_applicant' => 'boolean',
            'transfer_receipt_from_applicant' => 'boolean',
            'payment_request_creation' => 'boolean',
            'accounting_transfer_request' => 'boolean',
            'slack_sales_notification' => 'boolean',
            'lifeline' => 'boolean',
            'key_received' => 'boolean',
            'key_to_applicant' => 'boolean',
            'original_copy_to_applicant' => 'boolean',
            'key_receipt_return' => 'boolean',
            'contract_copy_storage' => 'boolean',
            'settlement_transition' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function screeningCompletion(): BelongsTo
    {
        return $this->belongsTo(ScreeningCompletion::class);
    }

    public function settlementManagements(): HasMany
    {
        return $this->hasMany(SettlementManagement::class);
    }

    /**
     * @return array<string, string>
     */
    public static function columnLabels(): array
    {
        return [
            'id' => 'ID',
            'customer_id' => '顧客ID',
            'screening_completion_id' => '審査完了ID',
            'staff_in_charge' => '担当者',
            'property_name_room' => '物件名＋部屋番号',
            'application_method' => '申込方法',
            'memo' => 'MEMO',
            'move_in_date' => '入居日',
            'document_deadline' => '書類期日',
            'scheduled_visit_date' => '来社予定日',
            'key_handover_date' => '鍵渡日',
            'documents_completed' => '書類完成',
            'documents_returned' => '書類返送',
            'resident_record_photo_request' => '住民票 顔写真依頼',
            'resident_record_photo_cancel' => '住民票 顔写真取消',
            'certified_copy_acquisition' => '謄本取得',
            'important_matters_explanation_creation' => '重説作成',
            'documents_arrived' => '書類到着',
            'ad_fee_invoice_creation' => '広告料請求書作成',
            'transfer_request_to_applicant' => '本人へ振込依頼',
            'transfer_receipt_from_applicant' => '本人より振込・受取',
            'payment_request_creation' => '支払依頼書作成',
            'accounting_transfer_request' => '経理部へ振込依頼',
            'slack_sales_notification' => 'スラックで売上連絡',
            'lifeline' => 'ライフライン',
            'key_received' => '鍵受取',
            'key_to_applicant' => '鍵本人へ',
            'original_copy_to_applicant' => '原本コピー本人配布',
            'key_receipt_return' => '鍵受領書など返送',
            'contract_copy_storage' => '契約書コピー/保管',
            'settlement_transition' => '決済金管理に移行',
        ];
    }

    /**
     * @return list<string>
     */
    public static function booleanFields(): array
    {
        return [
            'documents_completed',
            'documents_returned',
            'resident_record_photo_request',
            'resident_record_photo_cancel',
            'certified_copy_acquisition',
            'important_matters_explanation_creation',
            'documents_arrived',
            'transfer_request_to_applicant',
            'transfer_receipt_from_applicant',
            'payment_request_creation',
            'accounting_transfer_request',
            'slack_sales_notification',
            'lifeline',
            'key_received',
            'key_to_applicant',
            'original_copy_to_applicant',
            'key_receipt_return',
            'contract_copy_storage',
            'settlement_transition',
        ];
    }
}
