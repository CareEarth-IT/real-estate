<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('property_rental_incomes')
            ->whereNull('payment_status')
            ->update(['payment_status' => 'unpaid']);

        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->string('payment_status')->default('unpaid')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->string('payment_status')->nullable()->default(null)->change();
        });
    }
};
