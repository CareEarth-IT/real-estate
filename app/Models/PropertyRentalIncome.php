<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PropertyRentalIncome extends Model
{
    protected $table = 'property_rental_incomes';

    protected $attributes = [
        'payment_status' => 'unpaid',
    ];

    protected $fillable = [
        'created_on',
        'contractor',
        'property_name',
        'rent_year_month',
        'payment_method',
        'rent_amount',
        'payment_status',
        'occupant_count',
        'deposit_amount',
        'payment_month',
        'payment_on',
        'copied_from_id',
    ];

    protected function casts(): array
    {
        return [
            'created_on' => 'date',
            'payment_on' => 'date',
            'rent_year_month' => 'integer',
            'rent_amount' => 'integer',
            'occupant_count' => 'integer',
            'deposit_amount' => 'integer',
            'payment_month' => 'integer',
        ];
    }

    public function nextMonthCopy(): HasOne
    {
        return $this->hasOne(self::class, 'copied_from_id');
    }
}
