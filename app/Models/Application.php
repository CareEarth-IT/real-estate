<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'customer_id',
        'staff_in_charge',
        'property_name_room',
        'scheduled_move_in_date',
        'advertising_fee',
        'management_company_name',
        'application_method',
        'status',
        'memo',
        'property_documents_url',
        'appliance_support_notes',
        'sales_action_required',
        'screening_ok',
        'is_cancelled',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_move_in_date' => 'date',
            'advertising_fee' => 'integer',
            'sales_action_required' => 'boolean',
            'screening_ok' => 'boolean',
            'is_cancelled' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function screeningCompletions(): HasMany
    {
        return $this->hasMany(ScreeningCompletion::class);
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
            'property_name_room' => '物件名＋部屋番号',
            'scheduled_move_in_date' => '入居予定日',
            'advertising_fee' => '広告料',
            'management_company_name' => '管理会社名',
            'application_method' => '申込方法',
            'status' => '状況',
            'memo' => 'MEMO',
            'property_documents_url' => '物件資料',
            'appliance_support_notes' => '家電サポート・CB等',
            'sales_action_required' => '営業要対応',
            'screening_ok' => '審査ＯＫ',
            'is_cancelled' => 'キャンセル',
        ];
    }
}
