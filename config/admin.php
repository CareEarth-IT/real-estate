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

    /*
    |--------------------------------------------------------------------------
    | ローカル開発用メール＋パスワードログイン
    |--------------------------------------------------------------------------
    | Google OAuth 未設定時、または ADMIN_LOCAL_LOGIN_ENABLED=true のとき有効。
    | 許可メール（allowed_emails）のみ、local_password_hash と一致すればログイン可。
    */
    'local_login_enabled' => env('ADMIN_LOCAL_LOGIN_ENABLED', env('APP_ENV') === 'local'),

    'local_password_hash' => env(
        'ADMIN_LOCAL_PASSWORD_HASH',
        env('CAREEARTH_PASSWORD_HASH', '$2y$10$NseLpbRzBXWBI7g1kRwBSO3sKHuL0r7vJuSlTssfay/QFwKUodp0y'),
    ),

];
