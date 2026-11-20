<?php

namespace Tests\Feature;

use App\Models\MapsCalculator\Client;
use App\Models\MapsCalculator\Project;
use App\Models\Customer;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_team_member_can_download_a_quote_pdf(): void
    {
        $user = User::create([
            'name' => 'Maps Admin',
            'email' => 'pdf-admin@example.test',
            'password' => 'password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'admin',
        ]);
        $client = Client::create(['name' => 'Example Client', 'company' => 'Example Company']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Flooring Project',
            'type' => 'interior',
            'created_by' => $user->id,
        ]);
        $quote = Quote::create([
            'project_id' => $project->id,
            'name' => 'Example Client',
            'email' => 'client@example.test',
            'service_type' => 'resin',
            'status' => 'enviado',
            'subtotal' => 100,
            'tax_percent' => 21,
            'tax_amount' => 21,
            'total' => 121,
        ]);
        QuoteItem::create([
            'quote_id' => $quote->id,
            'name' => 'Resin flooring',
            'quantity' => 10,
            'unit_price' => 10,
            'discount_percent' => 0,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($user)
            ->get(route('mapscalculator.quote.pdf', $quote));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_quote_pdf_remains_private_to_assigned_team_members(): void
    {
        $owner = User::create([
            'name' => 'Project Owner',
            'email' => 'pdf-owner@example.test',
            'password' => 'password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'comercial',
        ]);
        $other = User::create([
            'name' => 'Other Commercial',
            'email' => 'pdf-other@example.test',
            'password' => 'password',
            'email_verified_at' => now(),
            'mapscalculator_role' => 'comercial',
        ]);
        $client = Client::create(['name' => 'Example Client']);
        $project = Project::create([
            'client_id' => $client->id,
            'name' => 'Private Project',
            'type' => 'interior',
            'created_by' => $owner->id,
        ]);
        $quote = Quote::create([
            'project_id' => $project->id,
            'name' => 'Example Client',
            'service_type' => 'resin',
            'status' => 'borrador',
        ]);

        $this->actingAs($other)
            ->get(route('mapscalculator.quote.pdf', $quote))
            ->assertForbidden();
    }

    public function test_verified_customer_can_download_only_their_own_sent_quote_pdf(): void
    {
        $customer = Customer::create([
            'name' => 'Verified Customer',
            'email' => 'pdf-customer@example.test',
            'password' => 'password',
        ]);
        $customer->forceFill(['email_verified_at' => now()])->save();
        $quote = Quote::create([
            'name' => 'Verified Customer',
            'email' => $customer->email,
            'service_type' => 'resin',
            'status' => 'enviado',
            'subtotal' => 100,
            'tax_percent' => 21,
            'tax_amount' => 21,
            'total' => 121,
        ]);
        $foreignQuote = Quote::create([
            'name' => 'Other Customer',
            'email' => 'other-customer@example.test',
            'service_type' => 'resin',
            'status' => 'enviado',
        ]);

        $this->actingAs($customer, 'customer')
            ->get(route('customer.quotes.pdf', $quote))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('customer.quotes.pdf', $foreignQuote))->assertNotFound();
    }
}
