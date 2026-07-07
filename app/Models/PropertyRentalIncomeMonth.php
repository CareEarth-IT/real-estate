<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyRentalIncomeMonth extends Model
{
    protected $table = 'property_rental_income_months';

    protected $fillable = [
        'payment_month',
    ];

    protected function casts(): array
    {
        return [
            'payment_month' => 'integer',
        ];
    }
}
