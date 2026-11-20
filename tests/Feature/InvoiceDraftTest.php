<?php

namespace Tests\Feature;

use App\Models\AccountingAccount;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\FiscalProfile;
use App\Services\InvoiceDraftService;
use App\Services\PlatformModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceDraftTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_quote_can_be_copied_into_a_non_fiscal_invoice_draft_once(): void
    {
        app(PlatformModules::class)->setEnabled('invoicing', true);
        $user = User::create([
            'name' => 'Administrator',
            'email' => 'invoice-admin@example.com',
            'password' => 'temporary-password',
        ]);
        $this->actingAs($user);

        $quote = Quote::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'phone' => '600123456',
            'company' => 'Customer Company',
            'address' => 'Calle Mayor 10',
            'city' => 'Málaga',
            'postal_code' => '29001',
            'service_type' => 'resin',
            'status' => 'aprobado',
            'subtotal' => 100,
            'tax_percent' => 21,
            'tax_amount' => 21,
            'total' => 121,
        ]);
        $quoteItem = QuoteItem::create([
            'quote_id' => $quote->id,
            'name' => 'Floor service',
            'description' => 'Customer-approved scope',
            'quantity' => 2.5,
            'unit_price' => 40,
            'discount_percent' => 0,
            'sort_order' => 0,
        ]);

        $invoice = app(InvoiceDraftService::class)->createFromApprovedQuote($quote);

        $this->assertSame('draft', $invoice->status);
        $this->assertNull($invoice->invoice_number);
        $this->assertSame('customer@example.com', $invoice->customer_email);
        $this->assertSame('121.00', $invoice->total);
        $this->assertSame($quoteItem->name, $invoice->items->first()->name);
        $this->assertSame('2.50', $invoice->items->first()->quantity);
        $this->assertSame($user->id, $invoice->created_by);
        $this->assertDatabaseCount('invoices', 1);

        try {
            app(InvoiceDraftService::class)->createFromApprovedQuote($quote);
            $this->fail('A quote can only be converted to one invoice draft.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quote', $exception->errors());
        }
    }

    public function test_invoice_draft_generation_requires_approved_quote_and_enabled_module(): void
    {
        app(PlatformModules::class)->setEnabled('accounting', false);
        app(PlatformModules::class)->setEnabled('invoicing', false);
        $quote = Quote::create([
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'service_type' => 'resin',
            'status' => 'borrador',
        ]);
        QuoteItem::create([
            'quote_id' => $quote->id,
            'name' => 'Floor service',
            'quantity' => 1,
            'unit_price' => 100,
        ]);

        try {
            app(InvoiceDraftService::class)->createFromApprovedQuote($quote);
            $this->fail('Invoice drafts remain blocked while the module is disabled.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        app(PlatformModules::class)->setEnabled('invoicing', true);

        try {
            app(InvoiceDraftService::class)->createFromApprovedQuote($quote);
            $this->fail('Only approved quotes may create invoice drafts.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('quote', $exception->errors());
        }

        $this->assertDatabaseCount('invoices', 0);
    }

    public function test_invoice_draft_admin_pages_are_hidden_when_module_is_disabled(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Administrator',
            'email' => 'invoice-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
        ]);
        $administrator->assignRole('admin');

        $this->assertTrue(app(PlatformModules::class)->enabled('invoicing'));
        $this->actingAs($administrator)
            ->get('/admin/invoices')
            ->assertOk()
            ->assertSee('Borradores de factura');

        app(PlatformModules::class)->setEnabled('accounting', false);
        app(PlatformModules::class)->setEnabled('invoicing', false);
        $this->actingAs($administrator)
            ->get('/admin/invoices')
            ->assertForbidden();

        app(PlatformModules::class)->setEnabled('invoicing', true);

        $this->actingAs($administrator)
            ->get('/admin/invoices')
            ->assertOk()
            ->assertSee('Borradores de factura');

        $quote = Quote::create([
            'name' => 'Visible customer',
            'email' => 'visible@example.com',
            'service_type' => 'resin',
            'status' => 'aprobado',
        ]);
        QuoteItem::create([
            'quote_id' => $quote->id,
            'name' => 'Visible line',
            'quantity' => 1,
            'unit_price' => 10,
        ]);
        $invoice = app(InvoiceDraftService::class)->createFromApprovedQuote($quote);

        $this->actingAs($administrator)
            ->get(route('filament.admin.resources.invoices.view', $invoice))
            ->assertOk()
            ->assertSee('Borrador interno no fiscal');
    }

    public function test_approved_quote_can_be_issued_as_a_real_invoice_and_creates_accounting_closure(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $administrator = User::create([
            'name' => 'Fiscal admin',
            'email' => 'fiscal-admin@example.com',
            'password' => 'temporary-password',
            'email_verified_at' => now(),
        ]);
        $administrator->assignRole('admin');
        $this->actingAs($administrator);

        app(PlatformModules::class)->setEnabled('invoicing', true);
        app(PlatformModules::class)->setEnabled('accounting', true);
        app(FiscalProfile::class)->save([
            'legal_name' => 'Costa Resin S.L.',
            'tax_id' => 'B12345678',
            'address' => 'Calle Mayor 1',
            'postal_code' => '29001',
            'city' => 'Málaga',
            'province' => 'Málaga',
            'country' => 'ES',
            'contact_email' => 'billing@example.com',
            'phone' => '+34951000000',
            'invoice_serie' => 'F1',
            'issuance_enabled' => true,
        ]);

        $quote = Quote::create([
            'name' => 'Paula Customer',
            'email' => 'paula@example.com',
            'phone' => '600111222',
            'company' => 'ACME SL',
            'address' => 'Calle Falsa 123',
            'city' => 'Málaga',
            'postal_code' => '29001',
            'service_type' => 'resin',
            'status' => 'aprobado',
            'subtotal' => 100,
            'tax_percent' => 21,
            'tax_amount' => 21,
            'total' => 121,
        ]);
        QuoteItem::create([
            'quote_id' => $quote->id,
            'name' => 'Pavimento epoxi',
            'description' => 'Instalación en garaje',
            'quantity' => 1,
            'unit_price' => 100,
            'discount_percent' => 0,
            'sort_order' => 0,
        ]);

        $invoice = app(InvoiceDraftService::class)->createFromApprovedQuote($quote);
        $issued = app(InvoiceDraftService::class)->issue($invoice);

        $this->assertSame('issued', $issued->status);
        $this->assertNotNull($issued->invoice_number);
        $this->assertSame('2026', $issued->issued_at?->format('Y'));
        $this->assertDatabaseHas('journal_entries', ['source_type' => Invoice::class, 'source_id' => $issued->id, 'status' => 'posted']);
        $this->assertTrue(AccountingAccount::query()->where('code', '1200')->exists());
        $this->assertTrue(AccountingAccount::query()->where('code', '7000')->exists());

        $entry = JournalEntry::query()->where('source_type', Invoice::class)->where('source_id', $issued->id)->firstOrFail();
        $this->assertSame('posted', $entry->status);
        $this->assertSame(3, $entry->lines()->count());
        $this->assertEquals(121.0, (float) $entry->lines()->sum('debit'));
        $this->assertEquals(121.0, (float) $entry->lines()->sum('credit'));
    }
}
