<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('applications') && ! Schema::hasColumn('applications', 'contractor_furigana')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->text('contractor_furigana')->nullable()->after('contractor')->comment('契約者フリガナ');
                $table->text('contractor_english_name')->nullable()->after('contractor_furigana')->comment('契約者英名');
                $table->text('overseas_screening')->nullable()->after('contractor_english_name')->comment('海外審査');
            });
        }

        if (Schema::hasTable('flow_managements') && ! Schema::hasColumn('flow_managements', 'contractor_furigana')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->text('contractor_furigana')->nullable()->after('contractor')->comment('契約者フリガナ');
                $table->text('contractor_english_name')->nullable()->after('contractor_furigana')->comment('契約者英名');
                $table->text('overseas_screening')->nullable()->after('contractor_english_name')->comment('海外審査');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('flow_managements') && Schema::hasColumn('flow_managements', 'contractor_furigana')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->dropColumn(['contractor_furigana', 'contractor_english_name', 'overseas_screening']);
            });
        }

        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'contractor_furigana')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropColumn(['contractor_furigana', 'contractor_english_name', 'overseas_screening']);
            });
        }
    }
};
