<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('settlement_managements');
        Schema::dropIfExists('flow_managements');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('screening_completions');

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Intentionally empty: rental management tables are removed permanently.
    }
};
