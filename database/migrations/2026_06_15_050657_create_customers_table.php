<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('case_number');
            $table->text('name')->nullable();
            $table->date('move_in_date')->nullable()->comment('入居日/保険加入日');
            $table->date('contract_period')->nullable()->comment('契約期間');
            $table->boolean('contract_period_type')->nullable()->comment('種類（契約期間）');
            $table->text('property_name')->nullable();
            $table->text('room_number')->nullable();
            $table->text('address')->nullable();
            $table->text('management_company')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('is_married')->nullable()->comment('既婚/未婚');
            $table->text('mobile_number')->nullable();
            $table->text('email')->nullable();
            $table->text('occupation')->nullable();
            $table->text('company_or_school_name')->nullable();
            $table->text('company_or_school_phone')->nullable();
            $table->text('company_or_school_address')->nullable();
            $table->text('emergency_contact_name')->nullable();
            $table->text('emergency_contact_relationship')->nullable();
            $table->date('emergency_contact_date_of_birth')->nullable();
            $table->text('emergency_contact_address')->nullable();
            $table->text('emergency_contact_mobile')->nullable();
            $table->text('emergency_contact_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
