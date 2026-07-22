<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedDemoDataCommand extends Command
{
    protected $signature = 'demo:seed';

    protected $description = '物件〜賃貸管理のデモデータを各10件投入する';

    public function handle(): int
    {
        require_once database_path('seeders/DemoDataSeeder.php');

        (new \Database\Seeders\DemoDataSeeder)->run();

        $this->info('デモデータの投入が完了しました。');

        return self::SUCCESS;
    }
}
