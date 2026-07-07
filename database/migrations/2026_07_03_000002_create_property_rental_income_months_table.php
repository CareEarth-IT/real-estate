<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_rental_income_months', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payment_month')->unique()->comment('支払い月 (yyyy/mm)');
            $table->timestamps();
        });

        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->foreignId('copied_from_id')
                ->nullable()
                ->after('payment_on')
                ->constrained('property_rental_incomes')
                ->nullOnDelete();
        });

        $months = array_merge(
            config('property-rental-income.payment_month_tabs', [202607, 202608, 202609]),
            DB::table('property_rental_incomes')
                ->whereNotNull('payment_month')
                ->distinct()
                ->pluck('payment_month')
                ->all(),
        );

        $now = now();
        foreach (array_unique(array_map('intval', $months)) as $month) {
            if ($month < 101) {
                continue;
            }

            DB::table('property_rental_income_months')->insertOrIgnore([
                'payment_month' => $month,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('copied_from_id');
        });

        Schema::dropIfExists('property_rental_income_months');
    }
};
