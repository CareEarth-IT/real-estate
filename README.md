# CareEarthHome — 統合アプリケーション

`real-estate`（賃貸申込・管理）と物件マスター機能を統合した Laravel 12 アプリです。  
共通データベース: **`estate`**

## 機能一覧

### 物件マスター（CareEarthHome 認証）
| URL | 説明 |
|-----|------|
| `/login` | 物件マスター用ログイン |
| `/` | マスターデータ一覧 |
| `/reference` | 参照一覧 |
| `/properties/create` | データ登録 |
| `/users` | ユーザー管理（不動産/経理ロール） |

### 賃貸申込（公開フォーム）
| URL | 説明 |
|-----|------|
| `/rental/customers/create` | 入居者情報入力 |
| `/rental/customers/{id}/applications/create` | 申込情報入力 |

### 賃貸管理（Google OAuth）
| URL | 説明 |
|-----|------|
| `/admin/login` | 管理画面ログイン |
| `/admin/applications` | 申込一覧 |
| `/admin/screening-completions` | 審査完了一覧 |
| `/admin/flow-managements` | フロー管理 |
| `/admin/settlement-managements` | 決済金管理 |

## データベース

| テーブル | 用途 |
|----------|------|
| `users` | 賃貸管理（Google OAuth） |
| `careearth_users` | 物件マスター認証・ロール |
| `customers`, `applications`, `flow_managements` 等 | 賃貸ワークフロー |
| `property_master`, `property_addresses`, `sales_persons` | 物件マスター |

## セットアップ

```bash
cd C:\xampp\htdocs\CareEarthHome
php C:\xampp\php\composer.phar install
copy .env.example .env
php artisan key:generate
php artisan migrate
```

`.env` 設定:
```
DB_DATABASE=estate
APP_URL=http://localhost/CareEarthHome
GOOGLE_CLIENT_ID=（Google Cloud Console）
GOOGLE_CLIENT_SECRET=（Google Cloud Console）
GOOGLE_REDIRECT_URI="${APP_URL}/admin/auth/google/callback"
```

## 認証

**物件マスター:** `careearth_users` テーブル（メール+パスワード）  
初期: `tomoya_hayashi@careearth.info` / `CareEarth2024!`（経理ロール）

**賃貸管理:** Google OAuth + `config/admin.php` の許可メール一覧

ローカル開発（Google OAuth 未設定時）は管理画面ログインでメール＋パスワードも利用できます。

- URL: `/admin/login`
- メール: `tomoya_hayashi@careearth.info`（許可リストに登録済み）
- パスワード: `CareEarth2024!`（物件マスターと同じ）

## SQL 再インポート後

`estate_complete.sql` を phpMyAdmin で `estate` DB にインポートしたあと:

```bash
php artisan migrate
php artisan careearth:import-legacy --force
```

- `migrate` … `careearth_users`・`screening_completions` の不足列追加、賃貸テーブルの差分反映
- `careearth:import-legacy` … 旧 `careearth_home` DB から物件データを移行（`--force` で上書き）

物件データのみ SQL ファイルから入れる場合は `careearth_home.sql` の INSERT 部分を利用できます。

## 旧データ移行

```bash
php artisan careearth:import-legacy
```
