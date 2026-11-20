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

class EditServices extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $slug = 'content/services';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Contenido');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.Editar Servicios');
    }

    public function getTitle(): string
    {
        return __('admin.Contenido — Servicios');
    }

    protected static string $view = 'filament.pages.edit-services';

    public ?string $locale = 'es';
    public ?array $data = [];

    /** Store original image paths from DB (for saving when no new upload) */
    public ?string $originalResinImage = null;
    public ?string $originalGraniteImage = null;

    public function mount(): void
    {
        $this->locale = app()->getLocale();
        $this->loadData();
    }

    /**
     * Load content blocks from DB into $this->data.
     * FileUpload fields ALWAYS get null — images are stored separately.
     */
    protected function loadData(): void
    {
        $L = $this->locale;

        // Store original image paths for save logic
        $this->originalResinImage = ContentBlock::get('services', 'resin', 'image', $L, '') ?: null;
        $this->originalGraniteImage = ContentBlock::get('services', 'granite', 'image', $L, '') ?: null;

        $this->data = [
            'locale'         => $L,

            // Hero
            'hero_title'     => ContentBlock::get('services', 'hero', 'title', $L, ''),
            'hero_subtitle'  => ContentBlock::get('services', 'hero', 'subtitle', $L, ''),

            // Resin
            'resin_badge'    => ContentBlock::get('services', 'resin', 'badge', $L, ''),
            'resin_title'    => ContentBlock::get('services', 'resin', 'title', $L, ''),
            'resin_p1'       => ContentBlock::get('services', 'resin', 'p1', $L, ''),
            'resin_p2'       => ContentBlock::get('services', 'resin', 'p2', $L, ''),
            'resin_content'  => ContentBlock::get('services', 'resin', 'content', $L, ''),
            'resin_image'    => null, // CRITICAL: FileUpload must always receive null
            'resin_alt'      => ContentBlock::get('services', 'resin', 'alt', $L, ''),

            // Granite
            'granite_badge'  => ContentBlock::get('services', 'granite', 'badge', $L, ''),
            'granite_title'  => ContentBlock::get('services', 'granite', 'title', $L, ''),
            'granite_p1'     => ContentBlock::get('services', 'granite', 'p1', $L, ''),
            'granite_p2'     => ContentBlock::get('services', 'granite', 'p2', $L, ''),
            'granite_content'=> ContentBlock::get('services', 'granite', 'content', $L, ''),
            'granite_image'  => null, // CRITICAL: FileUpload must always receive null
            'granite_alt'    => ContentBlock::get('services', 'granite', 'alt', $L, ''),

            // CTA
            'cta_title'      => ContentBlock::get('services', 'cta', 'title', $L, ''),
            'cta_text'       => ContentBlock::get('services', 'cta', 'text', $L, ''),
            'cta_button_text'=> ContentBlock::get('services', 'cta', 'button_text', $L, ''),
        ];
    }

    /**
     * When locale select changes, reload all data from DB for the new language.
     */
    public function updatedLocale(): void
    {
        $this->loadData();
    }

    /**
     * Create a FileUpload component with afterStateHydrated to prevent
     * the "foreach() argument must be of type array|object, string given" error.
     *
     * Filament's BaseFileUpload::getUploadedFiles() does foreach($this->getState() ?? []).
     * When Livewire rehydrates from snapshot, the state can be a string (file path),
     * which causes the foreach to crash. This callback converts strings to null.
     */
    protected function makeImageUpload(string $key): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make($key)
            ->label('Imagen del servicio')
            ->image()
            ->disk('public')
            ->directory('content-blocks/services')
            ->imagePreviewHeight('200')
            ->maxSize(10240)
            ->afterStateHydrated(function (Forms\Components\FileUpload $component, $state): void {
                // If Livewire snapshot restored a string path, convert to null
                // to prevent BaseFileUpload::getUploadedFiles() foreach crash
                if (is_string($state)) {
                    $component->state(null);
                }
            });
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Idioma')
                    ->schema([
                        Forms\Components\Select::make('locale')
                            ->label('Seleccionar idioma')
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
                    ->description('Título y subtítulo que aparecen en la parte superior de la página.')
                    ->schema([
                        Forms\Components\TextInput::make('hero_title')
                            ->label('Título principal')
                            ->placeholder('Servicios de Instalación')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('hero_subtitle')
                            ->label('Subtítulo')
                            ->placeholder('Instalación profesional de pavimentos en resina epóxica y chips de granito en toda España')
                            ->columnSpanFull(),
                    ])->collapsible(),

                // ── Resin ──
                Forms\Components\Section::make('🧪 Servicio 1 — Instalación de Resina Epóxica')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('resin_badge')
                                ->label('Etiqueta (badge)')
                                ->default('Servicio 1'),
                            Forms\Components\TextInput::make('resin_title')
                                ->label('Título del servicio')
                                ->placeholder('Instalación de Pavimento de Resina Epóxica')
                                ->columnSpanFull(),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('resin_p1')
                                ->label('Párrafo 1')
                                ->placeholder('Nuestro equipo de profesionales instala pavimentos de resina epóxica...')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('resin_p2')
                                ->label('Párrafo 2')
                                ->placeholder('Utilizamos resinas de poliuretano y epoxi fabricadas en nuestra planta...')
                                ->columnSpanFull(),
                        ]),
                        Forms\Components\RichEditor::make('resin_content')
                            ->label('Contenido completo (opcional — reemplaza los párrafos si se completa)')
                            ->helperText('Si dejas este campo vacío, se usarán los párrafos de arriba.')
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)->schema([
                            $this->makeImageUpload('resin_image'),
                            Forms\Components\TextInput::make('resin_alt')
                                ->label('Texto alternativo imagen (alt)')
                                ->placeholder('Instalación de resina epóxica'),
                        ]),
                    ])->collapsible(),

                // ── Granite ──
                Forms\Components\Section::make('💎 Servicio 2 — Spray Granite / Áridos Decorativos')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\TextInput::make('granite_badge')
                                ->label('Etiqueta (badge)')
                                ->default('Servicio 2'),
                            Forms\Components\TextInput::make('granite_title')
                                ->label('Título del servicio')
                                ->placeholder('Spray Granite / Áridos Decorativos')
                                ->columnSpanFull(),
                        ]),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('granite_p1')
                                ->label('Párrafo 1')
                                ->placeholder('Tecnología de pulverización de chips de granito...')
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('granite_p2')
                                ->label('Párrafo 2')
                                ->placeholder('Disponible en más de 20 colores de áridos naturales.')
                                ->columnSpanFull(),
                        ]),
                        Forms\Components\RichEditor::make('granite_content')
                            ->label('Contenido completo (opcional — reemplaza los párrafos si se completa)')
                            ->helperText('Si dejas este campo vacío, se usarán los párrafos de arriba.')
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)->schema([
                            $this->makeImageUpload('granite_image'),
                            Forms\Components\TextInput::make('granite_alt')
                                ->label('Texto alternativo imagen (alt)')
                                ->placeholder('Chips de granito'),
                        ]),
                    ])->collapsible(),

                // ── CTA ──
                Forms\Components\Section::make('📞 Call to Action — Fondo inferior')
                    ->description('Sección de llamada a la acción al final de la página.')
                    ->schema([
                        Forms\Components\TextInput::make('cta_title')
                            ->label('Título CTA')
                            ->placeholder('¿Solicitar una cotización?'),
                        Forms\Components\TextInput::make('cta_text')
                            ->label('Texto descriptivo')
                            ->placeholder('Rellene el formulario de cotización...')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('cta_button_text')
                            ->label('Texto del botón')
                            ->placeholder('Solicitar cotización gratuita'),
                    ])->collapsible(),
            ])
            ->statePath('data');
    }

    /**
     * Switch language: update locale, reload data, and force Livewire re-render.
     */
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
            ['resin',   'badge',       $data['resin_badge'] ?? '', 'text'],
            ['resin',   'title',       $data['resin_title'] ?? '', 'text'],
            ['resin',   'p1',          $data['resin_p1'] ?? '', 'text'],
            ['resin',   'p2',          $data['resin_p2'] ?? '', 'text'],
            ['resin',   'content',     $data['resin_content'] ?? '', 'richtext'],
            ['resin',   'alt',         $data['resin_alt'] ?? '', 'text'],
            ['granite', 'badge',       $data['granite_badge'] ?? '', 'text'],
            ['granite', 'title',       $data['granite_title'] ?? '', 'text'],
            ['granite', 'p1',          $data['granite_p1'] ?? '', 'text'],
            ['granite', 'p2',          $data['granite_p2'] ?? '', 'text'],
            ['granite', 'content',     $data['granite_content'] ?? '', 'richtext'],
            ['granite', 'alt',         $data['granite_alt'] ?? '', 'text'],
            ['cta',     'title',       $data['cta_title'] ?? '', 'text'],
            ['cta',     'text',        $data['cta_text'] ?? '', 'text'],
            ['cta',     'button_text', $data['cta_button_text'] ?? '', 'text'],
        ];

        // Capture pre-save values of the source locale (detects verbatim copies)
        $oldSources = [];
        foreach ($blocks as [$section, $key]) {
            $oldSources["{$section}.{$key}"] = ContentBlock::where('page', 'services')
                ->where('section', $section)
                ->where('key', $key)
                ->where('locale', $L)
                ->value('value');
        }

        // STEP 1: Save in current locale
        foreach ($blocks as [$section, $key, $value, $type]) {
            ContentBlock::set('services', $section, $key, $value, $type, $L);
        }

        // STEP 2: Propagate to all other locales — fills blanks and AI-translates
        // (Groq) any locale still holding a verbatim copy of the old/new source.
        foreach ($blocks as [$section, $key, $value, $type]) {
            if (! in_array($type, ['text', 'richtext'], true) || trim((string) $value) === '') {
                continue;
            }
            $block = new ContentBlock();
            $block->page = 'services';
            $block->section = $section;
            $block->key = $key;
            $block->value = $value;
            $block->type = $type;
            $block->locale = $L;
            $block->propagateTranslations($oldSources["{$section}.{$key}"] ?? null);
        }

        // Images: save new upload if present, otherwise keep original
        foreach (['resin', 'granite'] as $section) {
            $field = "{$section}_image";
            $newFile = $data[$field] ?? null;

            if (!blank($newFile) && is_string($newFile)) {
                // Save for all locales (images are language-independent)
                foreach (AutoTranslate::otherLocales($L) as $otherL) {
                    ContentBlock::set('services', $section, 'image', $newFile, 'image', $otherL);
                }
                ContentBlock::set('services', $section, 'image', $newFile, 'image', $L);
            }
        }

        ContentBlock::clearPageCache('services');

        $translatedCount = count(AutoTranslate::otherLocales($L));
        Notification::make()
            ->title("Contenido de Servicios guardado y traducido a {$translatedCount} idiomas")
            ->success()
            ->send();
    }
}
