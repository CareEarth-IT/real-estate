<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class ApplicationSampleSeeder extends Seeder
{
    public function run(): void
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
        ];
        $managementCompanies = [
            '大京アステージ',
            'レオパレス21',
            'タカシン管理',
            '日本財託管理サービス',
            '三菱地所ハウスネット',
        ];
        $applicationMethods = ['Web申込', '店頭申込', '電話申込', '紹介', '内見後申込'];
        $statuses = [
            "書類確認中\n管理会社へ送付済み",
            "審査中\n入居者情報確認待ち",
            "契約準備中\n重要事項説明予定",
            "入居日調整中",
            "追加書類提出待ち",
        ];
        $familyNames = ['山田', '佐藤', '鈴木', '田中', '渡辺', '伊藤', '中村', '小林', '加藤', '吉田'];
        $givenNames = ['太郎', '花子', '健太', '美咲', '翔', '結衣', '大輔', '愛', '直樹', 'さくら'];

        for ($i = 0; $i < 20; $i++) {
            $property = $properties[$i % count($properties)];
            $moveInDate = now()->addDays(14 + ($i * 3));
            $contractPeriod = $moveInDate->copy()->addYears(2);

            $customer = Customer::create([
                'name' => $familyNames[$i % count($familyNames)].' '.$givenNames[$i % count($givenNames)],
                'move_in_date' => $moveInDate,
                'contract_period' => ($i % 3 + 1).'年',
                'contract_period_type' => $i % 2 === 0,
                'property_name' => $property['name'],
                'room_number' => $property['room'],
                'address' => '東京都'.['渋谷区', '新宿区', '豊島区', '港区', '品川区'][$i % 5].'サンプル'.($i + 1).'丁目',
                'management_company' => $managementCompanies[$i % count($managementCompanies)],
                'date_of_birth' => now()->subYears(25 + ($i % 15))->subDays($i * 11),
                'is_married' => $i % 3 !== 0,
                'mobile_number' => '090-'.str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT).'-'.str_pad((string) (5000 + $i), 4, '0', STR_PAD_LEFT),
                'email' => 'sample'.($i + 1).'@example.com',
                'occupation' => ['会社員', '自営業', '学生', '公務員', 'フリーランス'][$i % 5],
                'company_or_school_name' => ['株式会社サンプル', '東京大学', '個人事業', '〇〇市役所', 'デザイン事務所'][$i % 5],
                'company_or_school_phone' => '03-'.str_pad((string) (1000 + $i), 4, '0', STR_PAD_LEFT).'-'.str_pad((string) (2000 + $i), 4, '0', STR_PAD_LEFT),
                'company_or_school_address' => '東京都千代田区サンプルビル'.($i + 1).'F',
                'emergency_contact_name' => $familyNames[($i + 2) % count($familyNames)].' '.$givenNames[($i + 3) % count($givenNames)],
                'emergency_contact_relationship' => ['父', '母', '配偶者', '兄弟', '姉妹'][$i % 5],
                'emergency_contact_date_of_birth' => now()->subYears(50 + ($i % 10)),
                'emergency_contact_address' => '神奈川県横浜市サンプル区'.($i + 10).'番地',
                'emergency_contact_mobile' => '080-'.str_pad((string) (2000 + $i), 4, '0', STR_PAD_LEFT).'-'.str_pad((string) (6000 + $i), 4, '0', STR_PAD_LEFT),
                'emergency_contact_email' => 'emergency'.($i + 1).'@example.com',
            ]);

            $isCancelled = $i % 7 === 0;
            $screeningOk = ! $isCancelled && $i % 4 === 0;
            $hasBrokerFee = $i % 2 === 0;

            Application::create([
                'customer_id' => $customer->id,
                'property_name' => $property['name'],
                'room_number' => $property['room'],
                'staff_in_charge' => $staffMembers[$i % count($staffMembers)],
                'scheduled_move_in_date' => $moveInDate,
                'advertising_fee' => 50000 + ($i * 5000),
                'has_broker_fee' => $hasBrokerFee,
                'broker_fee' => $hasBrokerFee ? 100000 + ($i * 10000) : null,
                'management_company_name' => $managementCompanies[$i % count($managementCompanies)],
                'application_method' => $applicationMethods[$i % count($applicationMethods)],
                'status' => $statuses[$i % count($statuses)],
                'memo' => $i % 3 === 0 ? '内見済み。ペット不可物件。' : null,
                'property_documents_url' => $i % 2 === 0 ? 'https://example.com/property/'.($i + 1) : null,
                'appliance_support_notes' => $i % 5 === 0 ? '家電セット希望あり' : null,
                'sales_action_required' => $i % 6 === 0,
                'screening_ok' => $screeningOk,
                'is_cancelled' => $isCancelled,
                'created_at' => now()->subDays(20 - $i),
                'updated_at' => now()->subDays(20 - $i),
            ]);
        }
    }
}
