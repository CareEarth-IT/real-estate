<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('property_rental_income_terminations', function (Blueprint $table): void {
            $table->date('terminated_on')->nullable()->after('move_out_cost')->comment('解約日');
        });
    }

    public function down(): void
    {
        Schema::table('property_rental_income_terminations', function (Blueprint $table): void {
            $table->dropColumn('terminated_on');
        });
    }
};
