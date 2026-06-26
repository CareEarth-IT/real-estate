<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertyAddress extends Model
{
    protected $table = 'property_addresses';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'address',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
