<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScreeningCompletion extends Model
{
    protected $fillable = [
        'customer_id',
        'application_id',
        'staff_in_charge',
        'property_name_room',
        'application_method',
        'flow_management_transition',
    ];

    protected function casts(): array
    {
        return [
            'flow_management_transition' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public static function syncFromApplication(Application $application): ?self
    {
        $screeningCompletion = static::query()
            ->where('application_id', $application->id)
            ->first();

        if (! $application->screening_ok) {
            return $screeningCompletion;
        }

        $screeningCompletion ??= new static([
            'application_id' => $application->id,
        ]);

        $screeningCompletion->customer_id = $application->customer_id;
        $screeningCompletion->staff_in_charge = $application->staff_in_charge;
        $screeningCompletion->property_name_room = $application->property_name_room;
        $screeningCompletion->application_method = $application->application_method;
        $screeningCompletion->save();

        static::relinkOrphanedFlowManagement($screeningCompletion);

        return $screeningCompletion;
    }

    protected static function relinkOrphanedFlowManagement(ScreeningCompletion $screeningCompletion): void
    {
        FlowManagement::query()
            ->whereNull('screening_completion_id')
            ->where('customer_id', $screeningCompletion->customer_id)
            ->where('property_name_room', $screeningCompletion->property_name_room)
            ->update(['screening_completion_id' => $screeningCompletion->id]);
    }

    public function flowManagements(): HasMany
    {
        return $this->hasMany(FlowManagement::class);
    }

    public static function syncFromScreeningCompletion(ScreeningCompletion $screeningCompletion): ?FlowManagement
    {
        $flowManagement = FlowManagement::query()
            ->where('screening_completion_id', $screeningCompletion->id)
            ->first();

        if (! $screeningCompletion->flow_management_transition) {
            return $flowManagement;
        }

        $screeningCompletion->loadMissing('application');
        $application = $screeningCompletion->application;

        $flowManagement ??= new FlowManagement([
            'screening_completion_id' => $screeningCompletion->id,
        ]);

        $flowManagement->customer_id = $screeningCompletion->customer_id;
        $flowManagement->staff_in_charge = $screeningCompletion->staff_in_charge;
        $flowManagement->property_name_room = $screeningCompletion->property_name_room;
        $flowManagement->application_method = $screeningCompletion->application_method;

        if (! $flowManagement->exists) {
            $flowManagement->move_in_date = $application?->scheduled_move_in_date;
        }

        $flowManagement->save();

        return $flowManagement;
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
            'staff_in_charge' => '担当者',
            'property_name_room' => '物件名＋部屋番号',
            'application_method' => '申込方法',
            'flow_management_transition' => 'フロー管理移行チェック',
        ];
    }
}
