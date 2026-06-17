<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->text('staff_in_charge')->nullable()->comment('担当者');
            $table->text('property_name_room')->nullable()->comment('物件名＋部屋番号');
            $table->text('application_method')->nullable()->comment('申込方法');
            $table->boolean('flow_management_transition')->default(false)->comment('フロー管理移行チェック');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_completions');
    }
};
