<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Setting;
use App\Services\AiAssistantService;
use App\Services\PlatformModules;
use Illuminate\Support\Facades\Session;

class AiChatWidget extends Component
{
    public bool $isOpen = false;
    public string $message = '';
    public array $messages = [];
    public bool $isLoading = false;

    // Settings from admin
    public bool $enabled = false;
    public string $provider = 'groq';
    public string $apiKey = '';
    public string $model = 'llama-3.1-8b-instant';
    public string $welcomeMessage = '';

    protected AiAssistantService $aiService;

    public function mount(): void
    {
        $this->enabled = Setting::get('ai_enabled', '0') === '1';
        $this->provider = Setting::get('ai_provider', 'groq');
        $this->apiKey = Setting::get('ai_api_key', '');
        $this->model = Setting::get('ai_model', 'llama-3.1-8b-instant');
        $this->welcomeMessage = Setting::translated('ai_welcome_message', __('Hello, I am your virtual assistant. How can I help you?'));
    }

    public function boot(): void
    {
        $this->aiService = app(AiAssistantService::class);
    }

    public function getStatus(): string
    {
        if (! $this->enabled) {
            return 'disabled';
        }

        return filled($this->apiKey) ? 'ready' : 'demo';
    }

    public function toggle(): void
    {
        $this->isOpen = !$this->isOpen;

        // Add welcome message if first open
        if ($this->isOpen && empty($this->messages)) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $this->welcomeMessage ?: $this->welcomeText(Session::get('locale', app()->getLocale())),
            ];
        }
    }

    public function send(): void
    {
        if (empty(trim($this->message))) {
            return;
        }

        // Check if AI is enabled and API key exists
        if (!$this->enabled || empty($this->apiKey)) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $this->getLocalResponse($this->message),
            ];
            $this->message = '';
            return;
        }

        $userMessage = trim($this->message);
        $this->messages[] = ['role' => 'user', 'content' => $userMessage];
        $this->message = '';
        $this->isLoading = true;

        // Build a product-aware prompt
        $systemContext = $this->aiService->buildKnowledgeBase();
        $locale = Session::get('locale', app()->getLocale());
        $langInstruction = $this->languageInstruction($locale);
        $platformName = config('platform.public_name');
        $commerceInstruction = app(PlatformModules::class)->enabled('products')
            ? 'The product catalog is enabled; answer product questions only from the provided knowledge base.'
            : 'The product catalog is disabled; do not mention products, prices, categories, or the online shop.';

        $fullPrompt = <<<PROMPT
{$systemContext}

INSTRUCCIONES:
- Actúa como asistente de {$platformName}.
- Usa únicamente la información disponible en la base de conocimiento; no inventes datos comerciales ni de contacto.
- {$commerceInstruction}
- {$langInstruction}
- Si no tienes la información, dirige al usuario al formulario de contacto.
- Sé breve, amable y profesional.

