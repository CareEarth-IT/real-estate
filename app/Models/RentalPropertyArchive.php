<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RentalPropertyArchive extends Model
{
    protected $fillable = [
        'property_name',
        'floors_above',
        'floors_below',
        'floor_part',
        'room_number',
        'property_type',
        'structure',
        'built_year',
        'built_month',
        'building_condition',
        'postal_code',
        'location',
        'address_detail',
        'block_building',
        'show_on_map',
        'transit1_line',
        'transit1_station',
        'transit1_method',
        'transit1_minutes',
        'transit2_line',
        'transit2_station',
        'transit2_method',
        'transit2_minutes',
        'transit3_line',
        'transit3_station',
        'transit3_method',
        'transit3_minutes',
        'landlord_name',
        'total_units',
        'monthly_rent_major',
        'monthly_rent_minor',
        'has_management_fee',
        'has_key_money',
        'has_security_deposit',
        'has_security_deposit_extra',
        'has_amortization',
        'has_shikibiki',
        'has_guarantee_deposit',
        'has_initial_cost',
        'has_other_fees',
        'brokerage_fee',
        'collateral_required',
        'collateral_amount_major',
        'collateral_amount_minor',
        'collateral_years',
        'contract_lease_type',
        'contract_period_type',
        'contract_years',
        'contract_months',
        'has_guarantor_company',
        'has_parking',
        'has_tokuyu_chin',
        'condition_corporation',
        'condition_student',
        'condition_gender',
        'condition_single',
        'condition_two_tenants',
        'condition_children',
        'condition_pets',
        'condition_instruments',
        'condition_office_use',
        'condition_roomshare',
        'has_free_rent',
        'address',
        'building_age',
        'google_drive_url',
    ];

    protected function casts(): array
    {
        return [
            'floors_above' => 'integer',
            'floors_below' => 'integer',
            'floor_part' => 'integer',
            'built_year' => 'integer',
            'built_month' => 'integer',
            'show_on_map' => 'boolean',
            'transit1_minutes' => 'integer',
            'transit2_minutes' => 'integer',
            'transit3_minutes' => 'integer',
            'monthly_rent_major' => 'integer',
            'monthly_rent_minor' => 'integer',
            'has_management_fee' => 'boolean',
            'has_key_money' => 'boolean',
            'has_security_deposit' => 'boolean',
            'has_security_deposit_extra' => 'boolean',
            'has_amortization' => 'boolean',
            'has_shikibiki' => 'boolean',
            'has_guarantee_deposit' => 'boolean',
            'has_initial_cost' => 'boolean',
            'has_other_fees' => 'boolean',
            'collateral_required' => 'boolean',
            'collateral_amount_major' => 'integer',
            'collateral_amount_minor' => 'integer',
            'collateral_years' => 'integer',
            'contract_years' => 'integer',
            'contract_months' => 'integer',
            'has_guarantor_company' => 'boolean',
            'has_parking' => 'boolean',
            'has_tokuyu_chin' => 'boolean',
            'has_free_rent' => 'boolean',
        ];
    }

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
            'floors_above' => '地上階',
            'floors_below' => '地下階',
            'floor_part' => '階部分',
            'room_number' => '号室',
            'property_type' => '物件種別',
            'structure' => '構造',
            'built_year' => '築年',
            'built_month' => '築月',
            'building_condition' => '築年月区分',
            'postal_code' => '郵便番号',
            'location' => '所在地',
            'address_detail' => '以下住所',
            'block_building' => '街区・号棟',
            'show_on_map' => '地図',
            'transit1_line' => '交通1沿線',
            'transit1_station' => '交通1駅',
            'transit1_method' => '交通1手段',
            'transit1_minutes' => '交通1分',
            'transit2_line' => '交通2沿線',
            'transit2_station' => '交通2駅',
            'transit2_method' => '交通2手段',
            'transit2_minutes' => '交通2分',
            'transit3_line' => '交通3沿線',
            'transit3_station' => '交通3駅',
            'transit3_method' => '交通3手段',
            'transit3_minutes' => '交通3分',
            'landlord_name' => '賃主名',
            'total_units' => '総戸数',
            'monthly_rent_major' => '月額賃料（整数）',
            'monthly_rent_minor' => '月額賃料（小数）',
            'has_management_fee' => '管理費',
            'has_key_money' => '礼金',
            'has_security_deposit' => '敷金',
            'has_security_deposit_extra' => '敷金積増',
            'has_amortization' => '償却金',
            'has_shikibiki' => '敷引',
            'has_guarantee_deposit' => '保証金',
            'has_initial_cost' => '初期費用',
            'has_other_fees' => 'その他諸費用',
            'brokerage_fee' => '仲介手数料',
            'collateral_required' => '担保',
            'collateral_amount_major' => '担保金額（整数）',
            'collateral_amount_minor' => '担保金額（小数）',
            'collateral_years' => '担保年数',
            'contract_lease_type' => '契約期間区分',
            'contract_period_type' => '契約期間指定',
            'contract_years' => '契約年数',
            'contract_months' => '契約月数',
            'has_guarantor_company' => '保証会社',
            'has_parking' => '駐車場',
            'has_tokuyu_chin' => '特優賃',
            'condition_corporation' => '法人',
            'condition_student' => '学生',
            'condition_gender' => '性別',
            'condition_single' => '単身者',
            'condition_two_tenants' => '二人入居',
            'condition_children' => '子供',
            'condition_pets' => 'ペット',
            'condition_instruments' => '楽器',
            'condition_office_use' => '事務所利用',
            'condition_roomshare' => 'ルームシェア',
            'has_free_rent' => 'フリーレント',
            'google_drive_url' => 'Googleドライブ',
        ];
    }

    /**
     * @return list<string>
     */
    public static function editableFields(): array
    {
        return array_keys(self::columnLabels());
    }

    /**
     * @return list<string>
     */
    public static function integerFields(): array
    {
        return [
            'floors_above',
            'floors_below',
            'floor_part',
            'built_year',
            'built_month',
            'transit1_minutes',
            'transit2_minutes',
            'transit3_minutes',
            'monthly_rent_major',
            'monthly_rent_minor',
            'collateral_amount_major',
            'collateral_amount_minor',
            'collateral_years',
            'contract_years',
            'contract_months',
        ];
    }

    /**
     * @return list<string>
     */
    public static function booleanFields(): array
    {
        return [
            'show_on_map',
            'has_management_fee',
            'has_key_money',
            'has_security_deposit',
            'has_security_deposit_extra',
            'has_amortization',
            'has_shikibiki',
            'has_guarantee_deposit',
            'has_initial_cost',
            'has_other_fees',
            'collateral_required',
            'has_guarantor_company',
            'has_parking',
            'has_tokuyu_chin',
            'has_free_rent',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function enumFieldOptions(): array
    {
        return [
            'property_type' => self::propertyTypes(),
            'structure' => self::structures(),
            'building_condition' => self::buildingConditions(),
            'transit1_method' => self::transitMethods(),
            'transit2_method' => self::transitMethods(),
            'transit3_method' => self::transitMethods(),
            'brokerage_fee' => self::brokerageFeeOptions(),
            'contract_lease_type' => self::contractLeaseTypes(),
            'contract_period_type' => self::contractPeriodTypes(),
            'condition_corporation' => ['指定なし', '限定', '希望'],
            'condition_student' => ['指定なし', '限定', '希望'],
            'condition_gender' => ['指定なし', '男性限定', '女性限定'],
            'condition_single' => ['指定なし', '可', '限定'],
            'condition_two_tenants' => ['指定なし', '可'],
            'condition_children' => ['指定なし', '可', '不可'],
            'condition_pets' => ['指定なし', '相談'],
            'condition_instruments' => ['指定なし', '相談'],
            'condition_office_use' => ['指定なし', '相談', '不可'],
            'condition_roomshare' => ['指定なし', '相談', '不可'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function propertyTypes(): array
    {
        return ['アパート', 'マンション', '戸建て'];
    }

    /**
     * @return list<string>
     */
    public static function structures(): array
    {
        return ['鉄骨', 'RC造', '木造'];
    }

    /**
     * @return list<string>
     */
    public static function buildingConditions(): array
    {
        return ['中古', '新築', '未入居'];
    }

    /**
     * @return list<string>
     */
    public static function transitMethods(): array
    {
        return ['徒歩', 'バス', '車'];
    }

    /**
     * @return list<string>
     */
    public static function brokerageFeeOptions(): array
    {
        return ['指定なし', 'あり', '不要'];
    }

    /**
     * @return list<string>
     */
    public static function contractLeaseTypes(): array
    {
        return ['普通借家', '定期借家'];
    }

    /**
     * @return list<string>
     */
    public static function contractPeriodTypes(): array
    {
        return ['指定なし', '期間'];
    }

    public function displayLocation(): string
    {
        return (string) ($this->location ?: $this->address ?: '');
    }
}
