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

    public function test_homepage_renders_editable_house_roof_content_and_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('site/logo/test-logo.svg', '<svg></svg>');
        Storage::disk('public')->put('site/hero/example.jpg', 'image');

        // Legacy template values must still render the House Roof homepage.
        Setting::set('homepage_template', 'services');
        Setting::set('logo_path', 'site/logo/test-logo.svg', 'image');
        ContentBlock::set('home', 'roof', 'hero_title', 'Panel hero title', 'text', 'en');
        ContentBlock::set('home', 'roof', 'hero_image', 'site/hero/example.jpg', 'image', 'en');

        $response = $this->get('/?lang=en');

        $response->assertOk()
            ->assertSee('Panel hero title')
            ->assertSee('example.jpg', false)
            ->assertSee('test-logo.svg', false)
            ->assertDontSee('Costa Resin');
    }
}
