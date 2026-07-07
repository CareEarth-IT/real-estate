<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDealDraftPropertyTax extends Model
{
    protected $fillable = [
        'property_deal_draft_id',
        'fiscal_year',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'fiscal_year' => 'integer',
            'amount' => 'integer',
        ];
    }

    public function propertyDealDraft(): BelongsTo
    {
        return $this->belongsTo(PropertyDealDraft::class);
    }
}
