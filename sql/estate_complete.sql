-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- ホスト: 127.0.0.1
-- 生成日時: 2026-06-26 05:35:36
-- サーバのバージョン： 10.4.32-MariaDB
-- PHP のバージョン: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `estate`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `staff_in_charge` text DEFAULT NULL COMMENT '担当者',
  `property_name_room` text DEFAULT NULL COMMENT '物件名＋部屋番号',
  `scheduled_move_in_date` date DEFAULT NULL COMMENT '入居予定日',
  `advertising_fee` int(11) DEFAULT NULL COMMENT '広告料',
  `has_broker_fee` tinyint(1) NOT NULL DEFAULT 0 COMMENT '仲介手数料 あり/なし',
  `broker_fee` int(11) DEFAULT NULL COMMENT '仲介手数料（金額）',
  `management_company_name` text DEFAULT NULL COMMENT '管理会社名',
  `application_method` text DEFAULT NULL COMMENT '申込方法',
  `status` text DEFAULT NULL COMMENT '状況',
  `memo` text DEFAULT NULL COMMENT 'MEMO',
  `property_documents_url` varchar(2048) DEFAULT NULL COMMENT '物件資料',
  `appliance_support_notes` text DEFAULT NULL COMMENT '家電サポート・CB等',
  `sales_action_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT '営業要対応',
  `screening_ok` tinyint(1) NOT NULL DEFAULT 0 COMMENT '審査ＯＫ',
  `is_cancelled` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'キャンセル',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `case_number` int(10) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `move_in_date` date NOT NULL COMMENT '入居日/保険加入日',
  `contract_period` varchar(50) NOT NULL,
  `contract_period_type` tinyint(1) NOT NULL COMMENT '種類（契約期間）',
  `property_name` text NOT NULL,
  `room_number` text NOT NULL,
  `address` text NOT NULL,
  `management_company` text NOT NULL,
  `date_of_birth` date NOT NULL,
  `is_married` tinyint(1) NOT NULL COMMENT '既婚/未婚',
  `mobile_number` text NOT NULL,
  `email` text NOT NULL,
  `occupation` text NOT NULL,
  `company_or_school_name` text NOT NULL,
  `company_or_school_phone` text NOT NULL,
  `company_or_school_address` text NOT NULL,
  `emergency_contact_name` text NOT NULL,
  `emergency_contact_relationship` text NOT NULL,
  `emergency_contact_date_of_birth` date NOT NULL,
  `emergency_contact_address` text NOT NULL,
  `emergency_contact_mobile` text NOT NULL,
  `emergency_contact_email` text NOT NULL,
  `customer_info_completed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '顧客情報入力済み',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `flow_managements`
--

