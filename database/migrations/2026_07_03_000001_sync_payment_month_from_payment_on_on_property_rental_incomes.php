<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('property_rental_incomes')
            ->whereNotNull('payment_on')
            ->orderBy('id')
            ->get()
            ->each(function ($record) {
                $paymentOn = date_create($record->payment_on);
                if ($paymentOn === false) {
                    return;
                }

                DB::table('property_rental_incomes')
                    ->where('id', $record->id)
                    ->update([
                        'payment_month' => (int) $paymentOn->format('Ym'),
                    ]);
            });
    }

    public function down(): void
    {
        // 以前の payment_month 値は復元できないため no-op
    }
};
