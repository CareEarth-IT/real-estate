<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->dropColumn('transfer_payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->string('transfer_payment_method')->nullable()->comment('入金方法（振込）')->after('transfer_year_month');
        });
    }
};
