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
        'application_id',
        'flow_management_transition',
        'staff_in_charge',
        'contractor',
        'contractor_furigana',
        'contractor_english_name',
        'overseas_screening',
        'google_drive_url',
        'contract_doc_resident_record_url',
        'contract_doc_residence_card_url',
        'contract_doc_passport_url',
        'contract_doc_payslip_url',
        'contract_doc_face_photo_url',
        'contract_doc_identity_verification_url',
        'contract_doc_resident_record_confirmed',
        'contract_doc_residence_card_confirmed',
        'contract_doc_passport_confirmed',
        'contract_doc_payslip_confirmed',
        'contract_doc_face_photo_confirmed',
        'contract_doc_identity_verification_confirmed',
        'property_name',
        'room_number',
        'application_method',
        'entry_method',
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
        'has_broker_fee',
        'settlement_transition',
    ];

    protected function casts(): array
    {
        return [
            'flow_management_transition' => 'boolean',
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
            'contract_doc_resident_record_confirmed' => 'boolean',
            'contract_doc_residence_card_confirmed' => 'boolean',
            'contract_doc_passport_confirmed' => 'boolean',
            'contract_doc_payslip_confirmed' => 'boolean',
            'contract_doc_face_photo_confirmed' => 'boolean',
            'contract_doc_identity_verification_confirmed' => 'boolean',
            'has_broker_fee' => 'boolean',
            'settlement_transition' => 'boolean',
        ];
    }

    public static function syncFromApplication(Application $application): ?self
    {
        $flowManagement = static::query()
            ->where('application_id', $application->id)
            ->first();

        if (! $application->screening_ok) {
            return $flowManagement;
        }

        $flowManagement ??= new static([
            'application_id' => $application->id,
        ]);

        $flowManagement->customer_id = $application->customer_id;
        $flowManagement->staff_in_charge = $application->staff_in_charge;
        $flowManagement->contractor = $application->contractor;
        $flowManagement->contractor_furigana = $application->contractor_furigana;
        $flowManagement->contractor_english_name = $application->contractor_english_name;
        $flowManagement->overseas_screening = $application->overseas_screening;
        $flowManagement->property_name = $application->property_name;
        $flowManagement->room_number = $application->room_number;
        $flowManagement->application_method = $application->application_method;
        $flowManagement->entry_method = $application->entry_method;
        foreach (array_keys(self::contractDocumentFields()) as $field) {
            $flowManagement->{$field} = $application->{$field};
        }
        $flowManagement->flow_management_transition = true;
        if ($application->has_broker_fee === true || (int) $application->broker_fee >= 1) {
            $flowManagement->has_broker_fee = true;
        }

        $flowManagement->save();

        return $flowManagement;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function settlementManagements(): HasMany
    {
        return $this->hasMany(SettlementManagement::class);
    }

    /**
     * @return array<string, string>
     */
    public static function contractDocumentFields(): array
    {
        return [
            'contract_doc_resident_record_url' => '住民票',
            'contract_doc_residence_card_url' => '在留カード（裏表）',
            'contract_doc_passport_url' => 'パスポート',
            'contract_doc_payslip_url' => '給与明細（3ヶ月分）',
            'contract_doc_face_photo_url' => '顔写真',
            'contract_doc_identity_verification_url' => '本人確認(任意)',
        ];
    }

    public static function contractDocumentConfirmedField(string $urlField): string
    {
        return preg_replace('/_url$/', '_confirmed', $urlField) ?? $urlField.'_confirmed';
    }

    /**
     * @return list<string>
     */
    public static function contractDocumentConfirmedFields(): array
    {
        return array_map(
            fn (string $urlField) => self::contractDocumentConfirmedField($urlField),
            array_keys(self::contractDocumentFields())
        );
    }

    public function contractDocumentStatus(string $urlField): string
    {
        if (! filled($this->{$urlField})) {
            return '';
        }

        $confirmedField = self::contractDocumentConfirmedField($urlField);

        return $this->{$confirmedField} ? '完了' : '確認中';
    }

    public function hasAnyContractDocumentUrl(): bool
    {
        foreach (array_keys(self::contractDocumentFields()) as $field) {
            if (filled($this->{$field})) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, string>
     */
    public static function columnLabels(): array
    {
        return [
            'id' => 'ID',
            'customer_id' => '顧客ID',
            'application_id' => '申込ID',
            'flow_management_transition' => '書類管理移行チェック',
            'staff_in_charge' => '担当者',
            'contractor' => '契約者',
            'contractor_furigana' => 'フリガナ',
            'contractor_english_name' => '英名',
            'overseas_screening' => '海外審査',
            'google_drive_url' => '契約書類',
            ...self::contractDocumentFields(),
            'property_name' => '物件名',
            'room_number' => '部屋番号',
            'application_method' => '申込方法',
            'entry_method' => '記入方法',
            'memo' => '備考',
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
            'has_broker_fee' => '仲介手数料あり',
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
            'has_broker_fee',
            'settlement_transition',
        ];
    }
}
