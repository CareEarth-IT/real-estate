<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_rental_income_terminations', function (Blueprint $table): void {
            $table->id();
            $table->string('contract_key', 64)->unique()->comment('契約キー');
            $table->text('contractor')->nullable()->comment('契約者');
            $table->text('property_name')->nullable()->comment('物件');
            $table->string('move_out_type')->comment('退去区分');
            $table->text('move_out_reason')->nullable()->comment('退去理由');
            $table->unsignedInteger('move_out_cost')->nullable()->comment('退去費');
            $table->timestamp('terminated_at')->comment('解約日時');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_rental_income_terminations');
    }
};
