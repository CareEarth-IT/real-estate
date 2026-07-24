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
        'floor_plan_rooms',
        'floor_plan_type',
        'area_major',
        'area_minor',
        'balcony_area_major',
        'balcony_area_minor',
        'opening_direction',
        'washitsu_tatami',
        'yoshitsu_tatami',
        'ldk_detail',
        'nando_sizes',
        'loft_sizes',
        'study_sizes',
        'sunroom_sizes',
        'grenier_sizes',
        'move_in_schedule',
        'transaction_type',
        'source_company_name',
        'source_staff_name',
        'source_phone',
        'source_confirmed_on',
        'company_property_code',
        'net_listing',
        'third_party_copy',
        'surroundings',
        'management_form',
        'has_reform_detail',
        'has_other_transit',
        'energy_performance',
        'has_env_facility_distance_1',
        'has_env_facility_distance_2',
        'has_env_facility_adjacent_1',
        'has_env_facility_adjacent_2',
        'has_env_facility_1f',
        'insulation_grade',
        'utility_cost_major',
        'utility_cost_minor',
        'location_environment',
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
            'floor_plan_rooms' => 'integer',
            'area_major' => 'integer',
            'area_minor' => 'integer',
            'balcony_area_major' => 'integer',
            'balcony_area_minor' => 'integer',
            'washitsu_tatami' => 'array',
            'yoshitsu_tatami' => 'array',
            'nando_sizes' => 'array',
            'loft_sizes' => 'array',
            'study_sizes' => 'array',
            'sunroom_sizes' => 'array',
            'grenier_sizes' => 'array',
            'source_confirmed_on' => 'date',
            'surroundings' => 'array',
            'has_reform_detail' => 'boolean',
            'has_other_transit' => 'boolean',
            'has_env_facility_distance_1' => 'boolean',
            'has_env_facility_distance_2' => 'boolean',
            'has_env_facility_adjacent_1' => 'boolean',
            'has_env_facility_adjacent_2' => 'boolean',
            'has_env_facility_1f' => 'boolean',
            'insulation_grade' => 'integer',
            'utility_cost_major' => 'integer',
            'utility_cost_minor' => 'integer',
            'location_environment' => 'array',
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
            'floor_plan_rooms' => '間取り',
            'floor_plan_type' => '間取りタイプ',
            'area_major' => '面積（整数）',
            'area_minor' => '面積（小数）',
            'balcony_area_major' => 'バルコニー面積（整数）',
            'balcony_area_minor' => 'バルコニー面積（小数）',
            'opening_direction' => '開口向き',
            'washitsu_tatami' => '和室（畳数）',
            'yoshitsu_tatami' => '洋室（畳数）',
            'ldk_detail' => 'LDK詳細',
            'nando_sizes' => '納戸',
            'loft_sizes' => 'ロフト',
            'study_sizes' => '書斎',
            'sunroom_sizes' => 'サンルーム',
            'grenier_sizes' => 'グルニエ',
            'move_in_schedule' => '入居予定',
            'transaction_type' => '取引態様',
            'source_company_name' => '元付会社名',
            'source_staff_name' => '元付担当者',
            'source_phone' => '元付電話番号',
            'source_confirmed_on' => '元付確認日',
            'company_property_code' => '貴社物件コード',
            'net_listing' => 'ネット掲載',
            'third_party_copy' => '他者によるコピー',
            'surroundings' => '周辺環境',
            'management_form' => '管理形態',
            'has_reform_detail' => 'リフォーム詳細',
            'has_other_transit' => '他交通機関',
            'energy_performance' => 'エネルギー消費性能',
            'has_env_facility_distance_1' => '環境設備・距離１',
            'has_env_facility_distance_2' => '環境設備・距離２',
            'has_env_facility_adjacent_1' => '環境設備・隣接１',
            'has_env_facility_adjacent_2' => '環境設備・隣接２',
            'has_env_facility_1f' => '環境設備・１F',
            'insulation_grade' => '断熱性能',
            'utility_cost_major' => '目安光熱費（整数）',
            'utility_cost_minor' => '目安光熱費（小数）',
            'location_environment' => '立地・環境',
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
            'floor_plan_rooms',
            'area_major',
            'area_minor',
            'balcony_area_major',
            'balcony_area_minor',
            'insulation_grade',
            'utility_cost_major',
            'utility_cost_minor',
        ];
    }

    /**
     * @return array<string, int>
     */
    public static function arrayFields(): array
    {
        return [
            'washitsu_tatami' => 10,
            'yoshitsu_tatami' => 10,
            'nando_sizes' => 5,
            'loft_sizes' => 2,
            'study_sizes' => 2,
            'sunroom_sizes' => 2,
            'grenier_sizes' => 2,
        ];
    }

    public static function surroundingsRowCount(): int
    {
        return 5;
    }

    /**
     * @return list<string>
     */
    public static function surroundingCategories(): array
    {
        return [
            'スーパー',
            'コンビニ',
            'ドラッグストア',
            'ホームセンター',
            'ショッピングセンター',
            '郵便局',
            '学校',
            '銀行',
        ];
    }

    /**
     * @return list<string>
     */
    /**
     * @return list<string>
     */
    public static function locationEnvironmentLocationOptions(): array
    {
        return [
            '始発駅',
            '2駅利用可',
            '2沿線利用可',
            '3駅以上利用可',
            '3沿線以上利用可',
            '路面電車沿線',
            'バス2路線',
            '100円バス路線',
            '駅まで平坦',
            '駅前',
            'ひな壇に立地',
            '防火地域に立地',
            '高台に立地',
            '平坦地',
            '前面棟無',
            '電線埋設',
            '1種低層',
            'オーシャンビュー',
            'リバーサイド',
            '田園風景',
            '花火大会鑑賞',
            '閑静な住宅地',
            '緑豊かな住宅地',
            '区画整理地内',
            '大型タウン内',
            '風致地区',
            '文教地区',
            '防犯強化地域',
            '駅徒歩5分以内',
            '駅徒歩10分以内',
        ];
    }

    /**
     * @return list<string>
     */
    public static function locationEnvironmentStructureOptions(): array
    {
        return [
            '耐震構造',
            '制震構造',
            '免震構造',
            '耐火構造',
            '準耐火構造',
            '簡易耐火構造',
            '耐風構造',
            '二重床構造',
            '二重天井構造',
            '通気断熱WB工法',
            'アウトポール工法',
            '外断熱工法',
            '逆梁工法',
            '2世帯住宅',
            '2×4工法',
            '2×6工法',
            '高気密住宅',
            '高断熱住宅',
            '高気密高断熱住宅',
            'ノンホルムアルデヒド',
            'ホルムアルデヒド対策',
            '100年コンクリート',
            '建設住宅性能評価付',
            '設計住宅性能評価付',
            '耐震補強工事済',
            '更新対策',
            '劣化対策',
            '省エネルギー対策',
            '平屋',
        ];
    }

    /**
     * @return list<string>
     */
    public static function locationEnvironmentBuildingOptions(): array
    {
        return [
            'タワー型マンション',
            'ログハウス',
            'デザイナーズ',
            '高床式',
            '外装コンクリート',
            '内装コンクリート',
            '外壁サイディング',
            '外壁タイル張り',
            '光触媒塗装',
            '珪藻土塗り壁',
            'ベタ基礎',
            'オープン外構',
            '間口8m以上',
            '間口10m以上',
            '可動間仕切り',
            '吹抜け',
            '天井高2.5m以上',
            '天井高2.7m以上',
            '天井高3m以上',
            '折上天井',
            '無添加塗装',
            'バリアフリー',
            'フラットフロア',
            'メーターモジュール',
            '温泉付',
            '分譲賃貸',
            '四方角部屋',
        ];
    }

    /**
     * @return list<string>
     */
    public static function locationEnvironmentOptions(): array
    {
        return [
            ...self::locationEnvironmentLocationOptions(),
            ...self::locationEnvironmentStructureOptions(),
            ...self::locationEnvironmentBuildingOptions(),
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function tagGroupOptions(): array
    {
        return [
            'location_environment' => self::locationEnvironmentOptions(),
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
            'has_reform_detail',
            'has_other_transit',
            'has_env_facility_distance_1',
            'has_env_facility_distance_2',
            'has_env_facility_adjacent_1',
            'has_env_facility_adjacent_2',
            'has_env_facility_1f',
        ];
    }

    /**
     * @return list<string>
     */
    public static function dateFields(): array
    {
        return ['source_confirmed_on'];
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
            'opening_direction' => ['北', '北東', '東', '南東', '南', '南西', '西', '北西'],
            'floor_plan_type' => ['R', 'K', 'DK', 'LDK', 'SLDK'],
            'move_in_schedule' => ['即', '相談', '指定アリ'],
            'transaction_type' => ['仲介先物'],
            'management_form' => ['常駐管理', '通勤管理', '巡回管理', '不明'],
            'energy_performance' => ['指定なし', '再エネなし', '再エネあり'],
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
