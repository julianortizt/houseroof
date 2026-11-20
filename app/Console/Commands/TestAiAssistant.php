<?php

namespace App\Console\Commands;

use App\Services\AiAssistantService;
use Illuminate\Console\Command;

class TestAiAssistant extends Command
{
    protected $signature = 'ai:test {--provider=groq} {--model=llama-3.3-70b-versatile} {--prompt=} {--template=product_description_es}';
    protected $description = 'Test the AI assistant service with product description, blog, email, etc.';

    public function handle(): int
    {
        $provider = $this->option('provider');
        $model = $this->option('model');
        $customPrompt = $this->option('prompt');

        $this->info('');
        $this->info('╔══════════════════════════════════════════════════════╗');
        $this->info('║                    Costa Resin & Construction - IA                    ║');
        $this->info('╚══════════════════════════════════════════════════════╝');
        $this->info('');

        // Check API key
        $apiKey = config("services.{$provider}.api_key", env(strtoupper($provider) . '_API_KEY', ''));

        if (empty($apiKey)) {
            $this->warn('⚠️  API key no configurada. Se mostrará una vista previa segura; no se inventarán datos.');
            $this->info('   Para contenido real, configura tu key:');
            $this->info('   1. Obtén tu key en: https://console.groq.com/keys');
            $this->info('   2. Añade al .env: GROQ_API_KEY=gsk_...');
            $this->info('');
        } else {
            $this->info("✅ API key configurada para {$provider}");
        }
        $this->info("📡 Modelo: {$model}");
        $this->info('');

        // Get templates
        $service = app(AiAssistantService::class);
        $prompts = $service->getPrompts();
        $templateKey = $this->option('template');

        // Show available templates if none specified or invalid
        if (!isset($prompts[$templateKey])) {
            $this->warn('⚠️  Plantilla no válida. Disponibles:');
            foreach ($prompts as $key => $p) {
                $this->line("   • {$key}: {$p['name']}");
            }
            $this->info('');
            $this->info('Uso: php artisan ai:test --template=product_description_es --prompt="Tu producto"');
            return Command::FAILURE;
        }

        $template = $prompts[$templateKey];
        $this->info("📋 Plantilla: {$template['name']}");

        // Default prompt based on template
        if (empty($customPrompt)) {
            $customPrompt = match($templateKey) {
                'product_description_es', 'product_description_en' => 'Nombre del producto y características verificadas.',
                'blog_post_es', 'blog_post_en' => 'Tema, audiencia y hechos confirmados para el artículo.',
                'email_template_es' => 'Objetivo, destinatario y contexto confirmado del correo.',
                'seo_meta_es' => 'Contenido confirmado de la página y público al que se dirige.',
                'whatsapp_message_es', 'whatsapp_broadcast' => 'Mensaje y datos de contacto confirmados que pueden incluirse.',
                'technical_spec_es' => 'Especificaciones verificadas del producto; indica los campos que falten.',
                'translate_en_es' => 'A short sample sentence for translation.',
                'translate_es_en' => 'Una frase breve de ejemplo para traducir.',
                default => 'Describe el contenido que quieres preparar e incluye solo datos confirmados.',
            };
        }

        $prompt = str_replace('{input}', $customPrompt, $template['prompt_template']);

        $this->info("📝 Prompt:");
        $this->line("   {$customPrompt}");
        $this->info('');
        $this->info('⏳ Generando respuesta...');
        $this->info('');

        $startTime = microtime(true);

        $service = app(AiAssistantService::class);
        $result = $service->generate(
            prompt: $prompt,
            provider: $provider,
            model: $model,
            temperature: 0.7,
            maxTokens: 512,
        );

        $elapsed = round((microtime(true) - $startTime) * 1000);

        if ($result['success']) {
            $demoLabel = ($result['is_demo'] ?? false) ? ' (DEMO)' : '';
            $this->info("✅ Respuesta generada exitosamente{$demoLabel}");
            $this->info("⏱️  Tiempo: {$elapsed}ms");
            $this->info("📊 Modelo: {$result['model']}");
            $this->info('');
            $this->info('┌──────────────────────────────────────────────────────┐');
            $this->info('│  📄 CONTENIDO GENERADO                              │');
            $this->info('└──────────────────────────────────────────────────────┘');
            $this->info('');
            $this->line($result['content']);
            $this->info('');

            if (isset($result['usage'])) {
                $promptTokens = $result['usage']['prompt_tokens'] ?? '?';
                $completionTokens = $result['usage']['completion_tokens'] ?? '?';
                $this->info("📈 Tokens: {$promptTokens} entrada + {$completionTokens} salida");
            }

            return Command::SUCCESS;
        } else {
            $this->error("❌ Error: {$result['error']}");
            return Command::FAILURE;
        }
    }
}
