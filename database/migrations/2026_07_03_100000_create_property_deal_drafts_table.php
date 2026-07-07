<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_deal_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('case_number', 32)->unique()->comment('案件番号');
            $table->string('status', 32)->default('for_sale')->comment('状況');
            $table->string('location', 255)->nullable()->comment('所在地');
            $table->string('property_type', 32)->nullable()->comment('種別');
            $table->string('usage', 255)->nullable()->comment('用途');
            $table->string('nationality', 64)->nullable()->comment('国籍');
            $table->integer('property_price')->nullable()->comment('物件価格');
            $table->integer('registration_license_tax')->nullable();
            $table->integer('judicial_scrivener_fee')->nullable();
            $table->integer('postage')->nullable();
            $table->integer('pre_registration_info_fee')->nullable();
            $table->integer('post_registration_certificate_fee')->nullable();
            $table->integer('withholding_income_tax')->nullable();
            $table->integer('purchase_brokerage_fee')->nullable();
            $table->integer('property_tax_r7')->nullable();
            $table->integer('property_tax_r8')->nullable();
            $table->integer('building_consumption_tax')->nullable();
            $table->integer('real_estate_acquisition_tax')->nullable();
            $table->integer('renovation_cost')->nullable();
            $table->integer('contingency_fund')->nullable();
            $table->integer('total_cost')->nullable();
            $table->integer('expected_selling_price')->nullable();
            $table->decimal('cost_ratio', 5, 1)->nullable();
            $table->decimal('gross_profit_margin', 5, 1)->nullable();
            $table->integer('kenbiya_ad_fee')->nullable();
            $table->integer('sale_brokerage_fee')->nullable();
            $table->integer('contract_stamp_duty')->nullable();
            $table->integer('receipt_stamp_duty')->nullable();
            $table->integer('total_selling_admin_expenses')->nullable();
            $table->decimal('estimated_operating_profit_margin', 5, 1)->nullable();
            $table->integer('expected_rent')->nullable();
            $table->decimal('expected_surface_yield', 5, 1)->nullable();
            $table->decimal('estimated_ownership_yield', 5, 1)->nullable();
            $table->timestamps();
        });

        DB::table('property_deal_drafts')->insert([
            'case_number' => 'K0001',
            'status' => 'for_sale',
            'location' => '堺市西区',
            'property_type' => 'detached',
            'usage' => '学生シェアH',
            'nationality' => 'ミャンマー',
            'property_price' => 2800000,
            'registration_license_tax' => 73600,
            'judicial_scrivener_fee' => 38500,
            'postage' => 2000,
            'pre_registration_info_fee' => 2400,
            'post_registration_certificate_fee' => 3310,
            'withholding_income_tax' => -2552,
            'purchase_brokerage_fee' => 330000,
            'property_tax_r7' => 6863,
            'property_tax_r8' => null,
            'building_consumption_tax' => null,
            'real_estate_acquisition_tax' => 77700,
            'renovation_cost' => 6500000,
            'contingency_fund' => null,
            'total_cost' => 9831821,
            'expected_selling_price' => 15000000,
            'cost_ratio' => 65.5,
            'gross_profit_margin' => 34.5,
            'kenbiya_ad_fee' => 5000,
            'sale_brokerage_fee' => 475000,
            'contract_stamp_duty' => null,
            'receipt_stamp_duty' => null,
            'total_selling_admin_expenses' => 480000,
            'estimated_operating_profit_margin' => 31.3,
            'expected_rent' => 150000,
            'expected_surface_yield' => 12.0,
            'estimated_ownership_yield' => 18.3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('property_deal_drafts');
    }
};
