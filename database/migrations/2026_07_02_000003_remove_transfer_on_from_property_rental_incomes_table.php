<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->dropColumn('transfer_on');
        });
    }

    public function down(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->date('transfer_on')->nullable()->after('payment_on')->comment('振込日');
        });
    }
};
