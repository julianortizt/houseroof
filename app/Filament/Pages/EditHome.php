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

class EditHome extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $slug = 'content/home';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Contenido');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.Editar Inicio');
    }

    public function getTitle(): string
    {
        return __('admin.Contenido — Inicio');
    }

    protected static string $view = 'filament.pages.edit-home';

    public ?string $locale = 'es';
    public ?array $data = [];

    protected function loadData(): void
    {
        $L = $this->locale;

        $this->data = [
            'locale' => $L,

            // ── Hero Slider (3 slides) ──
            'slide_1_badge'    => ContentBlock::get('home', 'hero', 'slide_1_badge', $L, ''),
            'slide_1_title'    => ContentBlock::get('home', 'hero', 'slide_1_title', $L, ''),
            'slide_1_subtitle' => ContentBlock::get('home', 'hero', 'slide_1_subtitle', $L, ''),
            'slide_1_image'    => null,
            'slide_1_cta_text' => ContentBlock::get('home', 'hero', 'slide_1_cta_text', $L, ''),
            'slide_1_cta_url'  => ContentBlock::get('home', 'hero', 'slide_1_cta_url', $L, ''),

            'slide_2_badge'    => ContentBlock::get('home', 'hero', 'slide_2_badge', $L, ''),
            'slide_2_title'    => ContentBlock::get('home', 'hero', 'slide_2_title', $L, ''),
            'slide_2_subtitle' => ContentBlock::get('home', 'hero', 'slide_2_subtitle', $L, ''),
            'slide_2_image'    => null,
            'slide_2_cta_text' => ContentBlock::get('home', 'hero', 'slide_2_cta_text', $L, ''),
            'slide_2_cta_url'  => ContentBlock::get('home', 'hero', 'slide_2_cta_url', $L, ''),

            'slide_3_badge'    => ContentBlock::get('home', 'hero', 'slide_3_badge', $L, ''),
            'slide_3_title'    => ContentBlock::get('home', 'hero', 'slide_3_title', $L, ''),
            'slide_3_subtitle' => ContentBlock::get('home', 'hero', 'slide_3_subtitle', $L, ''),
            'slide_3_image'    => null,
            'slide_3_cta_text' => ContentBlock::get('home', 'hero', 'slide_3_cta_text', $L, ''),
            'slide_3_cta_url'  => ContentBlock::get('home', 'hero', 'slide_3_cta_url', $L, ''),

            // ── Trust Bar ──
            'trust_item_1' => ContentBlock::get('home', 'trust_bar', 'item_1', $L, ''),
            'trust_item_2' => ContentBlock::get('home', 'trust_bar', 'item_2', $L, ''),
            'trust_item_3' => ContentBlock::get('home', 'trust_bar', 'item_3', $L, ''),
            'trust_item_4' => ContentBlock::get('home', 'trust_bar', 'item_4', $L, ''),

            // ── Value Proposition ──
            'vp_title'       => ContentBlock::get('home', 'value_proposition', 'title', $L, ''),
            'vp_subtitle'    => ContentBlock::get('home', 'value_proposition', 'subtitle', $L, ''),
            'vp_value_1_title' => ContentBlock::get('home', 'value_proposition', 'value_1_title', $L, ''),
            'vp_value_1_text'  => ContentBlock::get('home', 'value_proposition', 'value_1_text', $L, ''),
            'vp_value_2_title' => ContentBlock::get('home', 'value_proposition', 'value_2_title', $L, ''),
            'vp_value_2_text'  => ContentBlock::get('home', 'value_proposition', 'value_2_text', $L, ''),
            'vp_value_3_title' => ContentBlock::get('home', 'value_proposition', 'value_3_title', $L, ''),
            'vp_value_3_text'  => ContentBlock::get('home', 'value_proposition', 'value_3_text', $L, ''),

            // ── Services Section ──
            'services_section_title'    => ContentBlock::get('home', 'services_section', 'section_title', $L, ''),
            'services_section_subtitle' => ContentBlock::get('home', 'services_section', 'section_subtitle', $L, ''),
            'resin_title'   => ContentBlock::get('home', 'services_section', 'resin_title', $L, ''),
            'resin_desc'    => ContentBlock::get('home', 'services_section', 'resin_description', $L, ''),
            'resin_image'   => null,
            'granite_title' => ContentBlock::get('home', 'services_section', 'granite_title', $L, ''),
            'granite_desc'  => ContentBlock::get('home', 'services_section', 'granite_description', $L, ''),
            'granite_image' => null,

            // ── CTA ──
            'cta_title'       => ContentBlock::get('home', 'cta', 'title', $L, ''),
            'cta_subtitle'    => ContentBlock::get('home', 'cta', 'subtitle', $L, ''),
            'cta_button_text' => ContentBlock::get('home', 'cta', 'button_text', $L, ''),
        ];
    }

    public function updatedLocale(): void
    {
        $this->loadData();
    }

    protected function makeImageUpload(string $key, string $directory = 'content-blocks/home'): Forms\Components\FileUpload
    {
        return Forms\Components\FileUpload::make($key)
            ->label('Imagen')
            ->image()
            ->disk('public')
            ->directory($directory)
            ->imagePreviewHeight('150')
            ->maxSize(5120)
            ->afterStateHydrated(function (Forms\Components\FileUpload $component, $state): void {
                if (is_string($state)) {
                    $component->state(null);
                }
            });
    }

    /**
     * Build the Hero Slider section for a single slide.
     */
    protected function slideSection(int $n): Forms\Components\Section
    {
        return Forms\Components\Section::make("Slide {$n}")
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make("slide_{$n}_badge")
                        ->label('Badge (etiqueta)')
                        ->placeholder('Productos destacados'),
                    Forms\Components\TextInput::make("slide_{$n}_title")
                        ->label('Título')
                        ->placeholder('Título del slide')
                        ->columnSpanFull(),
                ]),
                Forms\Components\TextInput::make("slide_{$n}_subtitle")
                    ->label('Subtítulo')
                    ->placeholder('Descripción corta del slide')
                    ->columnSpanFull(),
                Forms\Components\Grid::make(2)->schema([
                    $this->makeImageUpload("slide_{$n}_image", 'content-blocks/home/slides'),
                    Forms\Components\TextInput::make("slide_{$n}_cta_text")
                        ->label('Texto del botón CTA')
                        ->placeholder('Ver productos'),
                ]),
                Forms\Components\TextInput::make("slide_{$n}_cta_url")
                    ->label('URL del botón CTA')
                    ->placeholder('/productos')
                    ->helperText('Ruta relativa (ej: /productos) o URL completa')
                    ->columnSpanFull(),
            ])
            ->compact()
            ->collapsible();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── Language ──
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

                // ── Hero Slider ──
                Forms\Components\Section::make('🖼️ Hero — Slider Principal')
                    ->description('Las diapositivas del carrusel principal. Se muestran en la parte superior de la página.')
                    ->schema([
                        $this->slideSection(1),
                        $this->slideSection(2),
                        $this->slideSection(3),
                    ])->collapsible(),

                // ── Trust Bar ──
                Forms\Components\Section::make('✅ Barra de Confianza')
                    ->description('Los 4 textos de confianza que aparecen debajo del hero (ej: "Hecho en España").')
                    ->schema([
                        Forms\Components\TextInput::make('trust_item_1')
                            ->label('Ítem 1')
                            ->placeholder('Hecho en España'),
                        Forms\Components\TextInput::make('trust_item_2')
                            ->label('Ítem 2')
                            ->placeholder('Envío a toda la UE'),
                        Forms\Components\TextInput::make('trust_item_3')
                            ->label('Ítem 3')
                            ->placeholder('Calidad garantizada'),
                        Forms\Components\TextInput::make('trust_item_4')
                            ->label('Ítem 4')
                            ->placeholder('Soporte técnico experto'),
                    ])->collapsible(),

                // ── Value Proposition ──
                Forms\Components\Section::make('💡 Propuesta de Valor')
                    ->description('La sección "¿Por qué elegirnos?" con 3 tarjetas de valores.')
                    ->schema([
                        Forms\Components\TextInput::make('vp_title')
                            ->label('Título de la sección')
                            ->placeholder('¿Por qué elegir Costa Resin & Construction?'),
                        Forms\Components\TextInput::make('vp_subtitle')
                            ->label('Subtítulo')
                            ->placeholder('Somos fabricantes directos con sede en Benissa...')
                            ->columnSpanFull(),
                        Forms\Components\Section::make('Valor 1')
                            ->schema([
                                Forms\Components\TextInput::make('vp_value_1_title')
                                    ->label('Título')
                                    ->placeholder('Fabricación propia'),
                                Forms\Components\TextInput::make('vp_value_1_text')
                                    ->label('Descripción')
                                    ->placeholder('Todos nuestros productos se fabrican en nuestra planta...')
                                    ->columnSpanFull(),
                            ])->compact()->collapsible(),
                        Forms\Components\Section::make('Valor 2')
                            ->schema([
                                Forms\Components\TextInput::make('vp_value_2_title')
                                    ->label('Título')
                                    ->placeholder('Envío europeo'),
                                Forms\Components\TextInput::make('vp_value_2_text')
                                    ->label('Descripción')
                                    ->placeholder('Entregamos en toda la Unión Europea...')
                                    ->columnSpanFull(),
                            ])->compact()->collapsible(),
                        Forms\Components\Section::make('Valor 3')
                            ->schema([
                                Forms\Components\TextInput::make('vp_value_3_title')
                                    ->label('Título')
                                    ->placeholder('Instalación profesional'),
                                Forms\Components\TextInput::make('vp_value_3_text')
                                    ->label('Descripción')
                                    ->placeholder('Nuestro equipo experto instala pavimentos...')
                                    ->columnSpanFull(),
                            ])->compact()->collapsible(),
                    ])->collapsible(),

                // ── Services Section ──
                Forms\Components\Section::make('🔧 Sección de Servicios (en el home)')
                    ->description('La sección "Nuestros servicios" que aparece en la página principal.')
                    ->schema([
                        Forms\Components\TextInput::make('services_section_title')
                            ->label('Título de la sección')
                            ->placeholder('Nuestros servicios'),
                        Forms\Components\TextInput::make('services_section_subtitle')
                            ->label('Subtítulo de la sección')
                            ->placeholder('Instalación profesional de pavimentos en resina epóxica y chips de granito')
                            ->columnSpanFull(),
                        Forms\Components\Section::make('Resina Epóxica')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('resin_title')
                                        ->label('Título — Resina')
                                        ->placeholder('Instalación de Resina Epóxica'),
                                    Forms\Components\TextInput::make('resin_desc')
                                        ->label('Descripción — Resina')
                                        ->placeholder('Pavimentos decorativos de resina epóxica...')
                                        ->columnSpanFull(),
                                    $this->makeImageUpload('resin_image'),
                                ]),
                            ])
                            ->compact()
                            ->collapsible(),
                        Forms\Components\Section::make('Chips de Granito')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('granite_title')
                                        ->label('Título — Granito')
                                        ->placeholder('Chips de Granito y Áridos'),
                                    Forms\Components\TextInput::make('granite_desc')
                                        ->label('Descripción — Granito')
                                        ->placeholder('Aplicación de chips de granito y áridos decorativos...')
                                        ->columnSpanFull(),
                                    $this->makeImageUpload('granite_image'),
                                ]),
                            ])
                            ->compact()
                            ->collapsible(),
                    ])->collapsible(),

                // ── CTA ──
                Forms\Components\Section::make('📞 Call to Action (sección final)')
                    ->description('Sección de llamada a la acción al final de la página principal.')
                    ->schema([
                        Forms\Components\TextInput::make('cta_title')
                            ->label('Título CTA')
                            ->placeholder('¿Necesita un presupuesto para su proyecto?'),
                        Forms\Components\TextInput::make('cta_subtitle')
                            ->label('Texto descriptivo')
                            ->placeholder('Contacte con nosotros para obtener una cotización personalizada...')
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

        $blocks = [];

        // ── Hero Slider (3 slides) ──
        foreach ([1, 2, 3] as $n) {
            foreach (['badge', 'title', 'subtitle', 'cta_text', 'cta_url'] as $field) {
                $blocks[] = ['hero', "slide_{$n}_{$field}", $data["slide_{$n}_{$field}"] ?? '', 'text'];
            }
        }

        // ── Trust Bar ──
        foreach ([1, 2, 3, 4] as $n) {
            $blocks[] = ['trust_bar', "item_{$n}", $data["trust_item_{$n}"] ?? '', 'text'];
        }

        // ── Value Proposition ──
        $blocks[] = ['value_proposition', 'title', $data['vp_title'] ?? '', 'text'];
        $blocks[] = ['value_proposition', 'subtitle', $data['vp_subtitle'] ?? '', 'text'];
        foreach ([1, 2, 3] as $n) {
            $blocks[] = ['value_proposition', "value_{$n}_title", $data["vp_value_{$n}_title"] ?? '', 'text'];
            $blocks[] = ['value_proposition', "value_{$n}_text", $data["vp_value_{$n}_text"] ?? '', 'text'];
        }

        // ── Services Section ──
        $blocks[] = ['services_section', 'section_title', $data['services_section_title'] ?? '', 'text'];
        $blocks[] = ['services_section', 'section_subtitle', $data['services_section_subtitle'] ?? '', 'text'];
        $blocks[] = ['services_section', 'resin_title', $data['resin_title'] ?? '', 'text'];
        $blocks[] = ['services_section', 'resin_description', $data['resin_desc'] ?? '', 'text'];
        $blocks[] = ['services_section', 'granite_title', $data['granite_title'] ?? '', 'text'];
        $blocks[] = ['services_section', 'granite_description', $data['granite_desc'] ?? '', 'text'];

        // ── CTA ──
        $blocks[] = ['cta', 'title', $data['cta_title'] ?? '', 'text'];
        $blocks[] = ['cta', 'subtitle', $data['cta_subtitle'] ?? '', 'text'];
        $blocks[] = ['cta', 'button_text', $data['cta_button_text'] ?? '', 'text'];

        // Capture pre-save values of the source locale (detects verbatim copies)
        $oldSources = [];
        foreach ($blocks as [$section, $key]) {
            $oldSources["{$section}.{$key}"] = ContentBlock::where('page', 'home')
                ->where('section', $section)
                ->where('key', $key)
                ->where('locale', $L)
                ->value('value');
        }

        // STEP 1: Save in current locale
        foreach ($blocks as [$section, $key, $value, $type]) {
            ContentBlock::set('home', $section, $key, $value, $type, $L);
        }

        // STEP 2: Propagate to all other locales — fills blanks and AI-translates
        // (Groq) any locale still holding a verbatim copy of the old/new source.
        foreach ($blocks as [$section, $key, $value, $type]) {
            if (! in_array($type, ['text', 'richtext'], true) || trim((string) $value) === '') {
                continue;
            }
            $block = new ContentBlock();
            $block->page = 'home';
            $block->section = $section;
            $block->key = $key;
            $block->value = $value;
            $block->type = $type;
            $block->locale = $L;
            $block->propagateTranslations($oldSources["{$section}.{$key}"] ?? null);
        }

        // Images: hero slides + services (images are the same for all locales)
        $imageFields = [
            'slide_1_image', 'slide_2_image', 'slide_3_image',
            'resin_image', 'granite_image',
        ];
        $imageSections = [
            'slide_1_image' => 'hero', 'slide_2_image' => 'hero', 'slide_3_image' => 'hero',
            'resin_image' => 'services_section', 'granite_image' => 'services_section',
        ];
        foreach ($imageFields as $field) {
            $value = $data[$field] ?? null;
            if (!blank($value) && is_string($value)) {
                // Save for all locales (images are language-independent)
                foreach (AutoTranslate::otherLocales($L) as $otherL) {
                    ContentBlock::set('home', $imageSections[$field], $field, $value, 'image', $otherL);
                }
                ContentBlock::set('home', $imageSections[$field], $field, $value, 'image', $L);
            }
        }

        ContentBlock::clearPageCache('home');

        $translatedCount = count(AutoTranslate::otherLocales($L));
        Notification::make()
            ->title("Contenido de Inicio guardado y traducido a {$translatedCount} idiomas")
            ->success()
            ->send();
    }
}
