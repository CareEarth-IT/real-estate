<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_managements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('flow_management_id')->nullable()->constrained('flow_managements')->nullOnDelete();
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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_managements');
    }
};
