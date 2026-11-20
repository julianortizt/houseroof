<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiAssistantService
{
    protected string $groqApiKey = '';
    protected string $groqBaseUrl = 'https://api.groq.com/openai/v1';

    // Supported providers
    protected array $providers = [
        'groq' => [
            'name' => 'Groq',
            'url' => 'https://api.groq.com/openai/v1/chat/completions',
            'models' => [
                'llama-3.3-70b-versatile' => 'Llama 3.3 70B (Recomendado)',
                'llama-3.1-8b-instant' => 'Llama 3.1 8B (Rápido)',
                'mixtral-8x7b-32768' => 'Mixtral 8x7B',
                'gemma2-9b-it' => 'Gemma 2 9B',
            ],
        ],
        'openai' => [
            'name' => 'OpenAI',
            'url' => 'https://api.openai.com/v1/chat/completions',
            'models' => [
                'gpt-4o-mini' => 'GPT-4o Mini',
                'gpt-4o' => 'GPT-4o',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            ],
        ],
        'anthropic' => [
            'name' => 'Anthropic',
            'url' => 'https://api.anthropic.com/v1/messages',
            'models' => [
                'claude-3-5-sonnet-20241022' => 'Claude 3.5 Sonnet',
                'claude-3-haiku-20240307' => 'Claude 3 Haiku',
            ],
        ],
        'deepseek' => [
            'name' => 'DeepSeek',
            'url' => 'https://api.deepseek.com/chat/completions',
            'models' => [
                'deepseek-chat' => 'DeepSeek Chat',
                'deepseek-reasoner' => 'DeepSeek Reasoner',
            ],
        ],
    ];

    public function __construct()
    {
        $this->groqApiKey = (string) (config('services.groq.api_key', env('GROQ_API_KEY', '')) ?? '');

        // Fallback to Settings if not in config
        if (empty($this->groqApiKey)) {
            $this->groqApiKey = (string) \App\Models\Setting::get('ai_api_key', '');
        }
    }

    /**
     * Build a knowledge base from the database for the AI system prompt.
     */
    public function buildKnowledgeBase(): string
    {
        $contact = collect([
            'Email' => \App\Models\Setting::get('site_email'),
            'Phone' => \App\Models\Setting::get('site_phone'),
            'WhatsApp' => \App\Models\Setting::get('site_whatsapp'),
            'Address' => \App\Models\Setting::get('site_address'),
        ])->filter();

        $kb = "=== " . config('platform.public_name') . " KNOWLEDGE BASE ===\n";
        $kb .= 'Website: ' . config('app.url') . "\n";

        foreach ($contact as $label => $value) {
            $kb .= "{$label}: {$value}\n";
        }

        if (app(\App\Services\PlatformModules::class)->enabled('products')) {
            $products = \App\Models\Product::active()
                ->with('category')
                ->select('id', 'name', 'slug', 'description', 'short_description', 'price', 'category_id', 'is_featured', 'is_coming_soon')
                ->get();

            $kb .= "\n--- PRODUCTS ({$products->count()}) ---\n";
            foreach ($products as $product) {
                $category = $product->category?->name ?? 'Uncategorized';
                $description = $product->short_description ?: mb_substr(strip_tags($product->description ?? ''), 0, 150);
                $kb .= "• {$product->name} ({$category}) - {$product->getPriceFormatted()}\n";
                if ($description) {
                    $kb .= "  Description: {$description}\n";
                }
            }
        } else {
            $kb .= "\nThe product catalog is disabled.\n";
        }

        return $kb;
    }

    /**
     * Get the system prompt for the AI assistant with company knowledge.
     */
    public function getSystemPrompt(): string
    {
        $kb = $this->buildKnowledgeBase();
        $platformName = config('platform.public_name');
        $commerceEnabled = app(\App\Services\PlatformModules::class)->enabled('products');
        $commerceInstructions = $commerceEnabled
            ? 'When asked about products, use only items listed in the knowledge base and link to /tienda.'
            : 'Do not mention products, prices, categories, or the online shop; the catalog is disabled.';

        return <<<PROMPT
You are a helpful AI assistant for {$platformName}.

KNOWLEDGE BASE:
{$kb}

RULES:
1. Answer based only on the knowledge base above. Do not invent business, product, pricing, or contact details.
2. If a question cannot be answered, direct the user to the contact form at /contacto.
3. Always respond in the same language the user writes in.
4. Be helpful, professional, and concise.
5. {$commerceInstructions}
6. For estimates, direct users to /cotizacion.
PROMPT;
    }


    /**
     * Get available AI providers and their models.
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Generate text using the specified provider.
     *
     * @param string $prompt The user prompt
     * @param string $provider Provider name (groq, openai, anthropic, deepseek)
     * @param string $model Model identifier
     * @param float $temperature Creativity level (0.0-2.0)
     * @param int $maxTokens Maximum response tokens
     */
    public function generate(
        string $prompt,
        string $provider = 'groq',
        string $model = 'llama-3.3-70b-versatile',
        float $temperature = 0.7,
        int $maxTokens = 2048,
        ?string $apiKey = null
    ): array {
        try {
            if ($apiKey === null) {
                $apiKey = (string) (config("services.{$provider}.api_key", env(strtoupper($provider) . '_API_KEY', '')) ?? '');
            }

            if (empty($apiKey)) {
                // Return demo response when no API key is configured
                return $this->generateDemoResponse($prompt, $provider, $model);
            }

            return match ($provider) {
                'groq' => $this->callGroq($prompt, $model, $apiKey, $temperature, $maxTokens),
                'openai' => $this->callOpenAI($prompt, $model, $apiKey, $temperature, $maxTokens),
                'anthropic' => $this->callAnthropic($prompt, $model, $apiKey, $temperature, $maxTokens),
                'deepseek' => $this->callDeepSeek($prompt, $model, $apiKey, $temperature, $maxTokens),
                default => ['success' => false, 'error' => "Proveedor no soportado: {$provider}"],
            };
        } catch (\Throwable $e) {
            Log::error("AI Assistant error ({$provider}): " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al conectar con la API de IA: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Return a safe preview when no API key is configured.
     */
    protected function generateDemoResponse(string $prompt, string $provider, string $model): array
    {
        $demoContent = $this->generateGenericDemo($prompt);

        return [
            'success' => true,
            'content' => $demoContent,
            'model' => "demo-{$model}",
            'provider' => "{$provider} (DEMO)",
            'usage' => [
                'prompt_tokens' => strlen($prompt),
                'completion_tokens' => strlen($demoContent),
                'total_tokens' => strlen($prompt) + strlen($demoContent),
            ],
            'is_demo' => true,
        ];
    }

    protected function generateGenericDemo(string $prompt): string
    {
        $safePrompt = htmlspecialchars(trim($prompt), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        if (app()->getLocale() === 'es') {
            return <<<HTML
<strong>Vista previa del asistente</strong>
<p>El proveedor de IA no está configurado. No se generó una respuesta ni se completaron datos no verificados.</p>
<blockquote>{$safePrompt}</blockquote>
<p>Configure un proveedor de IA para generar contenido basado en su solicitud y en los datos confirmados de la plataforma.</p>
HTML;
        }

        return <<<HTML
<strong>Assistant preview</strong>
<p>The AI provider is not configured. No answer was generated and no unverified details were added.</p>
<blockquote>{$safePrompt}</blockquote>
<p>Configure an AI provider to generate content based on your request and the platform's verified information.</p>
HTML;
    }

    /**
     * Pre-built prompts for common content tasks.
     */
    public function getPrompts(): array
    {
        return [
            'product_description_es' => [
                'name' => 'Descripción de producto (ES)',
                'icon' => 'heroicon-o-document-text',
                'prompt_template' => 'Escribe una descripción profesional del producto basada únicamente en la información proporcionada. No inventes características, prestaciones, certificaciones, precios ni garantías. Usa un tono claro y accesible. Datos confirmados del producto: {input}',
            ],
            'product_description_en' => [
                'name' => 'Product Description (EN)',
                'icon' => 'heroicon-o-language',
                'prompt_template' => 'Write a professional product description using only the supplied information. Do not invent features, performance claims, certifications, prices, or warranties. Use a clear and accessible tone. Verified product details: {input}',
            ],
            'blog_post_es' => [
                'name' => 'Artículo de blog (ES)',
                'icon' => 'heroicon-o-pencil-square',
                'prompt_template' => 'Escribe un artículo profesional sobre el siguiente tema. Separa los hechos confirmados de las recomendaciones y no inventes datos, referencias ni citas. Incluye introducción, subtítulos y conclusión en HTML limpio. Tema: {input}',
            ],
            'blog_post_en' => [
                'name' => 'Blog Post (EN)',
                'icon' => 'heroicon-o-pencil-square',
                'prompt_template' => 'Write a professional article on the following topic. Distinguish verified facts from recommendations and do not invent data, references, or quotes. Include an introduction, headings, and conclusion in clean HTML. Topic: {input}',
            ],
            'email_template_es' => [
                'name' => 'Plantilla de email (ES)',
                'icon' => 'heroicon-o-envelope',
                'prompt_template' => 'Redacta un correo profesional para el objetivo indicado. No inventes nombres, datos de contacto, compromisos ni detalles de la organización. Incluye marcadores donde falte información confirmada. Objetivo y contexto: {input}',
            ],
            'seo_meta_es' => [
                'name' => 'Meta tags SEO (ES)',
                'icon' => 'heroicon-o-magnifying-glass',
                'prompt_template' => 'Propón un meta title de hasta 60 caracteres y una meta description de hasta 160 caracteres, usando únicamente el contenido confirmado de la página. No inventes ubicación, productos ni prestaciones. Página: {input}',
            ],
            'whatsapp_message_es' => [
                'name' => 'Mensaje WhatsApp (ES)',
                'icon' => 'heroicon-o-chat-bubble-left',
                'prompt_template' => 'Redacta un mensaje profesional y breve para WhatsApp. No añadas nombres, teléfonos, enlaces, ofertas ni compromisos que no aparezcan en el texto proporcionado. Mensaje y datos confirmados: {input}',
            ],
            'technical_spec_es' => [
                'name' => 'Ficha técnica (ES)',
                'icon' => 'heroicon-o-document-check',
                'prompt_template' => 'Organiza los datos confirmados del producto en una ficha técnica. No infieras composición, rendimiento, aplicaciones, condiciones, seguridad ni almacenamiento; marca los campos faltantes como pendientes de confirmar. Datos: {input}',
            ],
            'translate_en_es' => [
                'name' => 'Traducir ES→EN',
                'icon' => 'heroicon-o-language',
                'prompt_template' => 'Translate the following text from Spanish to British English. Preserve its meaning and tone without adding facts. Text: {input}',
            ],
            'translate_es_en' => [
                'name' => 'Traducir EN→ES',
                'icon' => 'heroicon-o-language',
                'prompt_template' => 'Traduce el siguiente texto del inglés británico al español. Conserva su significado y tono sin añadir datos. Texto: {input}',
            ],
            'ask_products' => [
                'name' => 'Consultar información de la plataforma',
                'icon' => 'heroicon-o-chat-bubble-left-right',
                'prompt_template' => '{input}',
            ],
            'whatsapp_broadcast' => [
                'name' => 'Mensaje masivo WhatsApp',
                'icon' => 'heroicon-o-megaphone',
                'prompt_template' => 'Redacta un mensaje breve y profesional sobre el tema indicado. Usa solo hechos confirmados y evita contactos, ofertas o compromisos no proporcionados. Tema y contexto: {input}',
            ],
        ];
    }

    protected function callGroq(string $prompt, string $model, string $apiKey, float $temperature, int $maxTokens): array
    {
        $systemPrompt = $this->getSystemPrompt();
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(60)->post("{$this->groqBaseUrl}/chat/completions", [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? '',
                'model' => $model,
                'provider' => 'groq',
                'usage' => $data['usage'] ?? [],
            ];
        }

        return [
            'success' => false,
            'error' => 'Error de Groq: ' . ($response->json()['error']['message'] ?? $response->body()),
        ];
    }

    protected function callOpenAI(string $prompt, string $model, string $apiKey, float $temperature, int $maxTokens): array
    {
        $systemPrompt = $this->getSystemPrompt();
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? '',
                'model' => $model,
                'provider' => 'openai',
                'usage' => $data['usage'] ?? [],
            ];
        }

        return ['success' => false, 'error' => 'Error de OpenAI: ' . $response->body()];
    }

    protected function callAnthropic(string $prompt, string $model, string $apiKey, float $temperature, int $maxTokens): array
    {
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'content' => $data['content'][0]['text'] ?? '',
                'model' => $model,
                'provider' => 'anthropic',
                'usage' => $data['usage'] ?? [],
            ];
        }

        return ['success' => false, 'error' => 'Error de Anthropic: ' . $response->body()];
    }

    protected function callDeepSeek(string $prompt, string $model, string $apiKey, float $temperature, int $maxTokens): array
    {
        $systemPrompt = $this->getSystemPrompt();
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.deepseek.com/chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'success' => true,
                'content' => $data['choices'][0]['message']['content'] ?? '',
                'model' => $model,
                'provider' => 'deepseek',
                'usage' => $data['usage'] ?? [],
            ];
        }

        return ['success' => false, 'error' => 'Error de DeepSeek: ' . $response->body()];
    }
}
