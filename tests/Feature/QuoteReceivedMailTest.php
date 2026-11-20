<?php

namespace Tests\Feature;

use App\Mail\QuoteReceived;
use App\Models\Quote;
use Tests\TestCase;

class QuoteReceivedMailTest extends TestCase
{
    public function test_quote_notification_renders_a_valid_admin_edit_link(): void
    {
        $quote = new Quote([
            'name' => 'Cliente de prueba',
            'email' => 'cliente@example.test',
            'service_type' => 'resin',
        ]);
        $quote->id = 42;

        $html = (new QuoteReceived($quote))->render();

        $this->assertStringContainsString(
            route('filament.admin.resources.maps-calculator.quotes.edit', $quote),
            html_entity_decode($html),
        );
    }
}
