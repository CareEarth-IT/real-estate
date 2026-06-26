<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'property_master';

    public $timestamps = true;

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'created_at',
        'buyer_name',
        'broker_name',
        'owner_name',
        'property_address',
        'building_price',
        'land_price',
        'price_mode',
        'total_price',
        'registration_fee',
        'brokerage_fee',
        'property_tax',
        'sales_person',
        'purchase_certificate',
        'seal_certificate',
        'registry_certificate',
        'property_registry',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'building_price' => 'integer',
            'land_price' => 'integer',
            'total_price' => 'integer',
            'registration_fee' => 'integer',
            'brokerage_fee' => 'integer',
            'property_tax' => 'integer',
        ];
    }
}
