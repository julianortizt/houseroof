<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->darkMode(true)
            ->brandName(config('platform.name'))
            ->colors([
                'primary' => Color::Amber,
            ])
            ->resources([
                \App\Filament\Resources\BlogPostResource::class,
                \App\Filament\Resources\AccountingAccountResource::class,
                \App\Filament\Resources\AuditLogResource::class,
                \App\Filament\Resources\JournalEntryResource::class,
                \App\Filament\Resources\CategoryResource::class,
                \App\Filament\Resources\ColorResource::class,
                \App\Filament\Resources\ContentBlockResource::class,
                \App\Filament\Resources\InventoryItemResource::class,
                \App\Filament\Resources\InventoryLocationResource::class,
                \App\Filament\Resources\InventoryMovementResource::class,
                \App\Filament\Resources\InventorySupplierResource::class,
                \App\Filament\Resources\InventoryPurchaseOrderResource::class,
                \App\Filament\Resources\InventoryValuationResource::class,
                \App\Filament\Resources\InvoiceResource::class,
                \App\Filament\Resources\QuotePaymentResource::class,
                \App\Filament\Resources\MapsCalculator\ClientResource::class,
                \App\Filament\Resources\MapsCalculator\PriceItemResource::class,
                \App\Filament\Resources\MapsCalculator\ProjectResource::class,
                \App\Filament\Resources\MapsCalculator\QuoteResource::class,
                \App\Filament\Resources\MapsCalculator\WorkOrderResource::class,
                \App\Filament\Resources\OrderResource::class,
                \App\Filament\Resources\PageResource::class,
                \App\Filament\Resources\ProductResource::class,
                \App\Filament\Resources\ProjectResource::class,
                \App\Filament\Resources\SimulatorFinishResource::class,
                \App\Filament\Resources\SimulatorRoomTemplateResource::class,
                \App\Filament\Resources\UserResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                \App\Http\Middleware\AdminLocalization::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()->label('Tienda'),
                NavigationGroup::make()->label(__('admin.Inventario')),
                NavigationGroup::make()->label('Finanzas (borradores internos)'),
                NavigationGroup::make()->label('Contabilidad (base interna)'),
                NavigationGroup::make()->label('Contenido'),
                NavigationGroup::make()->label('Pedidos y Cotizaciones'),
                NavigationGroup::make()->label('Mapscalculator'),
                NavigationGroup::make()->label('Configuración')->collapsed(true),
            ])
            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.components.admin-language-toggle'),
            )
            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.components.admin-theme-toggle'),
            )
            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.components.mapscalculator-link'),
            )
            ->renderHook(
                'panels::topbar.end',
                fn () => view('filament.components.simulator-link'),
            )
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.components.admin-logo-size'),
            );
    }
}
