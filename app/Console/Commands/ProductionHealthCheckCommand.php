<?php

namespace App\Console\Commands;

use App\Services\ProductionReadiness;
use Illuminate\Console\Command;

class ProductionHealthCheckCommand extends Command
{
    protected $signature = 'app:production-check';
    protected $description = 'Run production readiness checks for configuration, legal pages, database and platform gating.';

    public function handle(): int
    {
        $report = app(ProductionReadiness::class)->check();

        $this->info('Production readiness report');
        $this->table(
            ['Status', 'Check', 'Message'],
            collect($report['checks'])->map(fn (array $check) => [
                $check['passed'] ? 'OK' : 'FAIL',
                $check['name'],
                $check['message'],
            ])->all(),
        );

        if ($report['warnings'] !== []) {
            $this->warn('Warnings:');
            foreach ($report['warnings'] as $warning) {
                $this->warn(' - ' . $warning);
            }
        }

        if ($report['errors'] !== []) {
            $this->error('Errors:');
            foreach ($report['errors'] as $error) {
                $this->error(' - ' . $error);
            }

            return self::FAILURE;
        }

        $this->info('System is ready for operational deployment.');

        return self::SUCCESS;
    }
}
