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
        Schema::dropIfExists('customers');

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('case_number')->unique();
            $table->text('name');
            $table->date('move_in_date')->comment('入居日/保険加入日');
            $table->date('contract_period')->comment('契約期間');
            $table->boolean('contract_period_type')->comment('種類（契約期間）');
            $table->text('property_name');
            $table->text('room_number');
            $table->text('address');
            $table->text('management_company');
            $table->date('date_of_birth');
            $table->boolean('is_married')->comment('既婚/未婚');
            $table->text('mobile_number');
            $table->text('email');
            $table->text('occupation');
            $table->text('company_or_school_name');
            $table->text('company_or_school_phone');
            $table->text('company_or_school_address');
            $table->text('emergency_contact_name');
            $table->text('emergency_contact_relationship');
            $table->date('emergency_contact_date_of_birth');
            $table->text('emergency_contact_address');
            $table->text('emergency_contact_mobile');
            $table->text('emergency_contact_email');
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
