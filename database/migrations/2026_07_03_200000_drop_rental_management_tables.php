<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rental tables are restored by 2026_07_07_100000_restore_rental_management_tables.
    }

    public function down(): void
    {
        // Intentionally empty: rental management tables are removed permanently.
    }
};
