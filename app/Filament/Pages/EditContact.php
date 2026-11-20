<?php

namespace App\Filament\Pages;

use App\Models\ContentBlock;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class EditContact extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-phone';
    protected static ?string $slug = 'content/contact';
    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.Contenido');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.Editar Contacto');
    }

    public function getTitle(): string
    {
        return __('admin.Contenido — Contacto');
    }

    protected static string $view = 'filament.pages.edit-contact';

    public ?string $locale = 'es';
    public ?array $data = [];

    public function mount(): void
    {
        $this->locale = app()->getLocale();
        $this->loadData();
    }

    protected function loadData(): void
    {
        $L = $this->locale;

        $this->data = [
            'locale'          => $L,
            'hero_title'      => ContentBlock::get('contact', 'hero', 'title', $L, ''),
            'hero_subtitle'   => ContentBlock::get('contact', 'hero', 'subtitle', $L, ''),
            'info_address'    => ContentBlock::get('contact', 'info', 'address', $L, ''),
            'info_phone'      => ContentBlock::get('contact', 'info', 'phone', $L, ''),
            'info_email'      => ContentBlock::get('contact', 'info', 'email', $L, ''),
            'info_schedule'   => ContentBlock::get('contact', 'info', 'schedule', $L, ''),
            'info_form_title' => ContentBlock::get('contact', 'info', 'form_title', $L, ''),
        ];
    }

    public function updatedLocale(): void
    {
        $this->loadData();
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

                Forms\Components\Section::make('🌟 Hero — Cabecera')
                    ->schema([
                        Forms\Components\TextInput::make('hero_title')
                            ->label('Título principal')
                            ->placeholder('Contacto')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('hero_subtitle')
                            ->label('Subtítulo')
                            ->placeholder('Solicite una cotización o contáctenos para cualquier consulta')
                            ->columnSpanFull(),
                    ])->collapsible(),

                Forms\Components\Section::make('📍 Información de contacto')
                    ->description('Datos que aparecen en la página de contacto.')
                    ->schema([
                        Forms\Components\TextInput::make('info_address')
                            ->label('Dirección')
                            ->placeholder('Calle de la Industria, s/n - 28001 Madrid, España')
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\TextInput::make('info_phone')
                                ->label('Teléfono')
                                ->placeholder('+34 711073958'),
                            Forms\Components\TextInput::make('info_email')
                                ->label('Email')
                                ->email()
                                ->placeholder('hello@TU_DOMINIO.com'),
                        ]),
                        Forms\Components\TextInput::make('info_schedule')
                            ->label('Horario')
                            ->placeholder('Lunes a Viernes: 9:00 - 18:00 — Sábados: 9:00 - 13:00')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('info_form_title')
                            ->label('Título del formulario de cotización')
                            ->placeholder('Solicitar cotización de instalación')
                            ->columnSpanFull(),
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
            ['hero', 'title',      $data['hero_title'] ?? '', 'text'],
            ['hero', 'subtitle',   $data['hero_subtitle'] ?? '', 'text'],
            ['info', 'address',    $data['info_address'] ?? '', 'text'],
            ['info', 'phone',      $data['info_phone'] ?? '', 'text'],
            ['info', 'email',      $data['info_email'] ?? '', 'text'],
            ['info', 'schedule',   $data['info_schedule'] ?? '', 'text'],
            ['info', 'form_title', $data['info_form_title'] ?? '', 'text'],
        ];

        foreach ($blocks as [$section, $key, $value, $type]) {
            ContentBlock::set('contact', $section, $key, $value, $type, $L);
        }

        ContentBlock::clearPageCache('contact');

        Notification::make()
            ->title("Contenido de Contacto guardado ({$L})")
            ->success()
            ->send();
    }
}