CREATE TABLE `flow_managements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `flow_management_transition` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'フロー管理移行チェック',
  `staff_in_charge` text DEFAULT NULL COMMENT '担当者',
  `property_name_room` text DEFAULT NULL COMMENT '物件名＋部屋番号',
  `application_method` text DEFAULT NULL COMMENT '申込方法',
  `memo` text DEFAULT NULL COMMENT 'MEMO',
  `move_in_date` date DEFAULT NULL COMMENT '入居日',
  `document_deadline` text DEFAULT NULL COMMENT '書類期日',
  `scheduled_visit_date` date DEFAULT NULL COMMENT '来社予定日',
  `key_handover_date` date DEFAULT NULL COMMENT '鍵渡日',
  `documents_completed` tinyint(1) NOT NULL DEFAULT 0 COMMENT '書類完成',
  `documents_returned` tinyint(1) NOT NULL DEFAULT 0 COMMENT '書類返送',
  `resident_record_photo_request` tinyint(1) NOT NULL DEFAULT 0 COMMENT '住民票 顔写真依頼',
  `resident_record_photo_cancel` tinyint(1) NOT NULL DEFAULT 0 COMMENT '住民票 顔写真取消',
  `certified_copy_acquisition` tinyint(1) NOT NULL DEFAULT 0 COMMENT '謄本取得',
  `important_matters_explanation_creation` tinyint(1) NOT NULL DEFAULT 0 COMMENT '重説作成',
  `documents_arrived` tinyint(1) NOT NULL DEFAULT 0 COMMENT '書類到着',
  `ad_fee_invoice_creation` text DEFAULT NULL COMMENT '広告料請求書作成',
  `transfer_request_to_applicant` tinyint(1) NOT NULL DEFAULT 0 COMMENT '本人へ振込依頼',
  `transfer_receipt_from_applicant` tinyint(1) NOT NULL DEFAULT 0 COMMENT '本人より振込・受取',
  `payment_request_creation` tinyint(1) NOT NULL DEFAULT 0 COMMENT '支払依頼書作成',
  `accounting_transfer_request` tinyint(1) NOT NULL DEFAULT 0 COMMENT '経理部へ振込依頼',
  `slack_sales_notification` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'スラックで売上連絡',
  `lifeline` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'ライフライン',
  `key_received` tinyint(1) NOT NULL DEFAULT 0 COMMENT '鍵受取',
  `key_to_applicant` tinyint(1) NOT NULL DEFAULT 0 COMMENT '鍵本人へ',
  `original_copy_to_applicant` tinyint(1) NOT NULL DEFAULT 0 COMMENT '原本コピー本人配布',
  `key_receipt_return` tinyint(1) NOT NULL DEFAULT 0 COMMENT '鍵受領書など返送',
  `contract_copy_storage` tinyint(1) NOT NULL DEFAULT 0 COMMENT '契約書コピー/保管',
  `settlement_transition` tinyint(1) NOT NULL DEFAULT 0 COMMENT '決済金管理に移行',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `settlement_managements`
--

CREATE TABLE `settlement_managements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `flow_management_id` bigint(20) UNSIGNED DEFAULT NULL,
  `fee_type` varchar(20) DEFAULT NULL COMMENT '手数料種別（advertising / broker_fee）',
  `management_number` text DEFAULT NULL COMMENT '管理番号',
  `staff_in_charge` text DEFAULT NULL COMMENT '担当者',
  `property_name` text DEFAULT NULL COMMENT '物件名',
  `contract_date` date DEFAULT NULL COMMENT '契約日',
  `estimated_sales` int(11) DEFAULT NULL COMMENT '想定売上',
  `settlement_transfer_request` tinyint(1) NOT NULL DEFAULT 0 COMMENT '決済金振込依頼',
  `settlement_transfer_date` date DEFAULT NULL COMMENT '決済金振込日',
  `sales_including_tax` int(11) DEFAULT NULL COMMENT '税込売上',
  `sales_excluding_tax` int(11) DEFAULT NULL COMMENT '税抜売上',
  `earned_points` text DEFAULT NULL COMMENT '発生ポイント',
  `ad_transfer_invoice_creation` tinyint(1) NOT NULL DEFAULT 0 COMMENT '【AD振込】請求書作成',
  `offset_statement_printing` tinyint(1) NOT NULL DEFAULT 0 COMMENT '【相殺】明細書印刷',
  `individual_invoice_printing` tinyint(1) NOT NULL DEFAULT 0 COMMENT '【個人】請求書印刷',
  `remarks` text DEFAULT NULL COMMENT '備考',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- ここから下は `careearth_home.txt` から追加されたテーブル
-- --------------------------------------------------------

