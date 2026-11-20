<?php

namespace App\Filament\Pages;

use App\Models\ContentBlock;
use App\Services\AutoTranslate;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EditAbout extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.edit-about';

    public ?string $locale = 'es';
    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Contenido');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.Editar About Us');
    }

    public function getTitle(): string
    {
        return __('admin.Contenido — About Us');
    }

    public function mount(): void
    {
        $this->locale = app()->getLocale();
        $this->loadData();
    }

    protected function loadData(): void
    {
        $L = $this->locale;

        $this->data = [
            'locale'            => $L,

            // Hero
            'hero_title'        => ContentBlock::get('about', 'hero', 'title', $L, ''),
            'hero_subtitle'     => ContentBlock::get('about', 'hero', 'subtitle', $L, ''),

            // Story
            'story_title'       => ContentBlock::get('about', 'story', 'title', $L, ''),
            'story_content'     => ContentBlock::get('about', 'story', 'content', $L, ''),
            'story_image'       => null, // CRITICAL: FileUpload must always receive null

            // Values
            'values_title'      => ContentBlock::get('about', 'values', 'title', $L, ''),
            'value_1_title'     => ContentBlock::get('about', 'values', 'value_1_title', $L, ''),
            'value_1_text'      => ContentBlock::get('about', 'values', 'value_1_text', $L, ''),
            'value_1_icon'      => ContentBlock::get('about', 'values', 'value_1_icon', $L, 'building-office-2'),
            'value_2_title'     => ContentBlock::get('about', 'values', 'value_2_title', $L, ''),
            'value_2_text'      => ContentBlock::get('about', 'values', 'value_2_text', $L, ''),
            'value_2_icon'      => ContentBlock::get('about', 'values', 'value_2_icon', $L, 'truck'),
            'value_3_title'     => ContentBlock::get('about', 'values', 'value_3_title', $L, ''),
            'value_3_text'      => ContentBlock::get('about', 'values', 'value_3_text', $L, ''),
            'value_3_icon'      => ContentBlock::get('about', 'values', 'value_3_icon', $L, 'wrench'),

            // CTA
            'cta_title'         => ContentBlock::get('about', 'cta', 'title', $L, ''),
            'cta_subtitle'      => ContentBlock::get('about', 'cta', 'subtitle', $L, ''),
            'cta_button_text'   => ContentBlock::get('about', 'cta', 'button_text', $L, ''),
        ];
    }

    public function updatedLocale(): void
    {
        $this->loadData();
    }

    protected function makeImageUpload(string $key, string $directory = 'content-blocks/about'): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make($key)
            ->label('Imagen')
            ->image()
            ->disk('public')
            ->directory($directory)
            ->imagePreviewHeight('200')
            ->maxSize(10240)
            ->afterStateHydrated(function (Forms\Components\FileUpload $component, $state): void {
                if (is_string($state)) {
                    $component->state(null);
                }
            });
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('admin.Idioma'))
                    ->schema([
                        Forms\Components\Select::make('locale')
                            ->label(__('admin.Idioma'))
                            ->options([
                                'es' => '🇪🇸 Español',
                                'en' => '🇬🇧 English',
                                'de' => '🇩🇪 Deutsch',
                                'it' => '🇮🇹 Italiano',
                                'fr' => '🇫🇷 Français',
                                'pt' => '🇵🇹 Português',
                                'nl' => '🇳🇱 Nederlands',
                            ])
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state) => $this->switchLocale($state)),
                    ])->collapsible(),

                // ── Hero ──
                Forms\Components\Section::make('🌟 Hero — Cabecera')
                    ->description('Título principal de la página About Us.')
                    ->schema([
                        Forms\Components\TextInput::make('hero_title')
                            ->label('Título principal')
                            ->placeholder('Sobre Nosotros')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('hero_subtitle')
                            ->label('Subtítulo')
                            ->placeholder('House Roof — Limpieza, mantenimiento y reparación de techos en Europa...')
                            ->columnSpanFull(),
                    ])->collapsible(),

                // ── Story ──
                Forms\Components\Section::make('📖 Nuestra Historia')
                    ->description('Sección principal con la historia de la empresa.')
                    ->schema([
                        Forms\Components\TextInput::make('story_title')
                            ->label('Título de la sección')
                            ->placeholder('Nuestra Historia')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('story_content')
                            ->label('Contenido de la historia')
                            ->placeholder('House Roof es una empresa especializada en el cuidado de cubiertas en Europa...')
                            ->columnSpanFull(),
                        $this->makeImageUpload('story_image'),
                    ])->collapsible(),

                // ── Values ──
                Forms\Components\Section::make('💎 Por Qué Elegirnos')
                    ->description('Tres valores o razones principales.')
                    ->schema([
                        Forms\Components\TextInput::make('values_title')
                            ->label('Título de la sección')
                            ->placeholder('Por Qué Elegirnos')
                            ->columnSpanFull(),

                        // Value 1
                        Forms\Components\Section::make('Valor 1')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('value_1_title')
                                        ->label('Título')
                                        ->placeholder('Fabricación Propia'),
                                    Forms\Components\TextInput::make('value_1_icon')
                                        ->label('Ícono (heroicon)')
                                        ->placeholder('building-office-2')
                                        ->helperText('Nombre del heroicon sin prefijo. Ej: building-office-2, truck, wrench'),
                                ]),
                                Forms\Components\TextInput::make('value_1_text')
                                    ->label('Descripción')
                                    ->placeholder('Todos nuestros productos se fabrican en nuestra planta...')
                                    ->columnSpanFull(),
                            ])->compact()->collapsible(),

                        // Value 2
                        Forms\Components\Section::make('Valor 2')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('value_2_title')
                                        ->label('Título')
                                        ->placeholder('Envío Europeo'),
                                    Forms\Components\TextInput::make('value_2_icon')
                                        ->label('Ícono (heroicon)')
                                        ->placeholder('truck'),
                                ]),
                                Forms\Components\TextInput::make('value_2_text')
                                    ->label('Descripción')
                                    ->placeholder('Entregamos en toda la Unión Europea...')
                                    ->columnSpanFull(),
                            ])->compact()->collapsible(),

                        // Value 3
                        Forms\Components\Section::make('Valor 3')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('value_3_title')
                                        ->label('Título')
                                        ->placeholder('Instalación Profesional'),
                                    Forms\Components\TextInput::make('value_3_icon')
                                        ->label('Ícono (heroicon)')
                                        ->placeholder('wrench'),
                                ]),
                                Forms\Components\TextInput::make('value_3_text')
                                    ->label('Descripción')
                                    ->placeholder('Nuestro equipo experto revisa y repara cubiertas...')
                                    ->columnSpanFull(),
                            ])->compact()->collapsible(),
                    ])->collapsible(),

                // ── CTA ──
                Forms\Components\Section::make('📞 Call to Action')
                    ->description('Sección de llamada a la acción al final de la página.')
                    ->schema([
                        Forms\Components\TextInput::make('cta_title')
                            ->label('Título CTA')
                            ->placeholder('¿Necesita un presupuesto para su proyecto?'),
                        Forms\Components\TextInput::make('cta_subtitle')
                            ->label('Texto descriptivo')
                            ->placeholder('Solicite una cotización o contáctenos...')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cta_button_text')
                            ->label('Texto del botón')
                            ->placeholder('Solicitar cotización'),
                    ])->collapsible(),
            ])
            ->statePath('data');
    }

    public function switchLocale(string $newLocale): void
    {
        $this->locale = $newLocale;
        $this->loadData();
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $L = $data['locale'] ?? $this->locale ?? 'es';

        $blocks = [
            ['hero',    'title',       $data['hero_title'] ?? '', 'text'],
            ['hero',    'subtitle',    $data['hero_subtitle'] ?? '', 'text'],
            ['story',   'title',       $data['story_title'] ?? '', 'text'],
            ['story',   'content',     $data['story_content'] ?? '', 'richtext'],
            ['values',  'title',       $data['values_title'] ?? '', 'text'],
            ['values',  'value_1_title', $data['value_1_title'] ?? '', 'text'],
            ['values',  'value_1_text',  $data['value_1_text'] ?? '', 'text'],
            ['values',  'value_1_icon',  $data['value_1_icon'] ?? 'building-office-2', 'text'],
            ['values',  'value_2_title', $data['value_2_title'] ?? '', 'text'],
            ['values',  'value_2_text',  $data['value_2_text'] ?? '', 'text'],
            ['values',  'value_2_icon',  $data['value_2_icon'] ?? 'truck', 'text'],
            ['values',  'value_3_title', $data['value_3_title'] ?? '', 'text'],
            ['values',  'value_3_text',  $data['value_3_text'] ?? '', 'text'],
            ['values',  'value_3_icon',  $data['value_3_icon'] ?? 'wrench', 'text'],
            ['cta',     'title',       $data['cta_title'] ?? '', 'text'],
            ['cta',     'subtitle',    $data['cta_subtitle'] ?? '', 'text'],
            ['cta',     'button_text', $data['cta_button_text'] ?? '', 'text'],
        ];

        // Capture pre-save values of the source locale (detects verbatim copies)
        $oldSources = [];
        foreach ($blocks as [$section, $key]) {
            $oldSources["{$section}.{$key}"] = ContentBlock::where('page', 'about')
                ->where('section', $section)
                ->where('key', $key)
                ->where('locale', $L)
                ->value('value');
        }

        // STEP 1: Save in current locale
        foreach ($blocks as [$section, $key, $value, $type]) {
            ContentBlock::set('about', $section, $key, $value, $type, $L);
        }

        // STEP 2: Propagate to all other locales — fills blanks and AI-translates
        // (Groq) any locale still holding a verbatim copy of the old/new source.
        foreach ($blocks as [$section, $key, $value, $type]) {
            if (! in_array($type, ['text', 'richtext'], true) || trim((string) $value) === '') {
                continue;
            }
            $block = new ContentBlock();
            $block->page = 'about';
            $block->section = $section;
            $block->key = $key;
            $block->value = $value;
            $block->type = $type;
            $block->locale = $L;
            $block->propagateTranslations($oldSources["{$section}.{$key}"] ?? null);
        }

        // Image: only save if a new file was uploaded (same for all locales)
        $storyImage = $data['story_image'] ?? null;
        if (!blank($storyImage) && is_string($storyImage)) {
            foreach (AutoTranslate::otherLocales($L) as $otherL) {
                ContentBlock::set('about', 'story', 'image', $storyImage, 'image', $otherL);
            }
            ContentBlock::set('about', 'story', 'image', $storyImage, 'image', $L);
        }

        ContentBlock::clearPageCache('about');

        $translatedCount = count(AutoTranslate::otherLocales($L));
        Notification::make()
            ->title("Contenido de About Us guardado y traducido a {$translatedCount} idiomas")
            ->success()
            ->send();
    }
}
