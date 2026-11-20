<?php

namespace App\Filament\Pages;

use App\Services\StripeCredentials;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class StripeSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Control exclusivo del desarrollador';
    protected static ?string $navigationLabel = 'Stripe · credenciales';
    protected static ?string $slug = 'stripe-settings';
    protected static ?int $navigationSort = 30;
    protected static string $view = 'filament.pages.stripe-settings';

    public ?array $data = [];
    public array $credentialStatus = [];

    public static function canAccess(): bool
    {
        $developerEmail = config('platform.developer_email');

        return Filament::getCurrentPanel()?->getId() === 'super-panel'
            && filled($developerEmail)
            && auth('web')->user()?->email === $developerEmail;
    }

    public function mount(StripeCredentials $credentials): void
    {
        $this->credentialStatus = $credentials->configured();
        $this->form->fill([
            'publishable_key' => '',
            'secret_key' => '',
            'webhook_secret' => '',
            'enable_product_checkout' => $credentials->checkoutEnabled(),
            'product_checkout_fiscal_review_confirmed' => (bool) \App\Models\Setting::get('stripe_product_fiscal_review_confirmed', false),
            'enable_service_payments' => $credentials->servicePaymentsEnabled(),
            'service_payment_fiscal_review_confirmed' => (bool) \App\Models\Setting::get('stripe_service_payments_fiscal_review_confirmed', false),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stripe en producción')
                    ->description('Las claves se cifran con APP_KEY. Los campos aparecen vacíos: dejar uno vacío conserva la credencial existente. Guardar aquí no inicia cobros. El servicio requiere los módulos Cotizaciones, Facturación y Pagos, además de la confirmación de revisión fiscal.')
                    ->schema([
                        Forms\Components\Placeholder::make('publishable_status')
                            ->label('Clave pública')
                            ->content(fn (): string => ($this->credentialStatus['publishable_key'] ?? false) ? 'Configurada' : 'Pendiente'),
                        Forms\Components\TextInput::make('publishable_key')
                            ->label('Clave pública live')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->regex('/^pk_live_[A-Za-z0-9_-]+$/')
                            ->dehydrated(),
                        Forms\Components\Placeholder::make('secret_status')
                            ->label('Clave privada')
                            ->content(fn (): string => ($this->credentialStatus['secret_key'] ?? false) ? 'Configurada' : 'Pendiente'),
                        Forms\Components\TextInput::make('secret_key')
                            ->label('Clave privada live')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->regex('/^sk_live_[A-Za-z0-9_-]+$/')
                            ->dehydrated(),
                        Forms\Components\Placeholder::make('webhook_status')
                            ->label('Secreto de webhook')
                            ->content(fn (): string => ($this->credentialStatus['webhook_secret'] ?? false) ? 'Configurado' : 'Pendiente'),
                        Forms\Components\TextInput::make('webhook_secret')
                            ->label('Secreto de webhook live')
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->regex('/^whsec_[A-Za-z0-9_-]+$/')
                            ->dehydrated(),
                        Forms\Components\Toggle::make('enable_product_checkout')
                            ->label('Habilitar checkout de productos')
                            ->helperText('Esta opción no habilita pagos de servicios ni factura cotizaciones.'),
                        Forms\Components\Checkbox::make('product_checkout_fiscal_review_confirmed')
                            ->label('Confirmo que asesoría validó precios, impuestos, envíos y condiciones de venta')
                            ->required(fn (Forms\Get $get): bool => (bool) $get('enable_product_checkout')),
                        Forms\Components\Toggle::make('enable_service_payments')
                            ->label('Habilitar anticipos y saldos de servicios')
                            ->helperText('Activa cobros live de cotizaciones; requiere los módulos Cotizaciones, Facturación y Pagos.'),
                        Forms\Components\Checkbox::make('service_payment_fiscal_review_confirmed')
                            ->label('Confirmo que asesoría validó el flujo fiscal de los cobros de servicios')
                            ->required(fn (Forms\Get $get): bool => (bool) $get('enable_service_payments')),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(StripeCredentials $credentials): void
    {
        $credentials->save($this->form->getState());
        $this->credentialStatus = $credentials->configured();
        $this->form->fill([
            'publishable_key' => '',
            'secret_key' => '',
            'webhook_secret' => '',
            'enable_product_checkout' => $credentials->checkoutEnabled(),
            'product_checkout_fiscal_review_confirmed' => (bool) \App\Models\Setting::get('stripe_product_fiscal_review_confirmed', false),
            'enable_service_payments' => $credentials->servicePaymentsEnabled(),
            'service_payment_fiscal_review_confirmed' => (bool) \App\Models\Setting::get('stripe_service_payments_fiscal_review_confirmed', false),
        ]);

        Notification::make()->title('Configuración Stripe guardada')->success()->send();
    }
}
