<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 管理画面ログイン許可メールアドレス
    |--------------------------------------------------------------------------
    */
    'allowed_emails' => [
        'naok_miyamoto@careearth.info',
        'masato_minamitani@careearth.info',
        'yuta_masui@careearth.info',
        'tomoya_hayashi@careearth.info',
        'mariko_nakamoto@careearth.info',
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Workspace ドメイン（アカウント選択のヒント）
    |--------------------------------------------------------------------------
    */
    'google_hosted_domain' => env('ADMIN_GOOGLE_HOSTED_DOMAIN', 'careearth.info'),

];
