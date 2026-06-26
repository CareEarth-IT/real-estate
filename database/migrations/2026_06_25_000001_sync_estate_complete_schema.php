<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->syncApplications();
        $this->syncCustomers();
        $this->syncScreeningCompletions();
        $this->syncFlowManagements();
        $this->syncSettlementManagements();
        $this->ensureCareEarthUsers();
    }

    private function syncApplications(): void
    {
        if (! Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'has_broker_fee')) {
                $table->boolean('has_broker_fee')->default(false)->after('advertising_fee')->comment('仲介手数料 あり/なし');
            }
            if (! Schema::hasColumn('applications', 'broker_fee')) {
                $table->integer('broker_fee')->nullable()->after('has_broker_fee')->comment('仲介手数料（金額）');
            }
        });
    }

    private function syncCustomers(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'customer_info_completed')) {
                $table->boolean('customer_info_completed')->default(false)->comment('顧客情報入力済み');
            }
        });

        $columnType = DB::selectOne("
            SELECT DATA_TYPE AS type
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'customers'
              AND COLUMN_NAME = 'contract_period'
        ");

        if ($columnType && strtolower((string) $columnType->type) === 'date') {
            DB::statement('ALTER TABLE customers MODIFY contract_period VARCHAR(50) NOT NULL COMMENT \'契約期間\'');
        }
    }

    private function syncScreeningCompletions(): void
    {
        if (Schema::hasTable('screening_completions')) {
            return;
        }

        Schema::create('screening_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('application_id')->nullable()->constrained('applications')->nullOnDelete();
            $table->text('staff_in_charge')->nullable()->comment('担当者');
            $table->text('property_name_room')->nullable()->comment('物件名＋部屋番号');
            $table->text('application_method')->nullable()->comment('申込方法');
            $table->boolean('flow_management_transition')->default(false)->comment('フロー管理移行チェック');
            $table->timestamps();
        });
    }

    private function syncFlowManagements(): void
    {
        if (! Schema::hasTable('flow_managements')) {
            return;
        }

        if (! Schema::hasColumn('flow_managements', 'screening_completion_id')
            && Schema::hasTable('screening_completions')) {
            Schema::table('flow_managements', function (Blueprint $table) {
                $table->foreignId('screening_completion_id')
                    ->nullable()
                    ->after('customer_id')
                    ->constrained('screening_completions')
                    ->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('flow_managements', 'application_id')) {
            return;
        }

        $flows = DB::table('flow_managements')
            ->whereNotNull('application_id')
            ->orderBy('id')
            ->get();

        foreach ($flows as $flow) {
            $application = DB::table('applications')->find($flow->application_id);
            if ($application === null) {
                continue;
            }

            $screeningCompletionId = DB::table('screening_completions')
                ->where('application_id', $flow->application_id)
                ->value('id');

            if ($screeningCompletionId === null) {
                $transition = false;
                if (Schema::hasColumn('flow_managements', 'flow_management_transition')) {
                    $transition = (bool) $flow->flow_management_transition;
                }

                $screeningCompletionId = DB::table('screening_completions')->insertGetId([
                    'customer_id' => $flow->customer_id ?? $application->customer_id,
                    'application_id' => $flow->application_id,
                    'staff_in_charge' => $flow->staff_in_charge ?? $application->staff_in_charge,
                    'property_name_room' => $flow->property_name_room ?? $application->property_name_room,
                    'application_method' => $flow->application_method ?? $application->application_method,
                    'flow_management_transition' => $transition,
                    'created_at' => $flow->created_at ?? now(),
                    'updated_at' => $flow->updated_at ?? now(),
                ]);
            }

            if (Schema::hasColumn('flow_managements', 'screening_completion_id')) {
                DB::table('flow_managements')
                    ->where('id', $flow->id)
                    ->whereNull('screening_completion_id')
                    ->update(['screening_completion_id' => $screeningCompletionId]);
            }
        }

        $applications = DB::table('applications')
            ->where('screening_ok', true)
            ->orderBy('id')
            ->get();

        foreach ($applications as $application) {
            $exists = DB::table('screening_completions')
                ->where('application_id', $application->id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('screening_completions')->insert([
                'customer_id' => $application->customer_id,
                'application_id' => $application->id,
                'staff_in_charge' => $application->staff_in_charge,
                'property_name_room' => $application->property_name_room,
                'application_method' => $application->application_method,
                'flow_management_transition' => false,
                'created_at' => $application->created_at ?? now(),
                'updated_at' => $application->updated_at ?? now(),
            ]);
        }
    }

    private function syncSettlementManagements(): void
    {
        if (! Schema::hasTable('settlement_managements')) {
            return;
        }

        if (Schema::hasColumn('settlement_managements', 'settlement_date')
            && ! Schema::hasColumn('settlement_managements', 'contract_date')) {
            DB::statement('ALTER TABLE settlement_managements CHANGE settlement_date contract_date DATE NULL COMMENT \'契約日\'');
        }

        Schema::table('settlement_managements', function (Blueprint $table) {
            if (! Schema::hasColumn('settlement_managements', 'fee_type')) {
                $table->string('fee_type', 20)->nullable()->after('flow_management_id')->comment('手数料種別（advertising / broker_fee）');
            }
            if (! Schema::hasColumn('settlement_managements', 'sales_excluding_tax')) {
                $table->integer('sales_excluding_tax')->nullable()->after('sales_including_tax')->comment('税抜売上');
            }
        });

        $indexExists = DB::selectOne("
            SELECT COUNT(*) AS count
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'settlement_managements'
              AND INDEX_NAME = 'settlement_managements_flow_management_id_fee_type_unique'
        ");

        if ((int) ($indexExists->count ?? 0) === 0
            && Schema::hasColumn('settlement_managements', 'fee_type')) {
            DB::table('settlement_managements')
                ->whereNull('fee_type')
                ->update(['fee_type' => 'advertising']);

            Schema::table('settlement_managements', function (Blueprint $table) {
                $table->unique(['flow_management_id', 'fee_type'], 'settlement_managements_flow_management_id_fee_type_unique');
            });
        }
    }

    private function ensureCareEarthUsers(): void
    {
        if (! Schema::hasTable('careearth_users')) {
            Schema::create('careearth_users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email', 255)->unique();
                $table->string('password_hash', 255);
                $table->string('role', 20)->default('fudosan')->comment('fudosan|keiri');
                $table->dateTime('created_at')->useCurrent();
                $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            });
        }

        if (DB::table('careearth_users')->where('email', 'tomoya_hayashi@careearth.info')->doesntExist()) {
            DB::table('careearth_users')->insert([
                'email' => 'tomoya_hayashi@careearth.info',
                'password_hash' => (string) config(
                    'careearth.password_hash',
                    '$2y$10$NseLpbRzBXWBI7g1kRwBSO3sKHuL0r7vJuSlTssfay/QFwKUodp0y',
                ),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // 統合スキーマは手動復元が必要なため down は未実装
    }
};
