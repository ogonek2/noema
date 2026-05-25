<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureHomepageTablesCommand extends Command
{
    protected $signature = 'homepage:ensure-tables';

    protected $description = 'Create homepage content tables if migration was recorded but tables are missing';

    public function handle(): int
    {
        if (Schema::hasTable('homepage_globals') && Schema::hasTable('homepage_blocks')) {
            $this->info('Homepage tables already exist.');

            return self::SUCCESS;
        }

        $migration = '2026_05_25_100000_create_homepage_content_tables';

        if (Schema::hasTable('migrations')) {
            DB::table('migrations')->where('migration', $migration)->delete();
            $this->warn("Removed stale migration record: {$migration}");
        }

        Artisan::call('migrate', [
            '--force' => true,
            '--path' => 'database/migrations/2026_05_25_100000_create_homepage_content_tables.php',
        ]);

        $this->output->write(Artisan::output());

        if (Schema::hasTable('homepage_globals')) {
            $this->info('Homepage tables created successfully.');

            return self::SUCCESS;
        }

        $this->error('Failed to create homepage tables.');

        return self::FAILURE;
    }
}
