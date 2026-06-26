<?php

return [

    'url' => env('APP_URL', 'http://localhost/CareEarthHome'),

    'allowed_email' => env('CAREEARTH_ALLOWED_EMAIL', 'tomoya_hayashi@careearth.info'),

    'password_hash' => env(
        'CAREEARTH_PASSWORD_HASH',
        '$2y$10$NseLpbRzBXWBI7g1kRwBSO3sKHuL0r7vJuSlTssfay/QFwKUodp0y'
    ),

    'upload' => [
        'max_size' => (int) env('CAREEARTH_UPLOAD_MAX_SIZE', 10 * 1024 * 1024),
        'extensions' => ['jpg', 'jpeg', 'png', 'pdf'],
    ],

    'session_lifetime' => (int) env('CAREEARTH_SESSION_LIFETIME', 3600),

];
