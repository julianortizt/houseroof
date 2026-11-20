<?php

namespace App\Filament\Pages\SuperPanel;

use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Validation\Rule;

class PlatformAppearance extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-swatch';

    protected static ?string $navigationGroup = 'Control exclusivo del desarrollador';

    protected static ?string $navigationLabel = 'Plantillas y estilos';

    protected static ?string $slug = 'appearance';

    protected static ?int $navigationSort = -100;

    protected static string $view = 'filament.pages.super-panel.platform-appearance';

    public string $siteStyle = 'professional';

    public string $homeTemplate = 'roof';

    public function getTitle(): string
    {
        return 'Plantillas y estilos';
    }

    public static function canAccess(): bool
    {
        $developerEmail = config('platform.developer_email');

        return Filament::getCurrentPanel()?->getId() === 'super-panel'
            && filled($developerEmail)
            && auth()->user()?->email === $developerEmail;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->siteStyle = (string) \App\Models\Setting::get('platform_style', 'professional');
        $this->homeTemplate = (string) \App\Models\Setting::get('homepage_template', config('platform.default_home_template', 'roof'));
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->validate([
            'siteStyle' => ['required', Rule::in(array_keys(config('platform.styles')))],
            'homeTemplate' => ['required', Rule::in(array_keys(config('platform.home_templates')))],
        ]);

        \App\Models\Setting::set('platform_style', $this->siteStyle, 'text', 'platform');
        \App\Models\Setting::set('homepage_template', $this->homeTemplate, 'text', 'platform');

        Notification::make()
            ->title('Appearance settings updated')
            ->success()
            ->send();
    }
}
