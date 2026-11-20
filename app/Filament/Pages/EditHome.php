<?php

namespace App\Filament\Pages;

use App\Models\ContentBlock;
use App\Support\RoofContent;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Admin editor for the House Roof homepage.
 *
 * Every field maps 1:1 to a ContentBlock in page=home, section=roof. The
 * shipping translations in config/roof-content.php are used as defaults, so
 * all seven languages are populated even before this page has been saved.
 */
class EditHome extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $slug = 'content/home';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.edit-home';

    public ?string $locale = 'es';
    public ?array $data = [];

    protected const GROUPS = [
        'Hero' => [
            'hero_badge', 'hero_title', 'hero_subtitle', 'hero_primary_cta',
            'hero_primary_url', 'hero_secondary_cta', 'hero_secondary_url', 'hero_note',
        ],
        'Cifras destacadas' => [
            'stat_1_value', 'stat_1_label', 'stat_2_value', 'stat_2_label',
            'stat_3_value', 'stat_3_label',
        ],
        'Barra de confianza' => ['trust_1', 'trust_2', 'trust_3', 'trust_4'],
        'Servicios' => [
            'services_kicker', 'services_title', 'services_subtitle',
            'service_1_badge', 'service_1_title', 'service_1_text', 'service_1_cta',
            'service_1_url', 'service_1_image',
            'service_2_badge', 'service_2_title', 'service_2_text', 'service_2_cta',
            'service_2_url', 'service_2_image',
            'service_3_badge', 'service_3_title', 'service_3_text', 'service_3_cta',
            'service_3_url', 'service_3_image',
            'service_4_badge', 'service_4_title', 'service_4_text', 'service_4_cta',
            'service_4_url', 'service_4_image',
        ],
        'Proceso de trabajo' => [
            'process_kicker', 'process_title', 'process_subtitle',
            'step_1_title', 'step_1_text', 'step_2_title', 'step_2_text',
            'step_3_title', 'step_3_text', 'step_4_title', 'step_4_text',
        ],
        'Zonas de servicio' => [
            'areas_kicker', 'areas_title', 'areas_subtitle',
            'area_1', 'area_2', 'area_3', 'area_4', 'area_5', 'area_6',
        ],
        'Por qué elegirnos' => [
            'why_kicker', 'why_title', 'why_subtitle',
            'why_1_title', 'why_1_text', 'why_2_title', 'why_2_text',
            'why_3_title', 'why_3_text',
        ],
        'Llamada final a la acción' => [
            'cta_title', 'cta_subtitle', 'cta_button', 'cta_url', 'coverage_note',
        ],
    ];

    protected const LABELS = [
        'hero_badge' => 'Etiqueta del hero',
        'hero_title' => 'Titular principal',
        'hero_subtitle' => 'Subtítulo del hero',
        'hero_primary_cta' => 'Texto del botón principal',
        'hero_primary_url' => 'URL del botón principal',
        'hero_secondary_cta' => 'Texto del botón secundario',
        'hero_secondary_url' => 'URL del botón secundario',
        'hero_note' => 'Nota bajo los botones',
        'stat_1_value' => 'Cifra 1', 'stat_1_label' => 'Etiqueta cifra 1',
        'stat_2_value' => 'Cifra 2', 'stat_2_label' => 'Etiqueta cifra 2',
        'stat_3_value' => 'Cifra 3', 'stat_3_label' => 'Etiqueta cifra 3',
        'trust_1' => 'Mensaje de confianza 1', 'trust_2' => 'Mensaje de confianza 2',
        'trust_3' => 'Mensaje de confianza 3', 'trust_4' => 'Mensaje de confianza 4',
        'services_kicker' => 'Antetítulo de servicios',
        'services_title' => 'Título de servicios',
        'services_subtitle' => 'Subtítulo de servicios',
        'process_kicker' => 'Antetítulo del proceso',
        'process_title' => 'Título del proceso',
        'process_subtitle' => 'Subtítulo del proceso',
        'areas_kicker' => 'Antetítulo de zonas',
        'areas_title' => 'Título de zonas',
        'areas_subtitle' => 'Subtítulo de zonas',
        'why_kicker' => 'Antetítulo de ventajas',
        'why_title' => 'Título de ventajas',
        'why_subtitle' => 'Subtítulo de ventajas',
        'cta_title' => 'Título final',
        'cta_subtitle' => 'Texto final',
        'cta_button' => 'Texto del botón final',
        'cta_url' => 'URL del botón final',
        'coverage_note' => 'Nota de cobertura',
    ];

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

    public function mount(): void
    {
        $this->loadData();
    }

    protected function loadData(): void
    {
        $locale = $this->locale ?: 'es';
        $data = ['locale' => $locale];

        foreach (static::keys() as $key) {
            $data[$key] = RoofContent::home($key, $locale);
        }

        $this->data = $data;
        $this->form->fill($data);
    }

    public function updatedLocale(): void
    {
        $this->loadData();
    }

    public function switchLocale(string $newLocale): void
    {
        $this->locale = $newLocale;
        $this->loadData();
    }

    /** @return array<int, string> */
    protected static function keys(): array
    {
        return array_values(array_unique(array_merge(...array_values(static::GROUPS))));
    }

    protected function labelFor(string $key): string
    {
        if (isset(static::LABELS[$key])) {
            return static::LABELS[$key];
        }

        if (preg_match('/^(service|step|why)_(\d)_(title|text|badge|cta)/', $key, $m)) {
            return ucfirst($m[1]) . ' ' . $m[2] . ' · ' . $m[3];
        }

        if (preg_match('/^area_(\d)$/', $key, $m)) {
            return 'Zona ' . $m[1];
        }

        return ucwords(str_replace('_', ' ', $key));
    }

    protected function makeField(string $key): Forms\Components\Component
    {
        $label = $this->labelFor($key);

        if (str_ends_with($key, '_image')) {
            return Forms\Components\FileUpload::make($key)
                ->label($label)
                ->image()
                ->disk('public')
                ->directory('content-blocks/home')
                ->imagePreviewHeight('140')
                ->maxSize(5120)
                ->columnSpanFull();
        }

        if (str_ends_with($key, '_url')) {
            return Forms\Components\TextInput::make($key)
                ->label($label)
                ->url()
                ->placeholder('/servicios');
        }

        if (str_ends_with($key, '_text') || str_ends_with($key, '_subtitle')
            || str_ends_with($key, '_note') || str_ends_with($key, '_content')) {
            return Forms\Components\Textarea::make($key)
                ->label($label)
                ->rows(2)
                ->columnSpanFull();
        }

        return Forms\Components\TextInput::make($key)->label($label);
    }

    public function form(Form $form): Form
    {
        $schema = [
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
        ];

        foreach (static::GROUPS as $title => $keys) {
            $schema[] = Forms\Components\Section::make($title)
                ->schema(array_map(fn (string $key) => $this->makeField($key), $keys))
                ->columns(2)
                ->collapsible()
                ->collapsed($title !== 'Hero');
        }

        return $form->schema($schema)->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $locale = $data['locale'] ?? $this->locale ?? 'es';

        foreach (static::keys() as $key) {
            $value = $data[$key] ?? '';

            // Never erase an existing image just because Filament returned no
            // change state for it.
            if (str_ends_with($key, '_image') && blank($value)) {
                continue;
            }

            ContentBlock::set(
                'home',
                'roof',
                $key,
                $value,
                str_ends_with($key, '_image') ? 'image' : 'text',
                $locale,
            );
        }

        ContentBlock::clearPageCache('home');

        Notification::make()
            ->title("Contenido de Inicio guardado en {$locale}")
            ->success()
            ->send();
    }
}
