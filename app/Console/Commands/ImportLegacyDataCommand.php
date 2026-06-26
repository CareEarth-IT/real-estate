<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportLegacyDataCommand extends Command
{
    protected $signature = 'careearth:import-legacy
                            {--from=careearth_home : 移行元データベース名}
                            {--force : 既存データがあっても上書きする}';

    protected $description = 'careearth_home から estate へ物件マスターデータを移行する';

    /** @var list<string> */
    private array $tables = [
        'property_master',
        'property_addresses',
        'sales_persons',
    ];

    public function handle(): int
    {
        $sourceDb = (string) $this->option('from');
        $force = (bool) $this->option('force');
        $targetDb = (string) config('database.connections.mysql.database');

        if ($sourceDb === $targetDb) {
            $this->error('移行元と移行先が同じデータベースです。');

            return self::FAILURE;
        }

        config(['database.connections.legacy.database' => $sourceDb]);
        DB::purge('legacy');

        try {
            DB::connection('legacy')->getPdo();
        } catch (\Throwable $e) {
            $this->error("移行元データベース「{$sourceDb}」に接続できません。");

            return self::FAILURE;
        }

        $this->info("{$sourceDb} → {$targetDb} へデータを移行します...");

        foreach ($this->tables as $table) {
            if (! Schema::connection('legacy')->hasTable($table)) {
                $this->warn("  {$table}: 移行元にテーブルなし（スキップ）");
                continue;
            }

            if (! Schema::hasTable($table)) {
                $this->error("  {$table}: 移行先にテーブルがありません。先に php artisan migrate を実行してください。");

                return self::FAILURE;
            }

            $targetCount = DB::table($table)->count();
            if ($targetCount > 0 && ! $force) {
                $this->line("  {$table}: 移行先に {$targetCount} 件あり（スキップ）");

                continue;
            }

            if ($force && $targetCount > 0) {
                DB::table($table)->truncate();
            }

            $rows = DB::connection('legacy')->table($table)->orderBy('id')->get();
            $imported = 0;

            foreach ($rows as $row) {
                DB::table($table)->updateOrInsert(
                    ['id' => $row->id],
                    (array) $row,
                );
                $imported++;
            }

            $this->info("  {$table}: {$imported} 件を移行");
        }

        if (Schema::connection('legacy')->hasTable('users')) {
            $legacyUsers = DB::connection('legacy')->table('users')->get();
            $importedUsers = 0;

            foreach ($legacyUsers as $row) {
                $exists = DB::table('careearth_users')->where('email', $row->email)->exists();
                if ($exists && ! $force) {
                    continue;
                }

                DB::table('careearth_users')->updateOrInsert(
                    ['email' => $row->email],
                    [
                        'password_hash' => $row->password_hash,
                        'role' => $row->role ?? 'fudosan',
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ],
                );
                $importedUsers++;
            }

            $this->info("  careearth_users: {$importedUsers} 件を移行");
        }

        $this->newLine();
        $this->info('移行が完了しました。');

        return self::SUCCESS;
    }
}
