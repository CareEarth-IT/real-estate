<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('flow_managements', 'application_id')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->foreignId('application_id')->nullable()->after('customer_id')->constrained('applications')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('flow_managements', 'flow_management_transition')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->boolean('flow_management_transition')->default(false)->after('application_id')->comment('フロー管理移行チェック');
            });
        }

        if (Schema::hasTable('screening_completions') && Schema::hasColumn('flow_managements', 'screening_completion_id')) {
            DB::statement('
                UPDATE flow_managements fm
                INNER JOIN screening_completions sc ON fm.screening_completion_id = sc.id
                SET fm.application_id = sc.application_id,
                    fm.flow_management_transition = sc.flow_management_transition
            ');
        }

        if (Schema::hasColumn('flow_managements', 'screening_completion_id')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->dropForeign(['screening_completion_id']);
                $table->dropColumn('screening_completion_id');
            });
        }

        Schema::dropIfExists('screening_completions');
    }

    public function down(): void
    {
        Schema::create('screening_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->text('staff_in_charge')->nullable();
            $table->text('property_name_room')->nullable();
            $table->text('application_method')->nullable();
            $table->boolean('flow_management_transition')->default(false);
            $table->timestamps();
        });

        Schema::table('flow_managements', function (Blueprint $table) {
            $table->foreignId('screening_completion_id')->nullable()->after('customer_id')->constrained('screening_completions')->nullOnDelete();
        });

        Schema::table('flow_managements', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropColumn(['application_id', 'flow_management_transition']);
        });
    }
};
