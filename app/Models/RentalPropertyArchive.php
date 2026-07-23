<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalPropertyArchive extends Model
{
    protected $fillable = [
        'property_name',
        'address',
        'building_age',
        'google_drive_url',
    ];

    public function images(): HasMany
    {
        return $this->hasMany(RentalPropertyArchiveImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return array<string, string>
     */
    public static function columnLabels(): array
    {
        return [
            'property_name' => '物件名',
            'address' => '住所',
            'building_age' => '築年数',
            'google_drive_url' => 'Googleドライブ',
        ];
    }

    /**
     * @return list<string>
     */
    public static function editableFields(): array
    {
        return ['property_name', 'address', 'building_age', 'google_drive_url'];
    }
}
