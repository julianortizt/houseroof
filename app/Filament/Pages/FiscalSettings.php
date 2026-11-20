<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\FiscalProfile;
use App\Services\PlatformModules;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class FiscalSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationGroup = 'Finanzas (borradores internos)';
    protected static ?string $navigationLabel = 'Datos fiscales de empresa';
    protected static ?string $slug = 'fiscal-settings';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.fiscal-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $panelId = Filament::getCurrentPanel()?->getId();
        /** @var User|null $user */
        $user = Auth::guard('web')->user();
        $developerEmail = config('platform.developer_email');

        $authorized = match ($panelId) {
            'super-panel' => filled($developerEmail) && $user?->email === $developerEmail,
            'admin' => $user instanceof User && $user->hasRole(['super_admin', 'admin']),
            default => false,
        };

        return $authorized && app(PlatformModules::class)->enabled('invoicing');
    }

    public function mount(FiscalProfile $profile): void
    {
        $this->form->fill($profile->get());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Datos de la empresa')
                    ->description('Configura la identidad legal de la empresa y activa la emisión fiscal controlada por serie.')
                    ->schema([
                        Forms\Components\Placeholder::make('fiscal_status')
                            ->label('Estado de emisión')
                            ->content(fn (): string => app(FiscalProfile::class)->canIssue()
                                ? 'Emisión fiscal habilitada y numeración activa.'
                                : 'Emisión fiscal desactivada. Completa la identidad legal y habilita la emisión.'),
                        Forms\Components\Toggle::make('issuance_enabled')
                            ->label('Habilitar emisión fiscal')
                            ->onIcon('heroicon-s-check')
                            ->offIcon('heroicon-s-x-mark')
                            ->helperText('Activa la numeración legal en serie para facturas emitidas desde el panel.'),
                        Forms\Components\TextInput::make('legal_name')->label('Razón social')->maxLength(255),
                        Forms\Components\TextInput::make('tax_id')->label('NIF / identificador fiscal')->maxLength(80),
                        Forms\Components\TextInput::make('address')->label('Domicilio fiscal')->maxLength(255),
                        Forms\Components\TextInput::make('postal_code')->label('Código postal')->maxLength(30),
                        Forms\Components\TextInput::make('city')->label('Localidad')->maxLength(120),
                        Forms\Components\TextInput::make('province')->label('Provincia / región')->maxLength(120),
                        Forms\Components\TextInput::make('country')
                            ->label('País (código ISO de 2 letras)')
                            ->length(2)
                            ->default('ES'),
                        Forms\Components\TextInput::make('invoice_serie')
                            ->label('Serie de facturas')
                            ->maxLength(20)
                            ->placeholder('F1')
                            ->helperText('Ejemplo: F1, F2026, A1'),
                        Forms\Components\TextInput::make('contact_email')->label('Correo de contacto')->email()->maxLength(255),
                        Forms\Components\TextInput::make('phone')->label('Teléfono')->tel()->maxLength(40),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(FiscalProfile $profile): void
    {
        abort_unless(static::canAccess(), 403);

        $profile->save($this->form->getState());
        $this->form->fill($profile->get());

        Notification::make()->title('Datos de empresa guardados')->success()->send();
    }
}
