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

    'session_lifetime' => (int) env('CAREEARTH_SESSION_LIFETIME', 7200),

    'employee_portal' => [
        'api_url' => rtrim((string) env('EMPLOYEE_PORTAL_API_URL', ''), '/'),
        'proxy_secret' => (string) env('EMPLOYEE_PORTAL_PROXY_SECRET', ''),
        'default_department' => (string) env('EMPLOYEE_PORTAL_DEFAULT_DEPARTMENT', '不動産'),
        'default_status' => (string) env('EMPLOYEE_PORTAL_DEFAULT_STATUS', '在籍'),
        'timeout' => (int) env('EMPLOYEE_PORTAL_TIMEOUT', 10),
    ],

];
