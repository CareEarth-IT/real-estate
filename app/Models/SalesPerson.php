<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPerson extends Model
{
    protected $table = 'sales_persons';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
