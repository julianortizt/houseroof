<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Services\ProductionReadiness;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionBootstrapCommand extends Command
{
    protected $signature = 'app:production-bootstrap {--force : skip confirmation}';
    protected $description = 'Prepare the application for commercial deployment by validating required infrastructure and caching assets.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('This action prepares the app for production deployment. Continue?')) {
            $this->warn('Production bootstrap cancelled.');

            return self::FAILURE;
        }

        $this->info('Starting production bootstrap...');

        $report = app(ProductionReadiness::class)->check();
        foreach ($report['checks'] as $check) {
            $status = $check['passed'] ? 'OK' : 'FAIL';
            $this->line(sprintf('[%s] %s - %s', $status, $check['name'], $check['message']));
        }

        if ($report['errors'] !== []) {
            $this->error('Production bootstrap blocked by configuration errors:');
            foreach ($report['errors'] as $error) {
                $this->error(' - ' . $error);
            }

            return self::FAILURE;
        }

        $this->info('Clearing and refreshing caches...');
        $this->call('config:cache');
        $this->call('route:cache');
        $this->call('view:cache');

        try {
            $this->call('storage:link');
        } catch (\Throwable $exception) {
            $this->warn('Storage link skipped: ' . $exception->getMessage());
        }

        try {
            $this->call('migrate', ['--force' => true]);
            $this->call('db:seed', ['--class' => 'Database\\Seeders\\PageSeeder']);
        } catch (\Throwable $exception) {
            $this->warn('Database migration/seed was skipped because the production DB is still unavailable: ' . $exception->getMessage());
        }

        $this->ensureRequiredLegalPages();

        $this->info('Production bootstrap completed successfully.');

        return self::SUCCESS;
    }

    protected function ensureRequiredLegalPages(): void
    {
        try {
            if (! DB::connection()->getPdo() || ! Schema::hasTable('pages')) {
                $this->warn('Database is not available yet, so legal pages were not seeded.');

                return;
            }
        } catch (\Throwable $exception) {
            $this->warn('Database is unreachable; legal pages could not be seeded.');

            return;
        }

        $required = ['privacy-policy', 'cookie-policy', 'terms-of-sale', 'legal-notice'];

        foreach ($required as $slug) {
            Page::query()->firstOrCreate(['slug' => $slug], ['is_active' => true]);
        }

        $this->info('Required legal pages are present or created.');
    }
}
