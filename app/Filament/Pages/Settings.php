<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Configuración');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Configuración del Sitio');
    }
    public function getTitle(): string
    {
        return __('admin.Configuración del Sitio');
    }
    protected static ?string $slug = 'settings';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // General
            'site_name' => Setting::get('site_name', 'Costa Resin & Construction'),
            'site_tagline' => Setting::get('site_tagline', 'Soluciones de resinas y pavimentos'),
            'site_email' => Setting::get('site_email', 'hello@TU_DOMINIO.com'),
            'site_phone' => Setting::get('site_phone', '+34 900 000 000'),
            'site_whatsapp' => Setting::get('site_whatsapp', '+34600000000'),
            'site_address' => Setting::get('site_address', 'Calle Principal 123, 28001 Madrid, España'),

            // Logo
            'logo_path' => $this->toFileUrl(Setting::get('logo_path', 'images/logo.png')),
            'logo_white_path' => $this->toFileUrl(Setting::get('logo_white_path', 'images/logo-white.png')),
            'favicon_path' => $this->toFileUrl(Setting::get('favicon_path', 'images/favicon.png')),

            // Slider
            'slider_enabled' => Setting::get('slider_enabled', '1'),
            'slider_autoplay' => Setting::get('slider_autoplay', '1'),
            'slider_interval' => Setting::get('slider_interval', '6000'),
            'slides' => $this->getSlidesData(),

            // SEO
            'seo_title' => Setting::get('seo_title', ''),
            'seo_description' => Setting::get('seo_description', ''),
            'seo_og_image' => $this->toFileUrl(Setting::get('seo_og_image', '')),
            'seo_keywords' => Setting::get('seo_keywords', ''),

            // Social
            'social_instagram' => Setting::get('social_instagram', 'https://www.instagram.com/'),
            'social_facebook' => Setting::get('social_facebook', 'https://www.facebook.com/'),
            'social_youtube' => Setting::get('social_youtube', 'https://www.youtube.com/'),
            'social_linkedin' => Setting::get('social_linkedin', ''),
            'social_tiktok' => Setting::get('social_tiktok', ''),
            'social_twitter' => Setting::get('social_twitter', ''),

            // WhatsApp
            'whatsapp_enabled' => Setting::get('whatsapp_enabled', '1'),
            'whatsapp_position' => Setting::get('whatsapp_position', 'bottom-right'),
            'whatsapp_color' => Setting::get('whatsapp_color', '#25D366'),
            'whatsapp_message' => Setting::get('whatsapp_message', 'Hola, ¿en qué puedo ayudarte?'),

            // Brand Colors
            'color_primary' => Setting::get('color_primary', '#D97706'),
            'color_secondary' => Setting::get('color_secondary', '#1F2937'),
            'color_accent' => Setting::get('color_accent', '#F59E0B'),

            // GDPR / Cookies
            'cookies_enabled' => Setting::get('cookies_enabled', '1'),
            'cookies_message' => Setting::get('cookies_message', 'Utilizamos cookies para mejorar tu experiencia. Al continuar navegando, aceptas nuestro uso de cookies.'),
            'cookies_privacy_url' => Setting::get('cookies_privacy_url', '/legal/cookie-policy'),
            'cookies_accept_text' => Setting::get('cookies_accept_text', 'Aceptar'),
            'cookies_decline_text' => Setting::get('cookies_decline_text', 'Rechazar'),

            // AI Assistant
            'ai_enabled' => Setting::get('ai_enabled', '0'),
            'ai_provider' => Setting::get('ai_provider', 'groq'),
            'ai_api_key' => Setting::get('ai_api_key', ''),
            'ai_model' => Setting::get('ai_model', 'llama-3.1-8b-instant'),
            'ai_welcome_message' => Setting::get('ai_welcome_message', 'Hola, soy tu asistente virtual. ¿En qué puedo ayudarte?'),

            // Footer
            'footer_company' => Setting::get('footer_company', 'Costa Resin & Construction'),
            'footer_copyright' => Setting::get('footer_copyright', '© ' . date('Y') . ' Costa Resin & Construction. Todos los derechos reservados.'),
        ]);
    }

    /**
     * Build slides data from individual settings.
     */
    protected function getSlidesData(): array
    {
        $slides = [];
        for ($i = 1; $i <= 3; $i++) {
            $slides[] = [
                'title' => Setting::get("slide_{$i}_title", ''),
                'subtitle' => Setting::get("slide_{$i}_subtitle", ''),
                'cta_text' => Setting::get("slide_{$i}_cta_text", ''),
                'cta_url' => Setting::get("slide_{$i}_cta_url", '/tienda'),
                'image' => $this->toFileUrl(Setting::get("slide_{$i}_image", '')),
            ];
        }
        return $slides;
    }

    /**
     * Convert a stored file path to a full URL for Filament FileUpload.
     * FileUpload needs a URL to generate previews, not a relative path.
     */
    protected function toFileUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        // Already a full URL
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Already a storage-relative URL
        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        // Check if file exists on the configured uploads disk (local or S3)
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return upload_url($path);
        }

        // Fallback: try as-is (e.g. images/logo.png in public/)
        return asset($path);
    }

    /**
     * Convert a full URL or Filament path back to a storage-relative path.
     * FileUpload returns URLs but we need to store relative paths.
     */
    protected function toStoragePath(?string $value): string
    {
        if (empty($value)) {
            return $value;
        }

        // If it's a Livewire temporary upload, return as-is (FileUpload handles it)
        if (str_contains($value, 'livewire-tmp') || str_contains($value, 'tmp')) {
            return $value;
        }

        // Extract path from full URL. Local URLs look like
        // http://localhost:8000/storage/site/file.png; S3 URLs use AWS_URL.
        if (str_starts_with($value, 'http')) {
            $disk = \Illuminate\Support\Facades\Storage::disk('public');
            $base = rtrim($disk->url(''), '/');

            if ($base && str_starts_with($value, $base . '/')) {
                return ltrim(substr($value, strlen($base) + 1), '/');
            }

            $path = parse_url($value, PHP_URL_PATH);
            if ($path && str_starts_with($path, '/storage/')) {
                return ltrim(substr($path, strlen('/storage/')), '/');
            }
            return $value;
        }

        // Already a relative path like site/file.png
        return $value;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ── Información General ──
                Section::make('Información General')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('site_name')
                                ->label(__('admin.Nombre del sitio'))
                                ->required(),
                            Forms\Components\TextInput::make('site_tagline')
                                ->label(__('admin.Lema / Tagline')),
                            Forms\Components\TextInput::make('site_email')
                                ->label(__('admin.Email'))
                                ->email(),
                            Forms\Components\TextInput::make('site_phone')
                                ->label(__('admin.Teléfono')),
                            Forms\Components\TextInput::make('site_whatsapp')
                                ->label(__('admin.WhatsApp (formato internacional)'))
                                ->placeholder(__('admin.+34600000000')),
                            Forms\Components\TextInput::make('site_address')
                                ->label(__('admin.Dirección'))
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ── Logo y Favicon ──
                Section::make('Logo y Favicon')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\FileUpload::make('logo_path')
                                ->label(__('admin.Logo principal'))
                                ->image()
                                ->maxSize(2048)
                                ->disk('public')
                                ->directory('site')
                                ->helperText(__('admin.Max 2MB. JPG, PNG, SVG.')),
                            Forms\Components\FileUpload::make('logo_white_path')
                                ->label(__('admin.Logo blanco (para fondos oscuros)'))
                                ->image()
                                ->maxSize(2048)
                                ->disk('public')
                                ->directory('site')
                                ->helperText(__('admin.Max 2MB. JPG, PNG, SVG.')),
                            Forms\Components\FileUpload::make('favicon_path')
                                ->label(__('admin.Favicon'))
                                ->image()
                                ->maxSize(2048)
                                ->disk('public')
                                ->directory('site')
                                ->helperText(__('admin.Max 2MB. JPG, PNG.')),
                        ]),
                    ]),

                // ── Hero Slider ──
                Section::make('Video de portada')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Select::make('hero_playback_rate')
                                ->label(__('admin.Velocidad del video'))
                                ->options([
                                    '0.25' => '0.25x — muy lento',
                                    '0.4' => '0.4x — cámara lenta',
                                    '0.625' => '0.625x — lenta (por defecto)',
                                    '0.75' => '0.75x — algo más rápido',
                                    '1.0' => '1.0x — velocidad normal',
                                    '1.25' => '1.25x',
                                    '1.5' => '1.5x',
                                    '2.0' => '2.0x — rápido',
                                ])
                                ->default('0.625')
                                ->helperText(__('admin.Velocidad de reproducción del video de fondo del héroe. Sin guardar aquí, se usa 0.625x.')),
                        ]),
                    ]),

                Section::make('Hero Slider')
                    ->schema([
                        Grid::make(3)->schema([
                            Toggle::make('slider_enabled')
                                ->label(__('admin.Slider activo'))
                                ->default(true),
                            Toggle::make('slider_autoplay')
                                ->label(__('admin.Autoplay automático'))
                                ->default(true),
                            Forms\Components\TextInput::make('slider_interval')
                                ->label(__('admin.Intervalo autoplay (ms)'))
                                ->numeric()
                                ->default(6000)
                                ->helperText(__('admin.Milisegundos entre slides (6000 = 6 segundos)')),
                        ]),

                        Repeater::make('slides')
                            ->label(__('admin.Slides'))
                            ->schema([
                                Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label(__('admin.Título'))
                                        ->required(),
                                    Forms\Components\TextInput::make('subtitle')
                                        ->label(__('admin.Subtítulo'))
                                        ->columnSpanFull(),
                                    Forms\Components\TextInput::make('cta_text')
                                        ->label(__('admin.Texto del botón CTA')),
                                    Forms\Components\TextInput::make('cta_url')
                                        ->label(__('admin.URL del botón CTA')),
                                    Forms\Components\FileUpload::make('image')
                                        ->label(__('admin.Imagen de fondo'))
                                        ->image()
                                        ->maxSize(2048)
                                        ->disk('public')
                                        ->directory('site/slides')
                                        ->columnSpanFull(),
                                ]),
                            ])
                            ->defaultItems(3)
                            ->addActionLabel('Agregar slide')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                    ]),

                // ── SEO ──
                Section::make('SEO (Posicionamiento)')
                    ->description(__('admin.Configuración meta para buscadores y redes sociales.'))
                    ->schema([
                        Grid::make(1)->schema([
                            Forms\Components\TextInput::make('seo_title')
                                ->label(__('admin.Meta Title'))
                                ->placeholder(__('admin.Costa Resin & Construction - Soluciones de pavimentos'))
                                ->helperText(__('admin.Título que aparece en Google. Max 60 caracteres.')),
                            Forms\Components\Textarea::make('seo_description')
                                ->label(__('admin.Meta Description'))
                                ->rows(3)
                                ->placeholder(__('admin.Fabricante y distribuidor de resinas de poliuretano en España...'))
                                ->helperText(__('admin.Descripción que aparece en Google. Max 160 caracteres.')),
                            Forms\Components\FileUpload::make('seo_og_image')
                                ->label(__('admin.Imagen Open Graph (para redes sociales)'))
                                ->image()
                                ->maxSize(5120)
                                ->disk('public')
                                ->directory('site/seo')
                                ->helperText(__('admin.Imagen que aparece al compartir en Facebook, Twitter, etc. (1200x630px recomendado)')),
                            Forms\Components\Textarea::make('seo_keywords')
                                ->label('Meta Keywords')
                                ->rows(3)
                                ->placeholder('resinas epoxicas, chips de granito, pavimentos decorativos, Benissa')
                                ->helperText('Palabras clave separadas por comas. Puedes incluir español e inglés.'),
                        ]),
                    ]),

                // ── Redes Sociales ──
                Section::make('Redes Sociales')
                    ->description(__('admin.Enlaces que aparecen en el footer del sitio web.'))
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('social_instagram')
                                ->label(__('admin.Instagram'))
                                ->url()
                                ->placeholder(__('admin.https://www.instagram.com/')),
                            Forms\Components\TextInput::make('social_facebook')
                                ->label(__('admin.Facebook'))
                                ->url()
                                ->placeholder(__('admin.https://www.facebook.com/')),
                            Forms\Components\TextInput::make('social_youtube')
                                ->label(__('admin.YouTube'))
                                ->url()
                                ->placeholder(__('admin.https://www.youtube.com/')),
                            Forms\Components\TextInput::make('social_linkedin')
                                ->label(__('admin.LinkedIn'))
                                ->url()
                                ->placeholder(__('admin.https://www.linkedin.com/company/costaresinconstruction')),
                            Forms\Components\TextInput::make('social_tiktok')
                                ->label(__('admin.TikTok'))
                                ->url()
                                ->placeholder(__('admin.https://www.tiktok.com/@costaresinconstruction')),
                            Forms\Components\TextInput::make('social_twitter')
                                ->label(__('admin.X / Twitter'))
                                ->url()
                                ->placeholder(__('admin.https://x.com/costaresinconstruction')),
                        ]),
                    ]),

                // ── WhatsApp Flotante ──
                Section::make('Botón Flotante de WhatsApp')
                    ->description(__('admin.Configura el botón de WhatsApp que aparece en todas las páginas.'))
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('whatsapp_enabled')
                                ->label(__('admin.Mostrar botón'))
                                ->default(true),
                            Forms\Components\Select::make('whatsapp_position')
                                ->label(__('admin.Posición'))
                                ->options([
                                    'bottom-right' => 'Abajo a la derecha',
                                    'bottom-left' => 'Abajo a la izquierda',
                                ])
                                ->default('bottom-right'),
                            Forms\Components\TextInput::make('whatsapp_color')
                                ->label(__('admin.Color del botón'))
                                ->placeholder(__('admin.#25D366'))
                                ->helperText(__('admin.Color hexadecimal (ej: #25D366)')),
                            Forms\Components\TextInput::make('whatsapp_message')
                                ->label(__('admin.Mensaje de bienvenida'))
                                ->placeholder(__('admin.Hola, ¿en qué puedo ayudarte?'))
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ── Colores de Marca ──
                Section::make('Colores de Marca')
                    ->description(__('admin.Colores principales del sitio web.'))
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('color_primary')
                                ->label(__('admin.Color primario'))
                                ->placeholder(__('admin.#D97706'))
                                ->helperText(__('admin.Color principal (botones, acentos)')),
                            Forms\Components\TextInput::make('color_secondary')
                                ->label(__('admin.Color secundario'))
                                ->placeholder(__('admin.#1F2937'))
                                ->helperText(__('admin.Color de fondos oscuros')),
                            Forms\Components\TextInput::make('color_accent')
                                ->label(__('admin.Color de acento'))
                                ->placeholder(__('admin.#F59E0B'))
                                ->helperText(__('admin.Color de resaltado')),
                        ]),
                    ]),

                // ── Cookies / GDPR ──
                Section::make('Banner de Cookies (GDPR)')
                    ->description(__('admin.Configura el aviso de cookies que se muestra a los visitantes.'))
                    ->schema([
                        Grid::make(1)->schema([
                            Toggle::make('cookies_enabled')
                                ->label(__('admin.Mostrar banner de cookies'))
                                ->default(true),
                            Forms\Components\TextInput::make('cookies_message')
                                ->label(__('admin.Mensaje del banner'))
                                ->placeholder(__('admin.Utilizamos cookies para mejorar tu experiencia...'))
                                ->columnSpanFull(),
                            Forms\Components\TextInput::make('cookies_privacy_url')
                                ->label(__('admin.URL de política de cookies'))
                                ->placeholder(__('admin./legal/cookie-policy')),
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('cookies_accept_text')
                                    ->label(__('admin.Texto botón Aceptar'))
                                    ->default('Aceptar'),
                                Forms\Components\TextInput::make('cookies_decline_text')
                                    ->label(__('admin.Texto botón Rechazar'))
                                    ->default('Rechazar'),
                            ]),
                        ]),
                    ]),

                // ── Asistente IA ──
                Section::make('Asistente IA (Chat)')
                    ->description(__('admin.Configura el chat de inteligencia artificial del sitio.'))
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('ai_enabled')
                                ->label(__('admin.Activar chat IA'))
                                ->default(false),
                            Forms\Components\Select::make('ai_provider')
                                ->label(__('admin.Proveedor'))
                                ->options([
                                    'groq' => 'Groq (Gratis)',
                                    'openai' => 'OpenAI',
                                    'anthropic' => 'Anthropic',
                                    'deepseek' => 'DeepSeek',
                                ])
                                ->default('groq'),
                            Forms\Components\TextInput::make('ai_api_key')
                                ->label(__('admin.API Key'))
                                ->password()
                                ->revealable()
                                ->placeholder(__('admin.gsk_...'))
                                ->helperText(__('admin.Clave de API del proveedor seleccionado')),
                            Forms\Components\TextInput::make('ai_model')
                                ->label(__('admin.Modelo'))
                                ->placeholder(__('admin.llama-3.1-8b-instant'))
                                ->helperText(__('admin.Nombre del modelo (ej: llama-3.1-8b-instant, gpt-4o-mini)')),
                            Forms\Components\TextInput::make('ai_welcome_message')
                                ->label(__('admin.Mensaje de bienvenida'))
                                ->placeholder(__('admin.Hola, soy tu asistente virtual...'))
                                ->columnSpanFull(),
                        ]),
                    ]),

                // ── Footer ──
                Section::make('Footer')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('footer_company')
                                ->label(__('admin.Nombre en footer')),
                            Forms\Components\TextInput::make('footer_copyright')
                                ->label(__('admin.Texto de copyright'))
                                ->columnSpanFull(),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Image fields that use FileUpload
        $imageFields = [
            'logo_path', 'logo_white_path', 'favicon_path',
            'seo_og_image',
        ];

        foreach ($data as $key => $value) {
            // Handle slides repeater - save as individual settings
            if ($key === 'slides' && is_array($value)) {
                foreach ($value as $index => $slide) {
                    $i = $index + 1;

                    // Save text fields (always, even if empty — user may intentionally clear)
                    Setting::set("slide_{$i}_title", $slide['title'] ?? '', 'text');
                    Setting::set("slide_{$i}_subtitle", $slide['subtitle'] ?? '', 'text');
                    Setting::set("slide_{$i}_cta_text", $slide['cta_text'] ?? '', 'text');
                    Setting::set("slide_{$i}_cta_url", $slide['cta_url'] ?? '/tienda', 'text');

                    // CRITICAL: Only overwrite slide image if user actually uploaded a new one.
                    // FileUpload returns null/empty when unchanged — never overwrite with empty.
                    $slideImage = $this->toStoragePath($slide['image'] ?? '');
                    if (!blank($slideImage)) {
                        Setting::set("slide_{$i}_image", $slideImage, 'image');
                    }
                }

                // Clean up any leftover slides beyond what the repeater has
                $count = count($value);
                for ($i = $count + 1; $i <= 5; $i++) {
                    Setting::set("slide_{$i}_title", '', 'text');
                    Setting::set("slide_{$i}_subtitle", '', 'text');
                    Setting::set("slide_{$i}_cta_text", '', 'text');
                    Setting::set("slide_{$i}_cta_url", '', 'text');
                    Setting::set("slide_{$i}_image", '', 'image');
                }

                continue;
            }

            $isImage = in_array($key, $imageFields, true);

            // CRITICAL: Never overwrite image fields with empty/null.
            // FileUpload returns null when the user didn't change the image.
            if ($isImage && blank($value)) {
                continue;
            }

            // Convert full URLs back to storage paths for image fields
            if ($isImage) {
                $value = $this->toStoragePath($value);
            }

            // Skip if value hasn't changed (avoids unnecessary writes)
            $currentValue = Setting::get($key, '');
            if ((string) $value === (string) $currentValue) {
                continue;
            }

            Setting::set($key, $value, $isImage ? 'image' : 'text');
        }

        // Invalidate content block cache for all pages
        foreach (['home', 'about', 'services', 'contact', 'shop', 'global'] as $page) {
            \App\Models\ContentBlock::clearPageCache($page);
        }

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
