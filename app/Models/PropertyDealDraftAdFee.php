<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyDealDraftAdFee extends Model
{
    protected $fillable = [
        'property_deal_draft_id',
        'agency_name',
        'amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function propertyDealDraft(): BelongsTo
    {
        return $this->belongsTo(PropertyDealDraft::class);
    }
}
