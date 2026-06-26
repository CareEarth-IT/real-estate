<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('careearth_users')) {
            Schema::create('careearth_users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('email', 255)->unique();
                $table->string('password_hash', 255);
                $table->string('role', 20)->default('fudosan')->comment('fudosan|keiri|admin');
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
        Schema::dropIfExists('careearth_users');
    }
};
