<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('applications') && ! Schema::hasColumn('applications', 'entry_method')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->string('entry_method', 50)->nullable()->after('application_method')->comment('記入方法');
            });
        }

        if (Schema::hasTable('flow_managements') && ! Schema::hasColumn('flow_managements', 'entry_method')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->string('entry_method', 50)->nullable()->after('application_method')->comment('記入方法');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('flow_managements') && Schema::hasColumn('flow_managements', 'entry_method')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->dropColumn('entry_method');
            });
        }

        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'entry_method')) {
            Schema::table('applications', function (Blueprint $table) {
                $table->dropColumn('entry_method');
            });
        }
    }
};
