<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyRentalIncomeTermination extends Model
{
    protected $table = 'property_rental_income_terminations';

    protected $fillable = [
        'contract_key',
        'contractor',
        'property_name',
        'move_out_type',
        'move_out_reason',
        'move_out_cost',
        'terminated_on',
        'terminated_at',
    ];

    protected function casts(): array
    {
        return [
            'move_out_cost' => 'integer',
            'terminated_on' => 'date',
            'terminated_at' => 'datetime',
        ];
    }
}
