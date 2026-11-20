<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class MaintenanceMode extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    public static function getNavigationGroup(): ?string
    {
        return __('admin.Configuración');
    }
    protected static ?int $navigationSort = 10;
    public function getTitle(): string
    {
        return __('admin.Maintenance Mode');
    }
    public static function getNavigationLabel(): string
    {
        return __('admin.Maintenance');
    }
    protected static string $view = 'filament.pages.maintenance-mode';

    public bool $isMaintenanceMode = false;
    public ?string $maintenanceSecret = null;
    public ?string $maintenanceMessage = null;
    public int $maintenanceRetry = 60;

    public function mount(): void
    {
        $this->isMaintenanceMode = app()->isDownForMaintenance();
        $this->maintenanceMessage = config('app.maintenance.message') ?? 'Estamos realizando mejoras en el sitio. Volveremos pronto.';
        $this->maintenanceRetry = (int) (config('app.maintenance.retry') ?? 60);

        // If maintenance is currently active, restore the secret so it can be reused.
        if ($this->isMaintenanceMode) {
            $data = $this->getDownData();
            $this->maintenanceSecret = $data['secret'] ?? null;
        }
    }

    /**
     * Read the raw "down" payload so we can recover the active secret.
     */
    protected function getDownData(): array
    {
        $path = storage_path('framework/down');

        if (! file_exists($path)) {
            return [];
        }

        $decoded = json_decode(file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Configuración de Mantenimiento')
                    ->description(__('admin.Active o desactive el modo mantenimiento del sitio web. Mientras esté activo, los visitantes verán un mensaje de mantenimiento.'))
                    ->schema([
                        Forms\Components\Textarea::make('maintenanceMessage')
                            ->label(__('admin.Mensaje para visitantes'))
                            ->rows(3)
                            ->default('Estamos realizando mejoras en el sitio. Volveremos pronto.')
                            ->helperText(__('admin.Mensaje que verán los usuarios cuando el sitio esté en mantenimiento.')),
                        Forms\Components\TextInput::make('maintenanceRetry')
                            ->label(__('admin.Reintentar después (segundos)'))
                            ->numeric()
                            ->default(60)
                            ->helperText(__('admin.Tiempo en segundos que el navegador del usuario esperará antes de reintentar.')),
                        Forms\Components\TextInput::make('maintenanceSecret')
                            ->label(__('admin.Secreto de mantenimiento'))
                            ->helperText(__('admin.Si lo establece, podrá ver el sitio añadiendo ?secret=TU_SECRETO a la URL.'))
                            ->placeholder(__('admin.Dejar vacío para no usar secreto')),
                    ])->columns(1),
            ]);
    }

    /**
     * Absolute path of the "down" file used by Laravel's maintenance mode.
     */
    protected function downPath(): string
    {
        return storage_path('framework/down');
    }

    /**
     * Write the "down" payload. We create it directly (instead of relying on
     * Artisan) so it is always owned by the web process and we can guarantee
     * it is writable/deletable afterwards. The file is created with 0664 so
     * it can be removed even on shared hosting.
     */
    protected function writeDownFile(array $payload): bool
    {
        $path = $this->downPath();

        $dir = dirname($path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $written = @file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT));

        if ($written === false) {
            return false;
        }

        @chmod($path, 0664);

        return true;
    }

    /**
     * Remove the "down" file. Tries a direct unlink and, if that fails due to
     * permissions, attempts to relax the mode first. Returns true only when
     * the file is actually gone.
     */
    protected function removeDownFile(): bool
    {
        $path = $this->downPath();

        if (! file_exists($path)) {
            return true;
        }

        if (@unlink($path)) {
            return true;
        }

        // Retry after making the file writable (helps when ownership differs).
        @chmod($path, 0664);

        return @unlink($path);
    }

    public function activateMaintenance(): void
    {
        $secret = $this->maintenanceSecret ?: \Illuminate\Support\Str::random(32);
        $retry = $this->maintenanceRetry ?: 60;
        $message = $this->maintenanceMessage ?: 'Estamos realizando mejoras. Volveremos pronto.';

        $payload = [
            'time' => time(),
            'message' => $message,
            'retry' => $retry,
            'secret' => $secret,
            'data' => ['render' => 'maintenance'],
        ];

        if (! $this->writeDownFile($payload)) {
            Notification::make()
                ->title('Error al activar mantenimiento')
                ->danger()
                ->body('No se pudo escribir el archivo storage/framework/down. Verifica los permisos del directorio storage/framework.')
                ->persistent()
                ->send();

            return;
        }

        // Persist the message so the rendered maintenance view can read it.
        config(['app.maintenance.message' => $message]);
        config(['app.maintenance.retry' => $retry]);

        $this->isMaintenanceMode = true;
        $this->maintenanceSecret = $secret;

        Notification::make()
            ->title('Modo mantenimiento ACTIVADO')
            ->warning()
            ->body('El sitio ahora está en mantenimiento. Los visitantes ven el mensaje configurado.')
            ->persistent()
            ->send();
    }

    public function deactivateMaintenance(): void
    {
        if (! $this->removeDownFile()) {
            Notification::make()
                ->title('Error al desactivar mantenimiento')
                ->danger()
                ->body('No se pudo eliminar el archivo storage/framework/down. Borralo manualmente por FTP/SSH o ajusta sus permisos.')
                ->persistent()
                ->send();

            return;
        }

        $this->isMaintenanceMode = false;
        $this->maintenanceSecret = null;

        Notification::make()
            ->title('Modo mantenimiento DESACTIVADO')
            ->success()
            ->body('El sitio está de vuelta en línea.')
            ->send();
    }
}
