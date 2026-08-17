<?php

return [

    /*
    | お客様用請求書の原本（参照専用。生成時はコピーして指定セルへ値を入れる）。
    */
    'reference' => resource_path('templates/invoices/reference/お客様用請求書.xlsx'),

    'cells' => [
        'management_number' => 'AF1',
        'issue_date' => 'AE2',
        'contractor' => 'F7',
        'estimated_sales' => 'I12',
        'property_name' => 'K17',
        'property_address' => 'K19',
        'settlement_transfer_date' => 'B47',
    ],

];
