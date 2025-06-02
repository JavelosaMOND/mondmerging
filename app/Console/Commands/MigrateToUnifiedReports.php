<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class MigrateToUnifiedReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:migrate-to-unified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate from separate report tables to the unified Report model structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting migration to unified report structure...');
        
        // Step 1: Create the new tables
        $this->info('Creating new tables...');
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_06_01_000000_create_reports_table.php'
        ]);
        
        $this->info(Artisan::output());
        
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_06_01_000001_create_report_files_table.php'
        ]);
        
        $this->info(Artisan::output());
        
        // Step 2: Migrate data
        $this->info('Migrating data from old tables to new structure...');
        Artisan::call('migrate', [
            '--path' => 'database/migrations/2025_06_01_000002_migrate_existing_reports_to_unified_model.php'
        ]);
        
        $this->info(Artisan::output());
        
        $this->info('Migration completed successfully!');
        $this->info('Note: The old report tables have been kept for backup purposes.');
        $this->info('After verifying the migration was successful, you can run:');
        $this->info('  php artisan reports:drop-old-tables');
        
        return 0;
    }
} 