<?php

namespace App\Filament\Pages\SuperPanel;

use App\Services\PlatformModules as PlatformModuleService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PlatformModules extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Control exclusivo del desarrollador';

    protected static ?string $navigationLabel = 'Módulos de plataforma';

    protected static ?string $slug = 'modules';

    protected static ?int $navigationSort = -90;

    protected static string $view = 'filament.pages.super-panel.platform-modules';

    public array $moduleSettings = [];

    public static function canAccess(): bool
    {
        $developerEmail = config('platform.developer_email');

        return Filament::getCurrentPanel()?->getId() === 'super-panel'
            && filled($developerEmail)
            && auth()->user()?->email === $developerEmail;
    }

    public function mount(PlatformModuleService $modules): void
    {
        $this->authorizeDeveloper();
        $this->moduleSettings = collect($modules->all())
            ->mapWithKeys(fn (array $module, string $key): array => [$key => $module['enabled']])
            ->all();
    }

    public function save(PlatformModuleService $modules): void
    {
        $this->authorizeDeveloper();
        $modules->setEnabledModules($this->moduleSettings);

        Notification::make()
            ->title('Module settings updated')
            ->success()
            ->send();
    }

    public function getModules(): array
    {
        return app(PlatformModuleService::class)->all();
    }

    private function authorizeDeveloper(): void
    {
        abort_unless(static::canAccess(), 403);
    }
}
