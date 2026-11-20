<?php

namespace App\Filament\Pages;

use App\Services\AiAssistantService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class AiAssistant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Configuración');
    }
    protected static ?int $navigationSort = 9;
    public function getTitle(): string
    {
        return __('admin.AI Assistant');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Asistente IA');
    }
    protected static string $view = 'filament.pages.ai-assistant';

    public ?string $selectedProvider = 'groq';
    public ?string $selectedModel = 'llama-3.3-70b-versatile';
    public ?string $selectedPrompt = null;
    public ?string $userInput = '';
    public ?string $aiResponse = '';
    public bool $isGenerating = false;
    public float $temperature = 0.7;
    public int $maxTokens = 2048;

    protected AiAssistantService $aiService;

    public function boot(): void
    {
        $this->aiService = app(AiAssistantService::class);
    }

    public function getProviders(): array
    {
        return $this->aiService->getProviders();
    }

    public function getModels(): array
    {
        $providers = $this->aiService->getProviders();
        return $providers[$this->selectedProvider]['models'] ?? [];
    }

    public function getPrompts(): array
    {
        return $this->aiService->getPrompts();
    }

    public function updatedSelectedProvider(): void
    {
        $models = $this->getModels();
        $this->selectedModel = array_key_first($models);
    }

    public function generate(): void
    {
        if (empty($this->userInput)) {
            Notification::make()
                ->title('Introduzca un texto')
                ->warning()
                ->send();
            return;
        }

        $this->isGenerating = true;

        $prompts = $this->getPrompts();
        $promptTemplate = $prompts[$this->selectedPrompt]['prompt_template'] ?? '{input}';
        $finalPrompt = str_replace('{input}', $this->userInput, $promptTemplate);

        $result = $this->aiService->generate(
            prompt: $finalPrompt,
            provider: $this->selectedProvider,
            model: $this->selectedModel,
            temperature: $this->temperature,
            maxTokens: $this->maxTokens,
        );

        $this->isGenerating = false;

        if ($result['success']) {
            $this->aiResponse = $result['content'];

            Notification::make()
                ->title('Contenido generado exitosamente')
                ->success()
                ->body("Modelo: {$result['model']} | Proveedor: {$result['provider']}")
                ->send();
        } else {
            Notification::make()
                ->title('Error al generar contenido')
                ->danger()
                ->body($result['error'])
                ->persistent()
                ->send();
        }
    }

    public function copyToClipboard(): void
    {
        // This will be handled via Alpine.js in the view
    }
}
