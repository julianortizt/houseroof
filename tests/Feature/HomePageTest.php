<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\JsonLdService;
use App\Services\PlatformModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_homepage_represents_the_resin_business_and_service_areas(): void
    {
        Setting::query()->where('key', 'homepage_template')->delete();

        $this->get('/?lang=es')
            ->assertOk()
            ->assertSee('data-home-template="resin_business"', false)
            ->assertSee('Costa Resin & Construction')
            ->assertSee('Pavimentos de resina, construcción y productos')
            ->assertSee('MÁLAGA')
            ->assertSee('Costa Blanca')
            ->assertSee('Venta de productos')
            ->assertSee(route('quote.create'));
    }

    public function test_homepage_falls_back_when_settings_table_is_unavailable(): void
    {
        Schema::dropIfExists('settings');

        $this->get('/?lang=es')
            ->assertOk()
            ->assertSee('Costa Resin & Construction');
    }

    public function test_previous_corporate_homepage_remains_available_as_an_alternative(): void
    {
        $this->get('/plataforma-empresarial?lang=es')
            ->assertOk()
            ->assertSee('Plataforma empresarial')
            ->assertSee('OPERACIONES EMPRESARIALES EN UN SOLO FLUJO');
    }

    public function test_business_structured_data_does_not_publish_unverified_contact_or_address(): void
    {
        $schema = app(JsonLdService::class);
        $organization = $schema->organization();
        $business = $schema->localBusiness();

        $this->assertArrayNotHasKey('foundingDate', $organization);
        $this->assertArrayNotHasKey('logo', $organization);
        $this->assertArrayNotHasKey('address', $business);
        $this->assertArrayNotHasKey('geo', $business);
        $this->assertArrayNotHasKey('openingHoursSpecification', $business);
        $this->assertArrayNotHasKey('telephone', $business);
        $this->assertArrayNotHasKey('email', $business);
        $modules = app(PlatformModules::class);
        $this->assertTrue($modules->enabled('products'));
        $this->assertSame(
            config('app.url') . '/tienda?q={search_term_string}',
            $schema->website()['potentialAction']['target']['urlTemplate'],
        );

        $modules->setEnabled('products', false);
        $this->assertArrayNotHasKey('potentialAction', $schema->website());

        Setting::set('site_phone', '+34 600 000 000');
        $this->assertSame('+34 600 000 000', app(JsonLdService::class)->localBusiness()['telephone']);
    }
}
