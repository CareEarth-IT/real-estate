<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('case_number')->unique();
                $table->text('name');
                $table->date('move_in_date')->comment('入居日/保険加入日');
                $table->string('contract_period', 50);
                $table->boolean('contract_period_type')->comment('種類（契約期間）');
                $table->text('property_name');
                $table->text('room_number');
                $table->text('address');
                $table->text('management_company');
                $table->date('date_of_birth');
                $table->boolean('is_married')->comment('既婚/未婚');
                $table->text('mobile_number');
                $table->text('email');
                $table->text('occupation');
                $table->text('company_or_school_name');
                $table->text('company_or_school_phone');
                $table->text('company_or_school_address');
                $table->text('emergency_contact_name');
                $table->text('emergency_contact_relationship');
                $table->date('emergency_contact_date_of_birth');
                $table->text('emergency_contact_address');
                $table->text('emergency_contact_mobile');
                $table->text('emergency_contact_email');
                $table->boolean('customer_info_completed')->default(false)->comment('顧客情報入力済み');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('applications')) {
            Schema::create('applications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->text('staff_in_charge')->nullable()->comment('担当者');
                $table->text('property_name')->nullable()->comment('物件名');
                $table->text('room_number')->nullable()->comment('部屋番号');
                $table->date('scheduled_move_in_date')->nullable()->comment('入居予定日');
                $table->integer('advertising_fee')->nullable()->comment('広告料');
                $table->boolean('has_broker_fee')->nullable()->default(false)->comment('仲介手数料 あり/なし');
                $table->integer('broker_fee')->nullable()->comment('仲介手数料（金額）');
                $table->text('management_company_name')->nullable()->comment('管理会社名');
                $table->text('application_method')->nullable()->comment('申込方法');
                $table->text('status')->nullable()->comment('状況');
                $table->text('memo')->nullable()->comment('MEMO');
                $table->string('property_documents_url', 2048)->nullable()->comment('物件資料');
                $table->text('appliance_support_notes')->nullable()->comment('家電サポート・CB等');
                $table->boolean('sales_action_required')->default(false)->comment('営業要対応');
                $table->boolean('screening_ok')->default(false)->comment('審査ＯＫ');
                $table->boolean('is_cancelled')->default(false)->comment('キャンセル');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('flow_managements')) {
            Schema::create('flow_managements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
                $table->boolean('flow_management_transition')->default(false)->comment('フロー管理移行チェック');
                $table->text('staff_in_charge')->nullable()->comment('担当者');
                $table->text('property_name')->nullable()->comment('物件名');
                $table->text('room_number')->nullable()->comment('部屋番号');
                $table->text('application_method')->nullable()->comment('申込方法');
                $table->text('memo')->nullable()->comment('MEMO');
                $table->date('move_in_date')->nullable()->comment('入居日');
                $table->text('document_deadline')->nullable()->comment('書類期日');
                $table->date('scheduled_visit_date')->nullable()->comment('来社予定日');
                $table->date('key_handover_date')->nullable()->comment('鍵渡日');
                $table->boolean('documents_completed')->default(false)->comment('書類完成');
                $table->boolean('documents_returned')->default(false)->comment('書類返送');
                $table->boolean('resident_record_photo_request')->default(false)->comment('住民票 顔写真依頼');
                $table->boolean('resident_record_photo_cancel')->default(false)->comment('住民票 顔写真取消');
                $table->boolean('certified_copy_acquisition')->default(false)->comment('謄本取得');
                $table->boolean('important_matters_explanation_creation')->default(false)->comment('重説作成');
                $table->boolean('documents_arrived')->default(false)->comment('書類到着');
                $table->text('ad_fee_invoice_creation')->nullable()->comment('広告料請求書作成');
                $table->boolean('transfer_request_to_applicant')->default(false)->comment('本人へ振込依頼');
                $table->boolean('transfer_receipt_from_applicant')->default(false)->comment('本人より振込・受取');
                $table->boolean('payment_request_creation')->default(false)->comment('支払依頼書作成');
                $table->boolean('accounting_transfer_request')->default(false)->comment('経理部へ振込依頼');
                $table->boolean('slack_sales_notification')->default(false)->comment('スラックで売上連絡');
                $table->boolean('lifeline')->default(false)->comment('ライフライン');
                $table->boolean('key_received')->default(false)->comment('鍵受取');
                $table->boolean('key_to_applicant')->default(false)->comment('鍵本人へ');
                $table->boolean('original_copy_to_applicant')->default(false)->comment('原本コピー本人配布');
                $table->boolean('key_receipt_return')->default(false)->comment('鍵受領書など返送');
                $table->boolean('contract_copy_storage')->default(false)->comment('契約書コピー/保管');
                $table->boolean('has_broker_fee')->default(false)->comment('仲介手数料あり');
                $table->boolean('settlement_transition')->default(false)->comment('決済金管理に移行');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('settlement_managements')) {
            Schema::create('settlement_managements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
                $table->foreignId('flow_management_id')->nullable()->constrained('flow_managements')->nullOnDelete();
                $table->string('fee_type', 20)->nullable()->comment('手数料種別（advertising / broker_fee）');
                $table->text('management_number')->nullable()->comment('管理番号');
                $table->text('staff_in_charge')->nullable()->comment('担当者');
                $table->text('property_name')->nullable()->comment('物件名');
                $table->date('contract_date')->nullable()->comment('契約日');
                $table->integer('estimated_sales')->nullable()->comment('想定売上');
                $table->boolean('settlement_transfer_request')->default(false)->comment('決済金振込依頼');
                $table->date('settlement_transfer_date')->nullable()->comment('決済金振込日');
                $table->integer('sales_including_tax')->nullable()->comment('税込売上');
                $table->integer('sales_excluding_tax')->nullable()->comment('税抜売上');
                $table->text('earned_points')->nullable()->comment('発生ポイント');
                $table->boolean('ad_transfer_invoice_creation')->default(false)->comment('【AD振込】請求書作成');
                $table->boolean('offset_statement_printing')->default(false)->comment('【相殺】明細書印刷');
                $table->boolean('individual_invoice_printing')->default(false)->comment('【個人】請求書印刷');
                $table->text('remarks')->nullable()->comment('備考');
                $table->timestamps();

                $table->unique(
                    ['flow_management_id', 'fee_type'],
                    'settlement_managements_flow_management_id_fee_type_unique',
                );
            });
        }
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('settlement_managements');
        Schema::dropIfExists('flow_managements');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('customers');

        Schema::enableForeignKeyConstraints();
    }
};
