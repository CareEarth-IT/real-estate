<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->date('payment_on')->nullable()->after('payment_month')->comment('支払日');
            $table->date('transfer_on')->nullable()->after('payment_on')->comment('振込日');
        });

        DB::table('property_rental_incomes')->orderBy('id')->get()->each(function ($record) {
            $updates = [];

            if ($record->payment_month) {
                $year = intdiv((int) $record->payment_month, 100);
                $month = (int) $record->payment_month % 100;
                if ($month >= 1 && $month <= 12) {
                    $updates['payment_on'] = sprintf('%04d-%02d-01', $year, $month);
                }
            }

            if ($record->transfer_year_month) {
                $year = intdiv((int) $record->transfer_year_month, 100);
                $month = (int) $record->transfer_year_month % 100;
                if ($month >= 1 && $month <= 12) {
                    $updates['transfer_on'] = sprintf('%04d-%02d-01', $year, $month);
                }
            }

            if ($updates !== []) {
                DB::table('property_rental_incomes')->where('id', $record->id)->update($updates);
            }
        });

        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->dropColumn('transfer_year_month');
        });
    }

    public function down(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->unsignedInteger('transfer_year_month')->nullable()->after('payment_month')->comment('振込日 (yyyy/mm)');
        });

        Schema::table('property_rental_incomes', function (Blueprint $table) {
            $table->dropColumn(['payment_on', 'transfer_on']);
        });
    }
};
