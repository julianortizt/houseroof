<?php

namespace Tests\Feature;

use App\Models\AccountingAccount;
use App\Models\JournalEntry;
use App\Models\User;
use App\Services\AccountingJournalService;
use App\Services\PlatformModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountingJournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_balanced_entries_can_be_posted_and_reversals_preserve_the_trial_balance(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Accounting administrator',
            'email' => 'accounting@example.com',
            'password' => 'temporary-password',
        ]);
        $administrator->assignRole('admin');
        $this->actingAs($administrator);
        app(PlatformModules::class)->setEnabled('invoicing', true);
        app(PlatformModules::class)->setEnabled('accounting', true);

        $cash = AccountingAccount::create([
            'code' => 'TEST-CASH',
            'name' => 'Test cash account',
            'account_type' => 'asset',
        ]);
        $income = AccountingAccount::create([
            'code' => 'TEST-INCOME',
            'name' => 'Test income account',
            'account_type' => 'income',
        ]);
        $entry = JournalEntry::create([
            'entry_date' => today(),
            'description' => 'Test balanced entry',
            'created_by' => $administrator->id,
        ]);
        $entry->lines()->create([
            'accounting_account_id' => $cash->id,
            'debit' => '100.00',
            'credit' => '0.00',
        ]);
        $entry->lines()->create([
            'accounting_account_id' => $income->id,
            'debit' => '0.00',
            'credit' => '100.00',
        ]);

        $service = app(AccountingJournalService::class);
        $posted = $service->post($entry);

        $this->assertSame('posted', $posted->status);
        $this->assertSame('GEN-' . today()->format('Y') . '-000001', $posted->entry_number);
        $beforeReversal = $service->trialBalance()->keyBy(fn (array $row): int => $row['account']->id);
        $this->assertSame('100.00', $beforeReversal[$cash->id]['balance']);
        $this->assertSame('100.00', $beforeReversal[$income->id]['balance']);

        $reversal = $service->reverse($posted, 'Test correction');
        $this->assertSame($posted->id, $reversal->reversal_of_id);
        $this->assertSame('reversed', $posted->fresh()->status);
        $afterReversal = $service->trialBalance()->keyBy(fn (array $row): int => $row['account']->id);
        $this->assertSame('0.00', $afterReversal[$cash->id]['balance']);
        $this->assertSame('0.00', $afterReversal[$income->id]['balance']);
    }

    public function test_unbalanced_or_incomplete_entries_remain_drafts(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Accounting administrator',
            'email' => 'accounting@example.com',
            'password' => 'temporary-password',
        ]);
        $administrator->assignRole('admin');
        $this->actingAs($administrator);
        app(PlatformModules::class)->setEnabled('invoicing', true);
        app(PlatformModules::class)->setEnabled('accounting', true);

        $account = AccountingAccount::create([
            'code' => 'TEST-ACCOUNT',
            'name' => 'Test account',
            'account_type' => 'asset',
        ]);
        $entry = JournalEntry::create([
            'entry_date' => today(),
            'description' => 'Unbalanced draft',
        ]);
        $entry->lines()->create([
            'accounting_account_id' => $account->id,
            'debit' => '10.00',
            'credit' => '0.00',
        ]);

        try {
            app(AccountingJournalService::class)->post($entry);
            $this->fail('An incomplete unbalanced entry must not post.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('lines', $exception->errors());
        }

        $this->assertSame('draft', $entry->fresh()->status);
        $this->assertNull($entry->fresh()->entry_number);
    }

    public function test_accounting_panel_resources_are_controlled_by_the_module_flag(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Accounting administrator',
            'email' => 'accounting@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
        ]);
        $administrator->assignRole('admin');

        app(PlatformModules::class)->setEnabled('accounting', false);
        $this->actingAs($administrator)->get('/admin/accounting-accounts')->assertForbidden();

        app(PlatformModules::class)->setEnabled('invoicing', true);
        app(PlatformModules::class)->setEnabled('accounting', true);
        $this->actingAs($administrator)
            ->get('/admin/accounting-accounts')
            ->assertOk()
            ->assertSee('Cuentas internas');
        $this->actingAs($administrator)
            ->get('/admin/journal-entries/create')
            ->assertOk()
            ->assertSee('Apuntes');
        $this->actingAs($administrator)
            ->get('/admin/accounting/trial-balance')
            ->assertOk()
            ->assertSee('Balance de comprobación interno');
    }
}
