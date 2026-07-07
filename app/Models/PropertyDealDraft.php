<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyDealDraft extends Model
{
    protected $fillable = [
        'case_number',
        'status',
        'location',
        'property_type',
        'usage',
        'nationality',
        'property_price',
        'registration_license_tax',
        'judicial_scrivener_fee',
        'postage',
        'pre_registration_info_fee',
        'post_registration_certificate_fee',
        'withholding_income_tax',
        'purchase_brokerage_fee',
        'building_consumption_tax',
        'real_estate_acquisition_tax',
        'renovation_cost',
        'contingency_fund',
        'total_cost',
        'expected_selling_price',
        'cost_ratio',
        'gross_profit_margin',
        'sale_brokerage_fee',
        'contract_stamp_duty',
        'receipt_stamp_duty',
        'total_selling_admin_expenses',
        'estimated_operating_profit_margin',
        'expected_rent',
        'expected_surface_yield',
        'estimated_ownership_yield',
    ];

    public function adFees(): HasMany
    {
        return $this->hasMany(PropertyDealDraftAdFee::class)->orderBy('sort_order')->orderBy('id');
    }

    public function propertyTaxes(): HasMany
    {
        return $this->hasMany(PropertyDealDraftPropertyTax::class)->orderBy('fiscal_year');
    }

    protected function casts(): array
    {
        return [
            'property_price' => 'integer',
            'registration_license_tax' => 'integer',
            'judicial_scrivener_fee' => 'integer',
            'postage' => 'integer',
            'pre_registration_info_fee' => 'integer',
            'post_registration_certificate_fee' => 'integer',
            'withholding_income_tax' => 'integer',
            'purchase_brokerage_fee' => 'integer',
            'building_consumption_tax' => 'integer',
            'real_estate_acquisition_tax' => 'integer',
            'renovation_cost' => 'integer',
            'contingency_fund' => 'integer',
            'total_cost' => 'integer',
            'expected_selling_price' => 'integer',
            'cost_ratio' => 'decimal:1',
            'gross_profit_margin' => 'decimal:1',
            'sale_brokerage_fee' => 'integer',
            'contract_stamp_duty' => 'integer',
            'receipt_stamp_duty' => 'integer',
            'total_selling_admin_expenses' => 'integer',
            'estimated_operating_profit_margin' => 'decimal:1',
            'expected_rent' => 'integer',
            'expected_surface_yield' => 'decimal:1',
            'estimated_ownership_yield' => 'decimal:1',
        ];
    }
}

