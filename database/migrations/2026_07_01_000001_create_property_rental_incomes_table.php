<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_rental_incomes', function (Blueprint $table) {
            $table->id();
            $table->date('created_on')->nullable()->comment('作成日');
            $table->text('contractor')->nullable()->comment('契約者');
            $table->text('property_name')->nullable()->comment('物件');
            $table->unsignedInteger('rent_year_month')->nullable()->comment('家賃年月 (yyyy/mm)');
            $table->string('payment_method')->nullable()->comment('入金方法');
            $table->integer('rent_amount')->nullable()->comment('家賃/(変動費)');
            $table->string('payment_status')->nullable()->comment('入金状況');
            $table->unsignedTinyInteger('occupant_count')->nullable()->comment('入居者人数');
            $table->integer('deposit_amount')->nullable()->comment('預り金');
            $table->unsignedInteger('payment_month')->nullable()->comment('支払い月 (yyyy/mm)');
            $table->unsignedInteger('transfer_year_month')->nullable()->comment('振込日 (yyyy/mm)');
            $table->string('transfer_payment_method')->nullable()->comment('入金方法（振込）');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_rental_incomes');
    }
};
