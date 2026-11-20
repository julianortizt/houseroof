<?php

namespace App\Filament\Pages\Accounting;

use App\Services\AccountingJournalService;
use App\Services\PlatformModules;
use Filament\Pages\Page;

class TrialBalance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-scale';
    protected static ?string $navigationGroup = 'Contabilidad (base interna)';
    protected static ?string $navigationLabel = 'Balance de comprobación';
    protected static ?string $slug = 'accounting/trial-balance';
    protected static ?int $navigationSort = 3;
    protected static string $view = 'filament.pages.accounting.trial-balance';

    public array $rows = [];
    public string $totalDebit = '0.00';
    public string $totalCredit = '0.00';

    public static function canAccess(): bool
    {
        return app(PlatformModules::class)->enabled('accounting') && parent::canAccess();
    }

    public function mount(AccountingJournalService $accounting): void
    {
        $rows = $accounting->trialBalance();
        $this->rows = $rows->map(fn (array $row): array => [
            'code' => $row['account']->code,
            'name' => $row['account']->name,
            'type' => $row['account']->account_type,
            'debit' => $row['debit'],
            'credit' => $row['credit'],
            'balance' => $row['balance'],
        ])->all();
        $this->totalDebit = number_format($rows->sum(fn (array $row): float => (float) $row['debit']), 2, '.', '');
        $this->totalCredit = number_format($rows->sum(fn (array $row): float => (float) $row['credit']), 2, '.', '');
    }
}
