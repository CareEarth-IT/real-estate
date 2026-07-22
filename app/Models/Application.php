<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    public const ENTRY_METHOD_PROXY_SEAL = '代筆（印鑑会社）';

    public const ENTRY_METHOD_PROXY_CUSTOMER = '代筆（お客様）';

    public const ENTRY_METHOD_OFFICE = '来社記入';

    protected $fillable = [
        'customer_id',
        'staff_in_charge',
        'contractor',
        'contractor_furigana',
        'contractor_english_name',
        'overseas_screening',
        'property_name',
        'room_number',
        'scheduled_move_in_date',
        'advertising_fee',
        'has_broker_fee',
        'broker_fee',
        'management_company_name',
        'application_method',
        'entry_method',
        'status',
        'memo',
        'property_documents_url',
        'appliance_support_notes',
        'contract_doc_resident_record_url',
        'contract_doc_residence_card_url',
        'contract_doc_passport_url',
        'contract_doc_payslip_url',
        'contract_doc_face_photo_url',
        'contract_doc_identity_verification_url',
        'sales_action_required',
        'screening_ok',
        'screening_ok_at',
        'is_cancelled',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_move_in_date' => 'date',
            'advertising_fee' => 'integer',
            'broker_fee' => 'integer',
            'sales_action_required' => 'boolean',
            'screening_ok' => 'boolean',
            'screening_ok_at' => 'datetime',
            'is_cancelled' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function flowManagement(): HasOne
    {
        return $this->hasOne(FlowManagement::class);
    }

    public function propertyNameRoomLabel(): string
    {
        $propertyName = trim((string) ($this->property_name ?: $this->customer?->property_name));
        $roomNumber = trim((string) ($this->room_number ?: $this->customer?->room_number));

        if ($propertyName === '' && $roomNumber === '') {
            return '—';
        }

        if ($roomNumber === '') {
            return $propertyName;
        }

        if ($propertyName === '') {
            return $roomNumber;
        }

        return $propertyName.' '.$roomNumber;
    }

    public function displayMoveInDate(): ?string
    {
        $date = $this->scheduled_move_in_date ?? $this->customer?->move_in_date;

        return $date?->format('Y/m/d');
    }

    public function displayManagementCompanyName(): string
    {
        $name = trim((string) ($this->management_company_name ?: $this->customer?->management_company));

        return $name !== '' ? $name : '—';
    }

    public function formattedAdvertisingFee(): string
    {
        return $this->advertising_fee !== null
            ? number_format((int) $this->advertising_fee)
            : '—';
    }

    /**
     * @return array<string, string>
     */
    public static function entryMethodOptions(): array
    {
        return [
            self::ENTRY_METHOD_PROXY_SEAL => self::ENTRY_METHOD_PROXY_SEAL,
            self::ENTRY_METHOD_PROXY_CUSTOMER => self::ENTRY_METHOD_PROXY_CUSTOMER,
            self::ENTRY_METHOD_OFFICE => self::ENTRY_METHOD_OFFICE,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function contractDocumentFields(): array
    {
        return FlowManagement::contractDocumentFields();
    }

    public function displayEntryMethod(): string
    {
        $value = trim((string) $this->entry_method);

        return $value !== '' ? $value : '—';
    }

    /**
     * @return array<string, string>
     */
    public static function columnLabels(): array
    {
        return [
            'id' => 'ID',
            'customer_id' => '顧客ID',
            'created_at' => '作成日時',
            'staff_in_charge' => '担当者',
            'contractor' => '契約者',
            'contractor_furigana' => 'フリガナ',
            'contractor_english_name' => '英名',
            'overseas_screening' => '海外審査',
            'property_name' => '物件名',
            'room_number' => '部屋番号',
            'scheduled_move_in_date' => '入居予定日',
            'advertising_fee' => '広告料',
            'has_broker_fee' => '仲介手数料',
            'broker_fee' => '仲介手数料（金額）',
            'management_company_name' => '管理会社名',
            'application_method' => '申込方法',
            'entry_method' => '記入方法',
            'status' => '状況',
            'memo' => 'MEMO',
            'property_documents_url' => '物件資料',
            'appliance_support_notes' => '家電サポート・CB等',
            ...self::contractDocumentFields(),
            'sales_action_required' => '営業要対応',
            'screening_ok' => '審査ＯＫ',
            'is_cancelled' => 'キャンセル',
        ];
    }
}
