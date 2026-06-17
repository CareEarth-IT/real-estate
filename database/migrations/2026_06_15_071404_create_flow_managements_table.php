<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flow_managements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('screening_completion_id')->nullable()->constrained('screening_completions')->nullOnDelete();
            $table->text('staff_in_charge')->nullable()->comment('担当者');
            $table->text('property_name_room')->nullable()->comment('物件名＋部屋番号');
            $table->text('application_method')->nullable()->comment('申込方法');
            $table->text('memo')->nullable()->comment('MEMO');
            $table->date('move_in_date')->nullable()->comment('入居日');
            $table->date('document_deadline')->nullable()->comment('書類期日');
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
            $table->boolean('settlement_transition')->default(false)->comment('決済金管理に移行');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flow_managements');
    }
};
