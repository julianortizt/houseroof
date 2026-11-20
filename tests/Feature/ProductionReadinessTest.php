<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Services\FiscalProfile;
use App\Services\ProductionReadiness;
use App\Services\StripeCredentials;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    public function test_environment_report_marks_the_app_ready_for_local_preview(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('text');
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function ($table) {
                $table->id();
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Page::query()->firstOrCreate(
            ['slug' => 'privacy-policy'],
            ['is_active' => true],
        );

        Page::query()->firstOrCreate(
            ['slug' => 'cookie-policy'],
            ['is_active' => true],
        );

        Page::query()->firstOrCreate(
            ['slug' => 'terms-of-sale'],
            ['is_active' => true],
        );

        Page::query()->firstOrCreate(
            ['slug' => 'legal-notice'],
            ['is_active' => true],
        );

        $report = app(ProductionReadiness::class)->check();

        $this->assertTrue($report['ready']);
        $this->assertArrayHasKey('checks', $report);
        $this->assertNotEmpty($report['checks']);
    }

    public function test_health_endpoint_reports_readiness_over_http(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('text');
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function ($table) {
                $table->id();
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Page::query()->firstOrCreate(['slug' => 'privacy-policy'], ['is_active' => true]);
        Page::query()->firstOrCreate(['slug' => 'cookie-policy'], ['is_active' => true]);
        Page::query()->firstOrCreate(['slug' => 'terms-of-sale'], ['is_active' => true]);
        Page::query()->firstOrCreate(['slug' => 'legal-notice'], ['is_active' => true]);

        $response = $this->get('/health');

        $response->assertOk();
        $response->assertJsonPath('status', 'ok');
    }

    public function test_legacy_cookie_route_redirects_to_the_canonical_legal_page(): void
    {
        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('type')->default('text');
                $table->string('group')->default('general');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function ($table) {
                $table->id();
                $table->string('slug')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        Page::query()->firstOrCreate(['slug' => 'cookie-policy'], ['is_active' => true]);

        $this->from('/')->get('/legal/cookies')
            ->assertRedirect('/legal/cookie-policy');
    }

    public function test_unavailable_database_keeps_stripe_and_fiscal_services_safe(): void
    {
        if (Schema::hasTable('settings')) {
            Schema::drop('settings');
        }

        $this->assertSame([
            'publishable_key' => false,
            'secret_key' => false,
            'webhook_secret' => false,
        ], app(StripeCredentials::class)->configured());

        $this->assertSame([
            'legal_name' => '',
            'tax_id' => '',
            'address' => '',
            'postal_code' => '',
            'city' => '',
            'province' => '',
            'country' => 'ES',
            'contact_email' => '',
            'phone' => '',
            'invoice_serie' => '',
            'issuance_enabled' => false,
        ], app(FiscalProfile::class)->get());
    }
}
