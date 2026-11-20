<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AccountingJournalService
{
    public function post(JournalEntry $entry): JournalEntry
    {
        $this->authorizeAccounting();

        return DB::transaction(function () use ($entry): JournalEntry {
            $lockedEntry = JournalEntry::query()->lockForUpdate()->findOrFail($entry->id);
            if ($lockedEntry->status !== 'draft') {
                throw ValidationException::withMessages([
                    'entry' => 'Solo se pueden contabilizar asientos en borrador.',
                ]);
            }

            $lines = $lockedEntry->lines()->with('account')->get();
            $debitCents = 0;
            $creditCents = 0;

            if ($lines->count() < 2) {
                throw ValidationException::withMessages([
                    'lines' => 'Un asiento requiere al menos dos apuntes.',
                ]);
            }

            foreach ($lines as $line) {
                $debit = $this->toCents($line->debit);
                $credit = $this->toCents($line->credit);
                if (($debit > 0) === ($credit > 0) || $debit < 0 || $credit < 0 || ! $line->account?->is_active) {
                    throw ValidationException::withMessages([
                        'lines' => 'Cada apunte debe usar una cuenta activa y tener un único importe positivo, en debe o haber.',
                    ]);
                }
                $debitCents += $debit;
                $creditCents += $credit;
            }

            if ($debitCents <= 0 || $debitCents !== $creditCents) {
                throw ValidationException::withMessages([
                    'lines' => 'El asiento debe cuadrar exactamente en céntimos y no puede tener importe cero.',
                ]);
            }

            $lockedEntry->update([
                'entry_number' => $lockedEntry->entry_number ?: sprintf('GEN-%s-%06d', $lockedEntry->entry_date->format('Y'), $lockedEntry->id),
                'status' => 'posted',
                'posted_at' => now(),
            ]);

            return $lockedEntry->fresh(['lines.account', 'creator']);
        });
    }

    public function reverse(JournalEntry $entry, string $reason): JournalEntry
    {
        $this->authorizeAccounting();
        Validator::make(['reason' => $reason], [
            'reason' => ['required', 'string', 'max:1000'],
        ])->validate();

        return DB::transaction(function () use ($entry, $reason): JournalEntry {
            $original = JournalEntry::query()->lockForUpdate()->with('lines')->findOrFail($entry->id);
            if ($original->status !== 'posted' || $original->reversed_by_entry_id !== null) {
                throw ValidationException::withMessages([
                    'entry' => 'Solo se puede revertir una vez un asiento contabilizado que no tenga ya reverso.',
                ]);
            }

            $reversal = JournalEntry::create([
                'entry_date' => today(),
                'description' => 'Reverso de ' . $original->entry_number . ': ' . trim($reason),
                'status' => 'posted',
                'reversal_of_id' => $original->id,
                'posted_at' => now(),
                'created_by' => auth()->id(),
            ]);
            $reversal->update([
                'entry_number' => sprintf('GEN-%s-%06d', $reversal->entry_date->format('Y'), $reversal->id),
            ]);

            foreach ($original->lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $reversal->id,
                    'accounting_account_id' => $line->accounting_account_id,
                    'debit' => $line->credit,
                    'credit' => $line->debit,
                    'description' => $line->description,
                ]);
            }

            $original->update([
                'status' => 'reversed',
                'reversed_by_entry_id' => $reversal->id,
            ]);

            return $reversal->fresh(['lines.account', 'reversalOf']);
        });
    }

    public function trialBalance(): Collection
    {
        abort_unless(app(PlatformModules::class)->enabled('accounting'), 404, 'Accounting module is disabled.');

        $totals = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->whereIn('journal_entries.status', ['posted', 'reversed'])
            ->selectRaw('accounting_account_id, SUM(debit) as debit_total, SUM(credit) as credit_total')
            ->groupBy('accounting_account_id')
            ->get()
            ->keyBy('accounting_account_id');

        return AccountingAccount::query()
            ->orderBy('code')
            ->get()
            ->map(function (AccountingAccount $account) use ($totals): array {
                $debit = (float) ($totals[$account->id]->debit_total ?? 0);
                $credit = (float) ($totals[$account->id]->credit_total ?? 0);
                $debitNormal = in_array($account->account_type, ['asset', 'expense'], true);

                return [
                    'account' => $account,
                    'debit' => number_format($debit, 2, '.', ''),
                    'credit' => number_format($credit, 2, '.', ''),
                    'balance' => number_format($debitNormal ? $debit - $credit : $credit - $debit, 2, '.', ''),
                ];
            });
    }

    private function authorizeAccounting(): void
    {
        abort_unless(app(PlatformModules::class)->enabled('accounting'), 404, 'Accounting module is disabled.');
        $user = auth()->user();
        $isAdministrator = $user !== null
            && ($user->hasRole(['super_admin', 'admin', 'editor', 'manager', 'site_admin'])
                || (filled(config('platform.developer_email')) && $user->email === config('platform.developer_email')));
        abort_unless($isAdministrator, 403);
    }

    private function toCents(mixed $amount): int
    {
        if (! is_numeric($amount) || ! is_finite((float) $amount) || (float) $amount < 0) {
            throw ValidationException::withMessages([
                'lines' => 'Los importes deben ser números finitos no negativos.',
            ]);
        }

        $scaled = (float) $amount * 100;
        $rounded = round($scaled);
        if (abs($scaled - $rounded) > 0.000001) {
            throw ValidationException::withMessages([
                'lines' => 'Los importes admiten como máximo dos decimales.',
            ]);
        }

        return (int) $rounded;
    }
}
