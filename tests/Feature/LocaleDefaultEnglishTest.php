<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleDefaultEnglishTest extends TestCase
{
    use RefreshDatabase;

    public function test_frontend_defaults_to_english_when_no_session_locale_is_present(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertSame('en', app()->getLocale());
    }

    public function test_switching_to_spanish_keeps_the_english_default_elsewhere(): void
    {
        $this->get('/locale/es')->assertRedirect();

        $this->assertSame('es', session('locale'));
        $this->assertSame('es', app()->getLocale());

        $this->get('/')->assertOk();
        $this->assertSame('es', app()->getLocale());
    }
}
