<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'move_in_date',
        'contract_period',
        'contract_period_type',
        'property_name',
        'room_number',
        'address',
        'management_company',
        'date_of_birth',
        'is_married',
        'mobile_number',
        'email',
        'occupation',
        'company_or_school_name',
        'company_or_school_phone',
        'company_or_school_address',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_date_of_birth',
        'emergency_contact_address',
        'emergency_contact_mobile',
        'emergency_contact_email',
    ];

    protected static function booted(): void
    {
        static::creating(function (Customer $customer): void {
            if ($customer->case_number === null) {
                $customer->case_number = (static::max('case_number') ?? 0) + 1;
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'move_in_date' => 'date',
            'contract_period' => 'date',
            'contract_period_type' => 'boolean',
            'date_of_birth' => 'date',
            'is_married' => 'boolean',
            'emergency_contact_date_of_birth' => 'date',
        ];
    }

    /**
     * カラム名と日本語ラベルの対応（画面表示用）
     *
     * @return array<string, string>
     */
    public static function columnLabels(): array
    {
        return [
            'id' => 'ID',
            'case_number' => '案件番号',
            'name' => '氏名',
            'move_in_date' => '入居日/保険加入日',
            'contract_period' => '契約期間',
            'contract_period_type' => '種類（契約期間）',
            'property_name' => '物件名',
            'room_number' => '部屋番号',
            'address' => '住所',
            'management_company' => '管理会社',
            'date_of_birth' => '生年月日',
            'is_married' => '既婚/未婚',
            'mobile_number' => '携帯番号',
            'email' => 'メールアドレス',
            'occupation' => '職業',
            'company_or_school_name' => '会社名/学校名',
            'company_or_school_phone' => '電話番号（会社名/学校名）',
            'company_or_school_address' => '住所（会社/学校）',
            'emergency_contact_name' => '緊急連絡先の氏名',
            'emergency_contact_relationship' => '続柄',
            'emergency_contact_date_of_birth' => '生年月日（緊急連絡先）',
            'emergency_contact_address' => '現住所（緊急連絡先）',
            'emergency_contact_mobile' => '携帯番号（緊急連絡先）',
            'emergency_contact_email' => 'メールアドレス（緊急連絡先）',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function screeningCompletions(): HasMany
    {
        return $this->hasMany(ScreeningCompletion::class);
    }

    public function flowManagements(): HasMany
    {
        return $this->hasMany(FlowManagement::class);
    }

    public function settlementManagements(): HasMany
    {
        return $this->hasMany(SettlementManagement::class);
    }
}
