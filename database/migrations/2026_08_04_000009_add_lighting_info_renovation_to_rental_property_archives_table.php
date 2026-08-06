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
            if (! Schema::hasColumn('rental_property_archives', 'lighting')) {
                $table->json('lighting')->nullable()->comment('照明');
            }
            if (! Schema::hasColumn('rental_property_archives', 'information_equipment')) {
                $table->json('information_equipment')->nullable()->comment('情報設備・回線');
            }
            if (! Schema::hasColumn('rental_property_archives', 'renovation')) {
                $table->json('renovation')->nullable()->comment('リフォーム');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('rental_property_archives')) {
            return;
        }

        Schema::table('rental_property_archives', function (Blueprint $table) {
            foreach (['lighting', 'information_equipment', 'renovation'] as $column) {
                if (Schema::hasColumn('rental_property_archives', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
