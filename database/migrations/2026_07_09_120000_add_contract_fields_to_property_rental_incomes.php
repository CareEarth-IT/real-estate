<?php

use App\Support\PropertyRentalIncomeContract;
use App\Support\YearMonth;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table): void {
            $table->string('contract_key', 64)->nullable()->after('contractor')->index();
            $table->date('contract_start_on')->nullable()->after('contract_key');
            $table->date('contract_end_on')->nullable()->after('contract_start_on');
        });

        $groups = DB::table('property_rental_incomes')
            ->select('contractor', 'property_name')
            ->groupBy('contractor', 'property_name')
            ->get();

        foreach ($groups as $group) {
            $contractKey = PropertyRentalIncomeContract::key(
                $group->contractor,
                $group->property_name,
            );

            $months = DB::table('property_rental_incomes')
                ->where('contractor', $group->contractor)
                ->where('property_name', $group->property_name)
                ->whereNotNull('payment_month')
                ->pluck('payment_month')
                ->map(static fn ($month): int => (int) $month)
                ->filter(static fn (int $month): bool => YearMonth::isValid($month))
                ->sort()
                ->values();

            $contractStart = null;
            $contractEnd = null;

            if ($months->isNotEmpty()) {
                $contractStart = YearMonth::firstDay($months->first());
                $contractEnd = YearMonth::lastDay($months->last());
            }

            DB::table('property_rental_incomes')
                ->where('contractor', $group->contractor)
                ->where('property_name', $group->property_name)
                ->update([
                    'contract_key' => $contractKey,
                    'contract_start_on' => $contractStart,
                    'contract_end_on' => $contractEnd,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('property_rental_incomes', function (Blueprint $table): void {
            $table->dropColumn(['contract_key', 'contract_start_on', 'contract_end_on']);
        });
    }
};