PREGUNTA DEL CLIENTE: {$userMessage}
PROMPT;

        $result = $this->aiService->generate(
            prompt: $fullPrompt,
            provider: $this->provider,
            model: $this->model,
            apiKey: $this->apiKey,
            temperature: 0.7,
            maxTokens: 1024,
        );

        $this->isLoading = false;

        if ($result['success']) {
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $result['content'],
            ];
        } else {
            // Fallback to local knowledge
            $this->messages[] = [
                'role' => 'assistant',
                'content' => $this->getLocalResponse($userMessage),
            ];
        }
    }

    protected function getLocalResponse(string $question): string
    {
        $question = mb_strtolower($question);
        $isSpanish = app()->getLocale() === 'es';
        $productsEnabled = app(PlatformModules::class)->enabled('products');

        if ($productsEnabled) {
            $products = \App\Models\Product::active()->with('category')->get();
            $matchedProducts = $products->filter(fn ($product) =>
                str_contains(mb_strtolower($product->name), $question)
                || str_contains(mb_strtolower($product->category?->name ?? ''), $question)
            );

            if ($matchedProducts->isNotEmpty()) {
                $list = $matchedProducts->take(5)->map(function ($product) {
                    $categoryName = $product->category?->name ?? '';

                    return "• <strong>{$product->name}</strong> ({$categoryName}) - {$product->getPriceFormatted()}";
                })->implode("\n");

                return ($isSpanish ? "Productos disponibles:\n\n" : "Available products:\n\n")
                    . $list . "\n\n<a href='" . route('shop.index') . "'>"
                    . ($isSpanish ? 'Ver catálogo' : 'View catalog') . '</a>';
            }
        }

        if (str_contains($question, 'precio') || str_contains($question, 'price') || str_contains($question, 'coste')) {
            $quoteUrl = route('quote.create');

            return $productsEnabled
                ? ($isSpanish
                    ? "Consulta los precios actualizados en la <a href='" . route('shop.index') . "'>tienda</a> o solicita un <a href='{$quoteUrl}'>presupuesto</a>."
                    : "Check current prices in the <a href='" . route('shop.index') . "'>store</a> or request an <a href='{$quoteUrl}'>estimate</a>.")
                : ($isSpanish
                    ? "El catálogo de productos está desactivado. Puedes solicitar un <a href='{$quoteUrl}'>presupuesto</a>."
                    : "The product catalog is disabled. You can request an <a href='{$quoteUrl}'>estimate</a>.");
        }

        if (str_contains($question, 'contact') || str_contains($question, 'teléfono') || str_contains($question, 'email') || str_contains($question, 'whatsapp')) {
            $details = collect([
                'Phone' => Setting::get('site_phone'),
                'Email' => Setting::get('site_email'),
                'Address' => Setting::get('site_address'),
            ])->filter();

            if ($details->isEmpty()) {
                $contactUrl = route('contact');

                return $isSpanish
                    ? "Los datos de contacto aún no están configurados. Puedes usar el <a href='{$contactUrl}'>formulario de contacto</a>."
                    : "Contact details are not configured yet. You can use the <a href='{$contactUrl}'>contact form</a>.";
            }

            return '<strong>' . ($isSpanish ? 'Contacto' : 'Contact') . "</strong>\n\n"
                . $details->map(fn ($value, $label) => "{$label}: {$value}")->implode("\n");
        }

        return $isSpanish
            ? 'Puedo ayudarte con la gestión de proyectos, mediciones, presupuestos y órdenes de trabajo. ¿Qué necesitas saber?'
            : 'I can help with projects, measurements, estimates, and work orders. What would you like to know?';
    }

    protected function languageName(string $locale): string
    {
        return match ($locale) {
            'es' => 'español',
            'en' => 'inglés',
            'de' => 'alemán',
            'it' => 'italiano',
            'fr' => 'francés',
            'pt' => 'portugués',
            'nl' => 'neerlandés',
            default => 'español',
        };
    }

    protected function languageInstruction(string $locale): string
    {
        return match ($locale) {
            'es' => 'Responde SIEMPRE en español, que es el idioma actual del sitio web, sin importar el idioma en que escriba el usuario.',
            'en' => 'ALWAYS reply in English, which is the current language of the website, regardless of the language the user writes in.',
            'de' => 'Antworte IMMER auf Deutsch, das ist die aktuelle Sprache der Webseite, unabhängig von der Sprache der Frage.',
            'it' => 'Rispondi SEMPRE in italiano, che è la lingua attuale del sito web, indipendentemente dalla lingua della domanda.',
            'fr' => 'Réponds TOUJOURS en français, qui est la langue actuelle du site web, quel que soit la langue utilisée par l\'utilisateur.',
            'pt' => 'Responda SEMPRE em português, que é o idioma atual do site, independentemente do idioma em que o utilizador escreve.',
            'nl' => 'Antwoord ALTIJD in het Nederlands, dat is de huidige taal van de website, ongeacht de taal waarin de gebruiker schrijft.',
            default => 'Responde SIEMPRE en español, que es el idioma actual del sitio web, sin importar el idioma en que escriba el usuario.',
        };
    }

    protected function welcomeText(string $locale): string
    {
        $platformName = config('platform.public_name');

        return match ($locale) {
            'en' => "👋 Hi! I'm the assistant for <strong>{$platformName}</strong>.\n\nI can help with projects, measurements, estimates, and work orders.\n\nHow can I help?",
            'de' => "👋 Hallo! Ich bin der Assistent von <strong>{$platformName}</strong>.\n\nIch helfe bei Projekten, Messungen, Kostenvoranschlägen und Arbeitsaufträgen.\n\nWie kann ich helfen?",
            'it' => "👋 Ciao! Sono l'assistente di <strong>{$platformName}</strong>.\n\nPosso aiutarti con progetti, misurazioni, preventivi e ordini di lavoro.\n\nCome posso aiutarti?",
            'fr' => "👋 Bonjour ! Je suis l'assistant de <strong>{$platformName}</strong>.\n\nJe peux vous aider avec les projets, les mesures, les devis et les ordres de travail.\n\nComment puis-je vous aider ?",
            'pt' => "👋 Olá! Sou o assistente de <strong>{$platformName}</strong>.\n\nPosso ajudar com projetos, medições, orçamentos e ordens de trabalho.\n\nComo posso ajudar?",
            'nl' => "👋 Hallo! Ik ben de assistent van <strong>{$platformName}</strong>.\n\nIk kan helpen met projecten, metingen, offertes en werkorders.\n\nHoe kan ik helpen?",
            default => "👋 Hola, soy el asistente de <strong>{$platformName}</strong>.\n\nPuedo ayudarte con proyectos, mediciones, presupuestos y órdenes de trabajo.\n\n¿En qué puedo ayudarte?",
        };
    }

    public function render()
    {
        return view('livewire.ai-chat-widget');
    }
}