--
-- テーブルの構造 `property_addresses`
--
CREATE TABLE `property_addresses` (
  `id` int(10) UNSIGNED NOT NULL,
  `address` varchar(500) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルの構造 `property_master`
--
CREATE TABLE `property_master` (
  `id` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `buyer_name` varchar(255) NOT NULL COMMENT '雉ｼ蜈･閠?',
  `broker_name` varchar(255) DEFAULT NULL COMMENT '莉ｲ莉区･ｭ閠?錐',
  `owner_name` varchar(255) DEFAULT NULL COMMENT '繧ｪ繝ｼ繝翫?蜷?',
  `property_address` varchar(500) NOT NULL COMMENT '迚ｩ莉ｶ菴乗園',
  `building_price` int(10) UNSIGNED DEFAULT 0 COMMENT '蟒ｺ迚ｩ蜿門ｾ嶺ｾ｡譬ｼ',
  `land_price` int(10) UNSIGNED DEFAULT 0 COMMENT '蝨溷慍蜿門ｾ嶺ｾ｡譬ｼ',
  `price_mode` varchar(10) NOT NULL DEFAULT 'split' COMMENT 'split|total',
  `total_price` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '物件価格(合算)',
  `registration_fee` int(10) UNSIGNED DEFAULT 0 COMMENT '逋ｻ險倩ｲｻ逕ｨ',
  `brokerage_fee` int(10) UNSIGNED DEFAULT 0 COMMENT '莉ｲ莉区焔謨ｰ譁?',
  `property_tax` int(10) UNSIGNED DEFAULT 0 COMMENT '蝗ｺ螳夊ｳ?肇遞?',
  `sales_person` varchar(255) DEFAULT NULL COMMENT '諡?ｽ灘霧讌ｭ',
  `purchase_certificate` varchar(500) DEFAULT NULL COMMENT '雋ｷ莉倩ｨｼ譏取嶌',
  `seal_certificate` varchar(500) DEFAULT NULL COMMENT '蜊ｰ髑題ｨｼ譏取嶌',
  `registry_certificate` varchar(500) DEFAULT NULL COMMENT '逋ｻ險倅ｺ矩??ｨｼ譏取嶌',
  `property_registry` varchar(500) DEFAULT NULL COMMENT '荳榊虚逕｣逋ｻ險倩ｬ?悽',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルの構造 `sales_persons`
--
CREATE TABLE `sales_persons` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applications_customer_id_foreign` (`customer_id`);

--
-- テーブルのインデックス `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- テーブルのインデックス `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- テーブルのインデックス `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_case_number_unique` (`case_number`);

--
-- テーブルのインデックス `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- テーブルのインデックス `flow_managements`
--
ALTER TABLE `flow_managements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `flow_managements_customer_id_foreign` (`customer_id`),
  ADD KEY `flow_managements_application_id_foreign` (`application_id`);

--
-- テーブルのインデックス `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- テーブルのインデックス `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- テーブルのインデックス `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- テーブルのインデックス `settlement_managements`
--
ALTER TABLE `settlement_managements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settlement_managements_flow_management_id_fee_type_unique` (`flow_management_id`,`fee_type`),
  ADD KEY `settlement_managements_customer_id_foreign` (`customer_id`);

--
-- テーブルのインデックス `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_google_id_unique` (`google_id`);

--
-- テーブルのインデックス `property_addresses`
--
ALTER TABLE `property_addresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `address` (`address`);

--
-- テーブルのインデックス `property_master`
--
ALTER TABLE `property_master`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_property_address` (`property_address`(100));

--
-- テーブルのインデックス `sales_persons`
--
ALTER TABLE `sales_persons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);


--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- テーブルの AUTO_INCREMENT `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- テーブルの AUTO_INCREMENT `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `flow_managements`
--
ALTER TABLE `flow_managements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- テーブルの AUTO_INCREMENT `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- テーブルの AUTO_INCREMENT `settlement_managements`
--
ALTER TABLE `settlement_managements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- テーブルの AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
  
--
-- テーブルの AUTO_INCREMENT `property_addresses`
--
ALTER TABLE `property_addresses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- テーブルの AUTO_INCREMENT `property_master`
--
ALTER TABLE `property_master`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- テーブルの AUTO_INCREMENT `sales_persons`
--
ALTER TABLE `sales_persons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- テーブルの制約 `flow_managements`
--
ALTER TABLE `flow_managements`
  ADD CONSTRAINT `flow_managements_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `flow_managements_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;

--
-- テーブルの制約 `settlement_managements`
--
ALTER TABLE `settlement_managements`
  ADD CONSTRAINT `settlement_managements_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `settlement_managements_flow_management_id_foreign` FOREIGN KEY (`flow_management_id`) REFERENCES `flow_managements` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
