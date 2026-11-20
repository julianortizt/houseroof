<?php

namespace App\Services;

use App\Models\AccountingAccount;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Quote;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceDraftService
{
    public function createFromApprovedQuote(Quote $quote): Invoice
    {
        abort_unless(app(PlatformModules::class)->enabled('invoicing'), 404, 'Invoicing module is disabled.');
        abort_unless(app(PlatformModules::class)->enabled('quotes'), 404, 'Quotes module is disabled.');

        return DB::transaction(function () use ($quote): Invoice {
            $lockedQuote = Quote::query()
                ->lockForUpdate()
                ->findOrFail($quote->id);

            if ($lockedQuote->status !== 'aprobado') {
                throw ValidationException::withMessages([
                    'quote' => 'Solo se pueden preparar borradores internos desde cotizaciones aprobadas.',
                ]);
            }

            $quoteItems = $lockedQuote->items()->get();
            if ($quoteItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'quote' => 'La cotización debe tener al menos una línea.',
                ]);
            }

            $existing = Invoice::query()->where('quote_id', $lockedQuote->id)->first();
            if ($existing) {
                throw ValidationException::withMessages([
                    'quote' => 'Ya existe un borrador de factura para esta cotización.',
                ]);
            }

            $invoice = Invoice::create([
                'quote_id' => $lockedQuote->id,
                'status' => 'draft',
                'customer_name' => $lockedQuote->name,
                'customer_email' => $lockedQuote->email,
                'customer_phone' => $lockedQuote->phone,
                'company' => $lockedQuote->company,
                'billing_address' => $lockedQuote->address,
                'billing_city' => $lockedQuote->city,
                'billing_postal_code' => $lockedQuote->postal_code,
                'subtotal' => $lockedQuote->subtotal,
                'tax_percent' => $lockedQuote->tax_percent,
                'tax_amount' => $lockedQuote->tax_amount,
                'total' => $lockedQuote->total,
                'created_by' => auth()->id(),
            ]);

            foreach ($quoteItems as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'quote_item_id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_percent' => $item->discount_percent,
                    'total_price' => $item->total_price,
                    'sort_order' => $item->sort_order,
                ]);
            }

            return $invoice->load('items', 'quote');
        });
    }

    public function issue(Invoice $invoice): Invoice
    {
        abort_unless(app(PlatformModules::class)->enabled('invoicing'), 404, 'Invoicing module is disabled.');
        abort_unless(app(PlatformModules::class)->enabled('accounting'), 404, 'Accounting module is disabled.');

        $fiscalProfile = app(FiscalProfile::class);
        if (! $fiscalProfile->canIssue()) {
            throw ValidationException::withMessages([
                'invoice' => 'Debe completar el perfil fiscal de la empresa y activar la emisión fiscal antes de emitir facturas.',
            ]);
        }

        return DB::transaction(function () use ($invoice, $fiscalProfile): Invoice {
            $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);

            if ($lockedInvoice->status !== 'draft') {
                throw ValidationException::withMessages([
                    'invoice' => 'Solo se puede emitir una factura en estado borrador.',
                ]);
            }

            $issuedAt = now();
            $invoiceNumber = $fiscalProfile->nextInvoiceNumber();

            $lockedInvoice->update([
                'invoice_number' => $invoiceNumber,
                'status' => 'issued',
                'issued_at' => $issuedAt,
                'due_date' => $issuedAt->copy()->addDays(30),
            ]);

            $receivable = AccountingAccount::query()->firstOrCreate(
                ['code' => '1200'],
                ['name' => 'Clientes y deudores', 'account_type' => 'asset', 'is_active' => true],
            );
            $income = AccountingAccount::query()->firstOrCreate(
                ['code' => '7000'],
                ['name' => 'Ingresos por servicios', 'account_type' => 'income', 'is_active' => true],
            );
            $vat = AccountingAccount::query()->firstOrCreate(
                ['code' => '2400'],
                ['name' => 'IVA repercutido', 'account_type' => 'liability', 'is_active' => true],
            );

            $entry = JournalEntry::create([
                'entry_date' => $issuedAt->toDateString(),
                'description' => 'Factura emitida ' . $invoiceNumber,
                'status' => 'draft',
                'source_type' => Invoice::class,
                'source_id' => $lockedInvoice->id,
                'created_by' => auth()->id(),
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'accounting_account_id' => $receivable->id,
                'debit' => (string) $lockedInvoice->total,
                'credit' => '0.00',
                'description' => 'Cobro pendiente fact.' . $invoiceNumber,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'accounting_account_id' => $income->id,
                'debit' => '0.00',
                'credit' => (string) max(0, $lockedInvoice->subtotal),
                'description' => 'Ingreso por servicios fact.' . $invoiceNumber,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'accounting_account_id' => $vat->id,
                'debit' => '0.00',
                'credit' => (string) max(0, $lockedInvoice->tax_amount),
                'description' => 'IVA repercutido fact.' . $invoiceNumber,
            ]);

            app(AccountingJournalService::class)->post($entry->fresh(['lines.account']));

            return $lockedInvoice->fresh(['items', 'quote']);
        });
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $sequence = Invoice::query()->whereNotNull('invoice_number')->count() + 1;

        do {
            $number = sprintf('INV-%s-%06d', $year, $sequence);
            $sequence++;
        } while (Invoice::query()->where('invoice_number', $number)->exists());

        return $number;
    }
}
