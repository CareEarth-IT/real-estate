<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        Schema::table('rental_property_archives', function (Blueprint $table) {
            $columns = [
                'monthly_rent_major' => fn (Blueprint $t) => $t->unsignedInteger('monthly_rent_major')->nullable()->comment('月額賃料（万円・整数部）'),
                'monthly_rent_minor' => fn (Blueprint $t) => $t->unsignedTinyInteger('monthly_rent_minor')->nullable()->comment('月額賃料（万円・小数部）'),
                'has_management_fee' => fn (Blueprint $t) => $t->boolean('has_management_fee')->default(false)->comment('管理費あり'),
                'has_key_money' => fn (Blueprint $t) => $t->boolean('has_key_money')->default(false)->comment('礼金あり'),
                'has_security_deposit' => fn (Blueprint $t) => $t->boolean('has_security_deposit')->default(false)->comment('敷金あり'),
                'has_security_deposit_extra' => fn (Blueprint $t) => $t->boolean('has_security_deposit_extra')->default(false)->comment('敷金積増あり'),
                'has_amortization' => fn (Blueprint $t) => $t->boolean('has_amortization')->default(false)->comment('償却金あり'),
                'has_shikibiki' => fn (Blueprint $t) => $t->boolean('has_shikibiki')->default(false)->comment('敷引あり'),
                'has_guarantee_deposit' => fn (Blueprint $t) => $t->boolean('has_guarantee_deposit')->default(false)->comment('保証金あり'),
                'has_initial_cost' => fn (Blueprint $t) => $t->boolean('has_initial_cost')->default(false)->comment('初期費用あり'),
                'has_other_fees' => fn (Blueprint $t) => $t->boolean('has_other_fees')->default(false)->comment('その他諸費用あり'),
                'brokerage_fee' => fn (Blueprint $t) => $t->string('brokerage_fee', 20)->nullable()->comment('仲介手数料'),
                'collateral_required' => fn (Blueprint $t) => $t->boolean('collateral_required')->default(false)->comment('担保要'),
                'collateral_amount_major' => fn (Blueprint $t) => $t->unsignedInteger('collateral_amount_major')->nullable()->comment('担保金額（万円・整数部）'),
                'collateral_amount_minor' => fn (Blueprint $t) => $t->unsignedTinyInteger('collateral_amount_minor')->nullable()->comment('担保金額（万円・小数部）'),
                'collateral_years' => fn (Blueprint $t) => $t->unsignedSmallInteger('collateral_years')->nullable()->comment('担保年数'),
                'contract_lease_type' => fn (Blueprint $t) => $t->string('contract_lease_type', 20)->nullable()->comment('普通借家/定期借家'),
                'contract_period_type' => fn (Blueprint $t) => $t->string('contract_period_type', 20)->nullable()->comment('指定なし/期間'),
                'contract_years' => fn (Blueprint $t) => $t->unsignedSmallInteger('contract_years')->nullable()->comment('契約年数'),
                'contract_months' => fn (Blueprint $t) => $t->unsignedTinyInteger('contract_months')->nullable()->comment('契約月数'),
                'has_guarantor_company' => fn (Blueprint $t) => $t->boolean('has_guarantor_company')->default(false)->comment('保証会社あり'),
                'has_parking' => fn (Blueprint $t) => $t->boolean('has_parking')->default(false)->comment('駐車場あり'),
                'has_tokuyu_chin' => fn (Blueprint $t) => $t->boolean('has_tokuyu_chin')->default(false)->comment('特優賃あり'),
            ];

            foreach ($columns as $name => $definition) {
                if (! Schema::hasColumn('rental_property_archives', $name)) {
                    $definition($table);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        $columns = [
            'monthly_rent_major',
            'monthly_rent_minor',
            'has_management_fee',
            'has_key_money',
            'has_security_deposit',
            'has_security_deposit_extra',
            'has_amortization',
            'has_shikibiki',
            'has_guarantee_deposit',
            'has_initial_cost',
            'has_other_fees',
            'brokerage_fee',
            'collateral_required',
            'collateral_amount_major',
            'collateral_amount_minor',
            'collateral_years',
            'contract_lease_type',
            'contract_period_type',
            'contract_years',
            'contract_months',
            'has_guarantor_company',
            'has_parking',
            'has_tokuyu_chin',
        ];

        Schema::table('rental_property_archives', function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn('rental_property_archives', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
