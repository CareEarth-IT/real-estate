<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalPropertyArchiveImage extends Model
{
    protected $fillable = [
        'rental_property_archive_id',
        'path',
        'original_name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function archive(): BelongsTo
    {
        return $this->belongsTo(RentalPropertyArchive::class, 'rental_property_archive_id');
    }
}
