<?php

return [

    /*
    | 領収書の原本（参照専用。生成時はコピーして指定セルへ値を入れる）。
    */
    'reference' => resource_path('templates/invoices/reference/領収書.xlsx'),

    'cells' => [
        'management_company_name' => 'B6',
        'amount' => 'D7',
        'blank' => 'D11',
        'issue_date' => 'C12',
        'body_amount' => 'E15',
        'tax_amount' => 'E16',
    ],

];
