<?php

return [

    'payment_methods' => [
        'cash' => '現金',
        'account_transfer' => '口座振替',
    ],

    'payment_statuses' => [
        'unpaid' => '未納',
        'temporary' => '一時金',
        'paid' => '納金済',
        'overdue' => '滞納',
    ],

    'payment_month_tabs' => [
        202607,
        202608,
        202609,
    ],

    'max_visible_month_tabs' => 6,

    // お試し機能: 契約期限から月次データを一括登録
    'contract_period_bulk_register' => true,
    'contract_period_max_months' => 120,

    'all_sortable_columns' => [
        'created_on' => '作成日',
        'contractor' => '契約者',
        'payment_month' => '支払い月',
    ],

    'default_all_sort' => 'payment_on',
    'default_all_direction' => 'desc',

];
