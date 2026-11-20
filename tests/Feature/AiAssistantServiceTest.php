<?php

namespace Tests\Feature;

use App\Services\AiAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiAssistantServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_unconfigured_assistant_returns_a_safe_preview_without_business_claims(): void
    {
        config([
            'services.groq.api_key' => '',
        ]);
        $this->app->setLocale('es');

        $result = app(AiAssistantService::class)->generate(
            'Redacta el texto <script>alert("x")</script> con datos verificados.',
        );

        $this->assertTrue($result['success']);
        $this->assertTrue($result['is_demo']);
        $this->assertStringContainsString('&lt;script&gt;', $result['content']);
        $this->assertStringNotContainsString('<script>', $result['content']);
        $this->assertStringContainsString('No se generó una respuesta', $result['content']);
        $this->assertStringNotContainsString('Costa Resin & Construction', $result['content']);
        $this->assertStringNotContainsString('Benissa', $result['content']);
        $this->assertStringNotContainsString('711 073 958', $result['content']);
    }

    public function test_content_templates_do_not_inject_unconfigured_company_or_product_details(): void
    {
        $templates = app(AiAssistantService::class)->getPrompts();

        foreach ($templates as $template) {
            $this->assertStringNotContainsString('Costa Resin & Construction', $template['prompt_template']);
            $this->assertStringNotContainsString('Benissa', $template['prompt_template']);
            $this->assertStringNotContainsString('Alicante', $template['prompt_template']);
        }
    }
}
