<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Customer;
use App\Models\FlowManagement;
use App\Models\Property;
use App\Models\PropertyDealDraft;
use App\Models\PropertyRentalIncome;
use App\Models\SettlementManagement;
use App\Support\PropertyRentalIncomeMonths;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedProperties();
        $this->seedDealDrafts();
        $this->seedRentalIncomes();
        $this->seedRentalManagement();
    }

    private function seedProperties(): void
    {
        $salesPersons = ['田中 一郎', '佐藤 花子', '鈴木 健太', '高橋 美咲', '伊藤 直樹'];
        $addresses = [
            '東京都渋谷区神南1-2-3',
            '東京都新宿区西新宿2-8-1',
            '東京都港区六本木3-2-1',
            '神奈川県横浜市西区みなとみらい4-4-2',
            '大阪府大阪市北区梅田1-1-3',
            '愛知県名古屋市中区栄2-3-1',
            '福岡県福岡市博多区博多駅前1-1-1',
            '北海道札幌市中央区北5条西2-5',
            '宮城県仙台市青葉区一番町3-7-1',
            '広島県広島市中区基町6-78',
        ];

        for ($i = 0; $i < 10; $i++) {
            $building = 15000000 + ($i * 850000);
            $land = 8000000 + ($i * 420000);

            Property::query()->create([
                'created_at' => now()->subDays(30 - $i),
                'buyer_name' => ['山田太郎', '佐藤花子', '鈴木一郎', '田中美咲', '渡辺健', '伊藤翔', '中村結衣', '小林大輔', '加藤愛', '吉田直樹'][$i],
                'broker_name' => ['〇〇不動産', '△△ホーム', '□□リアルティ', '◇◇エステート', '☆☆住宅'][$i % 5],
                'owner_name' => 'デモオーナー'.($i + 1),
                'property_address' => $addresses[$i],
                'building_price' => $building,
                'land_price' => $land,
                'price_mode' => $i % 3 === 0 ? 'total' : 'split',
                'total_price' => $building + $land,
                'registration_fee' => 120000 + ($i * 5000),
                'brokerage_fee' => 450000 + ($i * 25000),
                'property_tax' => 85000 + ($i * 3000),
                'sales_person' => $salesPersons[$i % count($salesPersons)],
                'updated_at' => now()->subDays(30 - $i),
            ]);
        }
    }

    private function seedDealDrafts(): void
    {
        $statuses = ['for_sale', 'rent_prep', 'rent_recruiting', 'purchasing', 'considering'];
        $types = ['detached_house', 'unit', 'land', 'building'];
        $locations = [
            '堺市西区', '大阪市北区', '神戸市中央区', '京都市下京区', '奈良市西大寺',
            '東大阪市', '枚方市', '吹田市', '豊中市', '高槻市',
        ];
        $usages = ['学生シェアH', '単身者向け', 'ファミリー', '事業用', '民泊想定'];
        $nationalities = ['日本', 'ミャンマー', 'ベトナム', '中国', 'ネパール'];

        for ($i = 0; $i < 10; $i++) {
            $propertyPrice = 2800000 + ($i * 320000);
            $totalCost = $propertyPrice + 1200000 + ($i * 80000);
            $expectedSelling = $totalCost + 3500000 + ($i * 120000);

            PropertyDealDraft::query()->create([
                'case_number' => 'K'.str_pad((string) (1002 + $i), 4, '0', STR_PAD_LEFT),
                'status' => $statuses[$i % count($statuses)],
                'location' => $locations[$i],
                'property_type' => $types[$i % count($types)],
                'usage' => $usages[$i % count($usages)],
                'nationality' => $nationalities[$i % count($nationalities)],
                'property_price' => $propertyPrice,
                'registration_license_tax' => 73600 + ($i * 1200),
                'judicial_scrivener_fee' => 38500,
                'postage' => 2000,
                'purchase_brokerage_fee' => 330000 + ($i * 15000),
                'renovation_cost' => 6500000 - ($i * 120000),
                'total_cost' => $totalCost,
                'expected_selling_price' => $expectedSelling,
                'cost_ratio' => round(($totalCost / $expectedSelling) * 100, 1),
                'gross_profit_margin' => round((($expectedSelling - $totalCost) / $expectedSelling) * 100, 1),
                'sale_brokerage_fee' => 475000,
                'expected_rent' => 120000 + ($i * 5000),
                'expected_surface_yield' => round(((120000 + ($i * 5000)) * 12 / $expectedSelling) * 100, 1),
                'estimated_ownership_yield' => 10.5 + ($i * 0.3),
                'created_at' => now()->subDays(25 - $i),
                'updated_at' => now()->subDays(25 - $i),
            ]);
        }
    }

    private function seedRentalIncomes(): void
    {
        $paymentStatuses = ['unpaid', 'temporary', 'paid', 'overdue'];
        $paymentMethods = ['cash', 'account_transfer'];
        $contractors = [
            'アウ ミン', 'グエン ヴァン アン', 'リー シャオ', 'パク ジヨン',
            'スミス ジョン', '田中 太郎', '佐藤 花子', '鈴木 健太', '高橋 美咲', '伊藤 直樹',
        ];
        $properties = [
            'グランドメゾン渋谷', 'パークハウス新宿', 'ライオンズ池袋', 'ブランズ品川',
            'シティタワー横浜', 'レジデンス大宮', 'コンフォート千葉', 'アーバン福岡',
            'プレミアム仙台', 'サンライズ広島',
        ];

        for ($i = 0; $i < 10; $i++) {
            $paymentMonth = (int) now()->subMonths(9 - $i)->format('Ym');
            PropertyRentalIncomeMonths::ensure($paymentMonth);

            $paymentOn = Carbon::createFromFormat('Ym', (string) $paymentMonth)->day(5 + ($i % 20));

            PropertyRentalIncome::query()->create([
                'created_on' => now()->subDays(20 - $i),
                'contractor' => $contractors[$i],
                'property_name' => $properties[$i],
                'rent_year_month' => $paymentMonth,
                'payment_method' => $paymentMethods[$i % 2],
                'rent_amount' => 68000 + ($i * 2500),
                'payment_status' => $paymentStatuses[$i % count($paymentStatuses)],
                'occupant_count' => 1 + ($i % 4),
                'deposit_amount' => 136000 + ($i * 10000),
                'payment_month' => $paymentMonth,
                'payment_on' => $paymentOn,
            ]);
        }
    }

    private function seedRentalManagement(): void
    {
        $staffMembers = ['田中 一郎', '佐藤 花子', '鈴木 健太', '高橋 美咲', '伊藤 直樹'];
        $properties = [
            ['name' => 'グランドメゾン渋谷', 'room' => '301'],
            ['name' => 'パークハウス新宿', 'room' => '1205'],
            ['name' => 'ライオンズマンション池袋', 'room' => '502'],
            ['name' => 'ブランズタワー品川', 'room' => '1802'],
            ['name' => 'シティタワー横浜', 'room' => '905'],
            ['name' => 'レジデンス大宮', 'room' => '203'],
            ['name' => 'コンフォート千葉', 'room' => '1101'],
            ['name' => 'アーバンコート福岡', 'room' => '701'],
            ['name' => 'プレミアムレジデンス仙台', 'room' => '405'],
            ['name' => 'サンライズ広島', 'room' => '802'],
        ];
        $managementCompanies = [
            '大京アステージ', 'レオパレス21', 'タカシン管理', '日本財託管理サービス', '三菱地所ハウスネット',
        ];
        $applicationMethods = ['Web申込', '店頭申込', '電話申込', '紹介', '内見後申込'];
        $activeStatuses = [
            "書類確認中\n管理会社へ送付済み",
            "審査中\n入居者情報確認待ち",
            "契約準備中\n重要事項説明予定",
            "入居日調整中",
        ];
        $familyNames = ['山田', '佐藤', '鈴木', '田中', '渡辺', '伊藤', '中村', '小林', '加藤', '吉田'];
        $givenNames = ['太郎', '花子', '健太', '美咲', '翔', '結衣', '大輔', '愛', '直樹', 'さくら'];

        $applicationProfiles = [
            ['screening_ok' => false, 'is_cancelled' => false],
            ['screening_ok' => false, 'is_cancelled' => false],
            ['screening_ok' => false, 'is_cancelled' => false],
            ['screening_ok' => false, 'is_cancelled' => false],
            ['screening_ok' => true, 'is_cancelled' => false],
            ['screening_ok' => true, 'is_cancelled' => false],
            ['screening_ok' => true, 'is_cancelled' => false],
            ['screening_ok' => true, 'is_cancelled' => false],
            ['screening_ok' => true, 'is_cancelled' => false],
            ['screening_ok' => false, 'is_cancelled' => true],
        ];

        for ($i = 0; $i < 10; $i++) {
            $property = $properties[$i];
            $moveInDate = now()->addDays(10 + ($i * 4));
            $profile = $applicationProfiles[$i];
            $hasBrokerFee = $i % 2 === 0;

            $customer = Customer::query()->create([
                'name' => $familyNames[$i].' '.$givenNames[$i],
                'move_in_date' => $moveInDate,
                'contract_period' => ($i % 3 + 1).'年',
                'contract_period_type' => $i % 2 === 0,
                'property_name' => $property['name'],
                'room_number' => $property['room'],
                'address' => '東京都'.['渋谷区', '新宿区', '豊島区', '港区', '品川区'][$i % 5].'デモ'.($i + 1).'丁目',
                'management_company' => $managementCompanies[$i % count($managementCompanies)],
                'date_of_birth' => now()->subYears(24 + ($i % 12)),
                'is_married' => $i % 3 !== 0,
                'mobile_number' => '090-'.str_pad((string) (2000 + $i), 4, '0', STR_PAD_LEFT).'-'.str_pad((string) (3000 + $i), 4, '0', STR_PAD_LEFT),
                'email' => 'demo.customer'.($i + 1).'@example.com',
                'occupation' => ['会社員', '自営業', '学生', '公務員', 'フリーランス'][$i % 5],
                'company_or_school_name' => 'デモ株式会社'.($i + 1),
                'company_or_school_phone' => '03-'.str_pad((string) (3000 + $i), 4, '0', STR_PAD_LEFT).'-'.str_pad((string) (4000 + $i), 4, '0', STR_PAD_LEFT),
                'company_or_school_address' => '東京都千代田区デモビル'.($i + 1).'F',
                'emergency_contact_name' => $familyNames[($i + 1) % 10].' '.$givenNames[($i + 2) % 10],
                'emergency_contact_relationship' => ['父', '母', '配偶者', '兄弟', '姉妹'][$i % 5],
                'emergency_contact_date_of_birth' => now()->subYears(48 + ($i % 8)),
                'emergency_contact_address' => '神奈川県横浜市デモ区'.($i + 10).'番地',
                'emergency_contact_mobile' => '080-'.str_pad((string) (4000 + $i), 4, '0', STR_PAD_LEFT).'-'.str_pad((string) (5000 + $i), 4, '0', STR_PAD_LEFT),
                'emergency_contact_email' => 'demo.emergency'.($i + 1).'@example.com',
                'customer_info_completed' => $profile['screening_ok'],
            ]);

            $status = $profile['is_cancelled']
                ? "キャンセル\n内見のみ"
                : ($profile['screening_ok']
                    ? "審査ＯＫ\n書類管理へ移行"
                    : $activeStatuses[$i % count($activeStatuses)]);

            $application = Application::query()->create([
                'customer_id' => $customer->id,
                'property_name' => $property['name'],
                'room_number' => $property['room'],
                'staff_in_charge' => $staffMembers[$i % count($staffMembers)],
                'scheduled_move_in_date' => $moveInDate,
                'advertising_fee' => 55000 + ($i * 4500),
                'has_broker_fee' => $hasBrokerFee,
                'broker_fee' => $hasBrokerFee ? 110000 + ($i * 8000) : null,
                'management_company_name' => $managementCompanies[$i % count($managementCompanies)],
                'application_method' => $applicationMethods[$i % count($applicationMethods)],
                'status' => $status,
                'memo' => $i % 2 === 0 ? 'デモデータ: 内見済み' : null,
                'property_documents_url' => $i % 3 === 0 ? 'https://example.com/demo/property/'.($i + 1) : null,
                'appliance_support_notes' => $i % 4 === 0 ? '家電セット希望' : null,
                'sales_action_required' => $i % 5 === 0,
                'screening_ok' => $profile['screening_ok'],
                'is_cancelled' => $profile['is_cancelled'],
                'created_at' => now()->subDays(18 - $i),
                'updated_at' => now()->subDays(18 - $i),
            ]);

            if (! $profile['screening_ok']) {
                continue;
            }

            $flow = FlowManagement::syncFromApplication($application->fresh());
            if ($flow === null) {
                continue;
            }

            $flow->update([
                'memo' => 'デモ: 書類管理'.($i + 1),
                'move_in_date' => $moveInDate,
                'document_deadline' => '入居2週間前まで',
                'scheduled_visit_date' => now()->addDays(3 + $i),
                'documents_completed' => $i % 2 === 0,
                'documents_arrived' => $i % 3 === 0,
                'important_matters_explanation_creation' => $i % 4 === 0,
                'key_received' => $i >= 7,
                'settlement_transition' => $i >= 5,
            ]);

            if ($flow->settlement_transition) {
                SettlementManagement::syncFromFlowManagement($flow->fresh());
            }
        }
    }
}
