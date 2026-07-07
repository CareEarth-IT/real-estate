<?php

return [

    'statuses' => [
        'for_sale' => '売り出し中',
        'rent_prep' => '賃貸準備中',
        'rent_recruiting' => '賃貸募集中',
        'purchasing' => '買付中',
        'considering' => '検討中',
    ],

    'property_types' => [
        'detached_house' => '戸建て',
        'unit' => '区分',
        'land' => '土地',
        'building' => '一棟',
    ],

    'rows' => [
        ['key' => 'case_number', 'label' => '案件番号', 'format' => 'text'],
        ['key' => 'status', 'label' => '状況', 'format' => 'status'],
        ['key' => 'location', 'label' => '所在地', 'format' => 'text'],
        ['key' => 'property_type', 'label' => '種別', 'format' => 'property_type'],
        ['key' => 'usage', 'label' => '用途', 'format' => 'text'],
        ['key' => 'nationality', 'label' => '国籍', 'format' => 'text'],
        ['key' => 'property_price', 'label' => '物件価格', 'format' => 'yen'],
        ['type' => 'group', 'label' => '登記費用'],
        ['key' => 'registration_license_tax', 'label' => '登録免許税', 'format' => 'yen', 'indent' => true],
        ['key' => 'judicial_scrivener_fee', 'label' => '司法書士報酬', 'format' => 'yen', 'indent' => true],
        ['key' => 'postage', 'label' => '郵送料', 'format' => 'yen', 'indent' => true],
        ['key' => 'pre_registration_info_fee', 'label' => '事前登記情報取得', 'format' => 'yen', 'indent' => true],
        ['key' => 'post_registration_certificate_fee', 'label' => '事後全部事項証明書取得', 'format' => 'yen', 'indent' => true],
        ['key' => 'withholding_income_tax', 'label' => '源泉所得税', 'format' => 'yen_signed', 'indent' => true],
        ['key' => 'purchase_brokerage_fee', 'label' => '仲介手数料（購入時）', 'format' => 'yen'],
        ['type' => 'group', 'label' => '固定資産税', 'group_key' => 'property_taxes'],
        ['key' => 'building_consumption_tax', 'label' => '建物消費税', 'format' => 'yen'],
        ['key' => 'real_estate_acquisition_tax', 'label' => '不動産取得税', 'format' => 'yen'],
        ['key' => 'renovation_cost', 'label' => 'リフォーム費用', 'format' => 'yen'],
        ['key' => 'contingency_fund', 'label' => '予備費', 'format' => 'yen'],
        ['key' => 'total_cost', 'label' => '原価計（物件価格＋諸費用）', 'format' => 'yen', 'highlight' => 'cost', 'computed' => true],
        ['key' => 'expected_selling_price', 'label' => '販売想定価格', 'format' => 'yen', 'highlight' => 'price'],
        ['key' => 'cost_ratio', 'label' => '原価率', 'format' => 'percent', 'computed' => true],
        ['key' => 'gross_profit_margin', 'label' => '粗利率', 'format' => 'percent', 'computed' => true],
        ['type' => 'group', 'label' => '広告費', 'group_key' => 'ad_fees', 'subtitle' => '（月額×掲載月）'],
        ['key' => 'sale_brokerage_fee', 'label' => '仲介手数料（売却時）', 'format' => 'yen'],
        ['key' => 'contract_stamp_duty', 'label' => '契約書印紙代', 'format' => 'yen'],
        ['key' => 'receipt_stamp_duty', 'label' => '領収書印紙代', 'format' => 'yen'],
        ['key' => 'total_selling_admin_expenses', 'label' => '販売管理費計（直接）', 'format' => 'yen', 'computed' => true],
        ['key' => 'estimated_operating_profit_margin', 'label' => '概算営業利益率', 'format' => 'percent', 'computed' => true],
        ['key' => 'expected_rent', 'label' => '想定賃料', 'format' => 'yen'],
        ['key' => 'expected_surface_yield', 'label' => '想定表面利回り', 'format' => 'percent', 'computed' => true],
        ['key' => 'estimated_ownership_yield', 'label' => '保有時概算利回り', 'format' => 'percent', 'computed' => true],
        ['type' => 'documents', 'label' => '書類'],
    ],

];
