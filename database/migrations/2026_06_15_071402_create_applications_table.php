<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->text('staff_in_charge')->nullable()->comment('担当者');
            $table->text('property_name_room')->nullable()->comment('物件名＋部屋番号');
            $table->date('scheduled_move_in_date')->nullable()->comment('入居予定日');
            $table->integer('advertising_fee')->nullable()->comment('広告料');
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

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
