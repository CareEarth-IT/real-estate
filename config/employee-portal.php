<?php

return [

    /*
    | 社員ポータル連携（SSO / 社員一覧参照）
    | 秘密鍵の実値は .env のみに置き、リポジトリへコミットしないこと。
    */

    'api_url' => rtrim((string) env('EMPLOYEE_PORTAL_API_URL', ''), '/'),

    'proxy_secret' => (string) env('EMPLOYEE_PORTAL_PROXY_SECRET', ''),

    'sync_secret' => (string) env('EMPLOYEE_SITE_SYNC_SECRET', ''),

    /** Cloud Run Identity Token の audience（不動産アプリのサービス URL） */
    'audience' => (string) env('EMPLOYEE_PORTAL_AUDIENCE', env('APP_URL', '')),

    'sso_enabled' => filter_var(env('EMPLOYEE_PORTAL_SSO_ENABLED', false), FILTER_VALIDATE_BOOL),

    /** ローカルメール＋パスワードログイン（無効。社員ポータル SSO のみ） */
    'local_login_fallback_enabled' => filter_var(
        env('LOCAL_LOGIN_FALLBACK_ENABLED', false),
        FILTER_VALIDATE_BOOL,
    ),

    /** 社員ポータルのログイン／ハブ URL（「社員ポータルからログイン」ボタン先） */
    'login_url' => (string) env('EMPLOYEE_PORTAL_LOGIN_URL', ''),

    'default_department' => (string) env('EMPLOYEE_PORTAL_DEFAULT_DEPARTMENT', '不動産'),

    'default_status' => (string) env('EMPLOYEE_PORTAL_DEFAULT_STATUS', '在籍'),

    /** SSO で自動作成するときのデフォルトロール */
    'default_role' => (string) env('EMPLOYEE_PORTAL_DEFAULT_ROLE', 'viewer'),

    /** handoff 交換コードの有効秒数 */
    'handoff_ttl_seconds' => (int) env('EMPLOYEE_PORTAL_HANDOFF_TTL', 120),

    'http_timeout' => (int) env('EMPLOYEE_PORTAL_HTTP_TIMEOUT', 15),

];
