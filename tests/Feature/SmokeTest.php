<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_responds_successfully()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_uses_slider_settings(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('site/slides/example.jpg', 'image');
        Setting::set('homepage_template', 'services');
        Setting::set('slide_1_title', 'Panel slider title');
        Setting::set('slide_1_image', 'site/slides/example.jpg', 'image');
        ContentBlock::set('home', 'cta', 'title', 'Database CTA title', 'text', 'en');

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Panel slider title')
            ->assertSee('Database CTA title')
            ->assertSee('example.jpg', false);
    }
}
